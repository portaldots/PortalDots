<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Answer;
use App\Eloquents\Form;
use Illuminate\Http\UploadedFile;

class AnswerInputNormalizer
{
    public function __construct(private AnswerDetailsService $answerDetailsService)
    {
    }

    public function normalize(mixed $answers, Form $form, ?Answer $savedAnswer = null): mixed
    {
        $answers = $this->normalizeValue($answers);
        if (!is_array($answers)) {
            return $answers;
        }
        $form->loadMissing('questions');

        foreach ($form->questions->where('type', 'table') as $question) {
            $rows = $answers[$question->id] ?? null;
            if (!is_array($rows)) {
                continue;
            }
            $columnsById = collect($question->table ?? [])->keyBy('id');
            $savedEnvelope = $savedAnswer instanceof Answer && $savedAnswer->form_id === $form->id
                ? $this->answerDetailsService->getTableAnswerEnvelopeByAnswer($savedAnswer, (int) $question->id)
                : ['rows' => [], 'columns' => []];
            $normalizedRows = [];
            foreach ($rows as $rowId => $row) {
                $isPresent = is_array($row) && ($row['__present'] ?? null) === '1';
                if (is_array($row)) {
                    unset($row['__present']);
                }
                if (
                    $this->hasMalformedShape($rowId, $row, $columnsById) ||
                    !$this->isBlankRow($row, $columnsById) ||
                    ($isPresent && $this->hasSavedDeletedColumnValue($rowId, $columnsById, $savedEnvelope))
                ) {
                    $normalizedRows[$rowId] = $row;
                }
            }
            $answers[$question->id] = $normalizedRows;
        }

        return $answers;
    }

    private function hasMalformedShape(mixed $rowId, mixed $row, $columnsById): bool
    {
        if (!is_string($rowId) || !\Illuminate\Support\Str::isUuid($rowId) || !is_array($row)) {
            return true;
        }
        foreach ($row as $columnId => $value) {
            $column = $columnsById->get($columnId);
            if (!is_array($column)) {
                return true;
            }
            if (($column['type'] ?? null) === 'checkbox') {
                if (!is_array($value)) {
                    continue;
                }
                foreach ($value as $item) {
                    if (is_array($item) || $item instanceof UploadedFile) {
                        return true;
                    }
                }
            } elseif (is_array($value)) {
                return true;
            }
        }
        return false;
    }

    private function isBlankRow(array $row, $columnsById): bool
    {
        foreach ($row as $columnId => $value) {
            $column = $columnsById->get($columnId);
            if (!is_array($column)) {
                return false;
            }
            if (($column['type'] ?? null) === 'checkbox') {
                if ($value !== [] && $value !== null && $value !== '') {
                    return false;
                }
            } elseif (!$this->isBlankValue($value)) {
                return false;
            }
        }
        return true;
    }

    private function hasSavedDeletedColumnValue(mixed $rowId, $columnsById, array $savedEnvelope): bool
    {
        if (!is_string($rowId) || !isset($savedEnvelope['rows'][$rowId]) || !is_array($savedEnvelope['rows'][$rowId])) {
            return false;
        }
        foreach ($savedEnvelope['rows'][$rowId] as $columnId => $value) {
            if (!$columnsById->has($columnId) && !$this->isBlankValue($value)) {
                return true;
            }
        }
        return false;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof UploadedFile) {
            return $value;
        }
        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeValue($item), $value);
        }
        if (is_string($value)) {
            return str_replace("\r\n", "\n", $value);
        }
        return $value;
    }

    private function isBlankValue(mixed $value): bool
    {
        if ($value instanceof UploadedFile) {
            return false;
        }
        if (is_array($value)) {
            if ($value === []) {
                return true;
            }
            foreach ($value as $item) {
                if (!$this->isBlankValue($item)) {
                    return false;
                }
            }
            return true;
        }
        if (is_string($value)) {
            return trim($value) === '';
        }
        return $value === null;
    }
}
