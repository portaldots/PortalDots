<?php

declare(strict_types=1);

namespace App\Support;

use App\Eloquents\Question;

class TableAnswerPresenter
{
    public static function cells(Question $question, ?string $stored): array
    {
        $envelope = \App\Services\Forms\AnswerDetailsService::decodeTableAnswerEnvelope($stored);
        $current = collect($question->table ?? [])->keyBy('id');
        $columns = $current->union(collect($envelope['columns'] ?? [])->keyBy('id'));
        $cells = [];
        $number = 0;
        foreach ($envelope['rows'] ?? [] as $rowId => $row) {
            $number++;
            foreach ($columns as $columnId => $column) {
                if (!array_key_exists($columnId, $row)) {
                    continue;
                }
                $value = $row[$columnId];
                $type = $column['type'];
                $name = ($column['name'] ?? '') ?: '(無題の項目)';
                if (!$current->has($columnId)) {
                    $name .= '（削除済みの項目）';
                }
                $text = is_array($value) ? implode(', ', $value) : (string) $value;
                if ($type === 'upload' && $text !== '') {
                    $text = basename($text);
                }
                $cells[] = [
                    'row' => $rowId,
                    'column' => $columnId,
                    'label' => $number . '件目・' . $name,
                    'type' => $type,
                    'text' => $text,
                ];
            }
        }
        return $cells;
    }

    public static function text(Question $question, ?string $stored): string
    {
        return implode("\n", array_map(
            fn (array $cell) => $cell['label'] . ': ' . $cell['text'],
            self::cells($question, $stored)
        ));
    }
}
