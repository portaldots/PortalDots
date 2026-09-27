<?php

namespace Tests\Unit\Support;

use App\Support\CsvFormulaEscaper;
use PHPUnit\Framework\TestCase;

class CsvFormulaEscaperTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function 危険な先頭文字と引用符を可逆に保護する(): void
    {
        $values = [
            '=1+1', '+1', '-1', '@A1', '＝1', '＋1', '－1', '＠A1',
            "\t=1", "\r=1", "\n=1", "\0=1", ' =1', "'word", "'=1", "''=1",
        ];
        foreach ($values as $value) {
            $escaped = CsvFormulaEscaper::escape($value);
            $this->assertSame("'" . $value, $escaped);
            $this->assertSame($value, CsvFormulaEscaper::unescape($escaped));
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 通常の値と空値を変更しない(): void
    {
        foreach (['', '場所A', '001', "O'Brien", "メモ\n2行目"] as $value) {
            $this->assertSame($value, CsvFormulaEscaper::escape($value));
            $this->assertSame($value, CsvFormulaEscaper::unescape($value));
        }
        $this->assertNull(CsvFormulaEscaper::escape(null));
        $this->assertSame("'word", CsvFormulaEscaper::unescape("'word"));
    }
}
