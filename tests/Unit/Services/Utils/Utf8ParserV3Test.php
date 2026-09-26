<?php

namespace Tests\Unit\Services\Utils;

use App\Services\Utils\Utf8ParserV3;
use Jackiedo\DotenvEditor\Exceptions\InvalidValueException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class Utf8ParserV3Test extends TestCase
{
    #[Test]
    public function unquoted_utf8_values_are_parsed_without_treating_bytes_as_whitespace(): void
    {
        $parsed = (new Utf8ParserV3())->parseEntry(
            'PORTAL_UNIVEMAIL_NAME=学校発行メールアドレス # 表示名'
        );

        $this->assertSame('setter', $parsed['type']);
        $this->assertSame('PORTAL_UNIVEMAIL_NAME', $parsed['key']);
        $this->assertSame('学校発行メールアドレス', $parsed['value']);
        $this->assertSame('表示名', $parsed['comment']);
    }

    #[Test]
    #[DataProvider('validValues')]
    public function supported_values_preserve_their_value_and_comment(
        string $entry,
        string $expectedValue,
        string $expectedComment,
    ): void {
        $parsed = (new Utf8ParserV3())->parseEntry($entry);

        $this->assertSame($expectedValue, $parsed['value']);
        $this->assertSame($expectedComment, $parsed['comment']);
    }

    public static function validValues(): array
    {
        return [
            'quoted whitespace' => ['KEY="hello world"', 'hello world', ''],
            'newline escape' => ['KEY="first\\nsecond"', "first\nsecond", ''],
            'escaped backslash' => ['KEY="C:\\\\directory"', 'C:\\directory', ''],
            'trailing comment' => ['KEY=value # explanation', 'value', 'explanation'],
        ];
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function invalid_values_are_rejected(string $entry): void
    {
        $this->expectException(InvalidValueException::class);

        (new Utf8ParserV3())->parseEntry($entry);
    }

    public static function invalidValues(): array
    {
        return [
            'missing quote' => ['KEY="unterminated'],
            'invalid escape' => ['KEY="bad\\q"'],
            'content after whitespace' => ['KEY=value trailing'],
        ];
    }
}
