<?php

namespace App\Imports;

use App\Eloquents\Place;
use App\Support\CsvFormulaEscaper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class PlacesImport
{
    private const HEADERS = [
        '場所ID',
        '場所名',
        'タイプ',
        'スタッフ用メモ',
    ];

    private const TYPES = [
        '屋内' => 1,
        '屋外' => 2,
        '特殊場所' => 3,
    ];

    public function import(UploadedFile $file): int
    {
        $rows = $this->readCsv($file);

        return DB::transaction(function () use ($rows) {
            $places = Place::query()->lockForUpdate()->get()->keyBy('id');
            $errors = [];
            $ids = [];

            foreach ($rows as $index => &$row) {
                $line = $row['line'];
                $idValue = trim($row['values']['場所ID']);
                $name = trim(CsvFormulaEscaper::unescape($row['values']['場所名']));
                $type = trim($row['values']['タイプ']);
                $notes = CsvFormulaEscaper::unescape($row['values']['スタッフ用メモ']);

                $id = null;
                if ($idValue !== '') {
                    $validatedId = filter_var(
                        $idValue,
                        FILTER_VALIDATE_INT,
                        ['options' => ['min_range' => 1]]
                    );
                    if ($validatedId === false) {
                        $this->addError($errors, $line, '場所ID', '場所IDは正の整数で入力してください。');
                    } else {
                        $id = $validatedId;
                        $ids[$id][] = $line;
                        if (!$places->has($id)) {
                            $this->addError($errors, $line, '場所ID', '指定された場所IDは存在しません。');
                        }
                    }
                }

                if (!array_key_exists($type, self::TYPES)) {
                    $this->addError(
                        $errors,
                        $line,
                        'タイプ',
                        'タイプは「屋内」「屋外」「特殊場所」のいずれかを入力してください。'
                    );
                }

                foreach ([$idValue, $name, $type, $notes] as $value) {
                    if (!mb_check_encoding($value, 'UTF-8')) {
                        $this->addError($errors, $line, '文字コード', 'CSVはUTF-8で保存してください。');
                        break;
                    }
                }

                $row['id'] = $id;
                $row['name'] = $name;
                $row['type'] = self::TYPES[$type] ?? null;
                $row['notes'] = $notes === '' ? null : $notes;
            }
            unset($row);

            foreach ($ids as $lines) {
                if (count($lines) < 2) {
                    continue;
                }
                foreach ($lines as $line) {
                    $this->addError($errors, $line, '場所ID', '同じ場所IDがCSV内で重複しています。');
                }
            }

            if ($errors !== []) {
                usort($errors, fn (array $a, array $b) => $a['line'] <=> $b['line']);
                throw new PlacesImportValidationException($errors);
            }

            foreach ($rows as $row) {
                $validator = Validator::make(
                    ['name' => $row['name']],
                    [
                        'name' => [
                            'required',
                            'string',
                            'max:255',
                            Rule::unique('places', 'name')->ignore($row['id']),
                        ],
                    ]
                );
                if ($validator->fails()) {
                    foreach ($validator->errors()->get('name') as $message) {
                        $reason = match (true) {
                            $row['name'] === '' => '場所名は必須です。',
                            mb_strlen($row['name']) > 255 => '場所名は255文字以内で入力してください。',
                            default => '同名の場所が既に存在します。',
                        };
                        $this->addError($errors, $row['line'], '場所名', $reason);
                    }
                    continue;
                }

                $place = $row['id'] === null ? new Place() : $places->get($row['id']);
                $place->name = $row['name'];
                $place->type = $row['type'];
                $place->notes = $row['notes'];
                $place->save();
            }

            if ($errors !== []) {
                throw new PlacesImportValidationException($errors);
            }

            return count($rows);
        });
    }

    private function readCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded CSV.');
        }

        try {
            $prefix = fread($handle, 3);
            if ($prefix !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $headerOffset = ftell($handle);
            $headers = fgetcsv($handle, escape: '');
            if ($headers === false) {
                throw new PlacesImportValidationException([
                    $this->error(1, 'ヘッダー', 'CSVのヘッダー行がありません。'),
                ]);
            }
            $headerEnd = ftell($handle);
            if (!$this->hasBalancedQuotes($this->readRange($handle, $headerOffset, $headerEnd))) {
                throw new PlacesImportValidationException([
                    $this->error(1, 'ヘッダー', 'CSVの引用符が閉じられていません。'),
                ]);
            }

            $headerErrors = [];
            foreach (self::HEADERS as $requiredHeader) {
                $matches = array_keys($headers, $requiredHeader, true);
                if ($matches === []) {
                    $headerErrors[] = $this->error(1, $requiredHeader, '必須ヘッダーがありません。');
                } elseif (count($matches) > 1) {
                    $headerErrors[] = $this->error(1, $requiredHeader, '同じヘッダーが重複しています。');
                }
            }
            if ($headerErrors !== []) {
                throw new PlacesImportValidationException($headerErrors);
            }

            $headerIndexes = [];
            foreach (self::HEADERS as $requiredHeader) {
                $headerIndexes[$requiredHeader] = array_search($requiredHeader, $headers, true);
            }

            $rows = [];
            $line = 1;
            while (!feof($handle)) {
                $offset = ftell($handle);
                $values = fgetcsv($handle, escape: '');
                if ($values === false) {
                    break;
                }
                $end = ftell($handle);
                $line++;
                $startLine = $line;
                foreach ($values as $value) {
                    $line += substr_count((string) $value, "\n");
                }
                if (!$this->hasBalancedQuotes($this->readRange($handle, $offset, $end))) {
                    throw new PlacesImportValidationException([
                        $this->error($startLine, 'CSV形式', 'CSVの引用符が閉じられていません。'),
                    ]);
                }
                if ($this->isEmptyRow($values)) {
                    continue;
                }
                if (count($values) !== count($headers)) {
                    $rows[] = [
                        'line' => $startLine,
                        'values' => array_fill_keys(self::HEADERS, ''),
                        'shapeError' => $this->error(
                            $startLine,
                            '列数',
                            'ヘッダーと同じ列数で入力してください。'
                        ),
                    ];
                    continue;
                }

                $row = [];
                foreach ($headerIndexes as $header => $index) {
                    $row[$header] = (string) ($values[$index] ?? '');
                }
                $rows[] = ['line' => $startLine, 'values' => $row];
            }

            $shapeErrors = array_values(array_filter(array_column($rows, 'shapeError')));
            if ($shapeErrors !== []) {
                throw new PlacesImportValidationException($shapeErrors);
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function readRange($handle, int $start, int $end): string
    {
        $current = ftell($handle);
        fseek($handle, $start);
        $contents = fread($handle, $end - $start);
        fseek($handle, $current);

        return $contents === false ? '' : $contents;
    }

    private function hasBalancedQuotes(string $record): bool
    {
        $insideQuotes = false;
        $length = strlen($record);

        for ($index = 0; $index < $length; $index++) {
            if ($record[$index] !== '"') {
                continue;
            }
            if ($insideQuotes && $index + 1 < $length && $record[$index + 1] === '"') {
                $index++;
                continue;
            }
            $insideQuotes = !$insideQuotes;
        }

        return !$insideQuotes;
    }

    private function addError(array &$errors, int $line, string $attribute, string $reason): void
    {
        $errors[] = $this->error($line, $attribute, $reason);
    }

    private function error(int $line, string $attribute, string $reason): array
    {
        return compact('line', 'attribute', 'reason');
    }
}
