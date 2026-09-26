<?php

declare(strict_types=1);

namespace App\Services\Utils;

use Jackiedo\DotenvEditor\Contracts\ParserInterface;
use Jackiedo\DotenvEditor\Exceptions\InvalidValueException;
use Jackiedo\DotenvEditor\Workers\Parsers\Parser;

/**
 * Dotenv editor parser that treats a UTF-8 character as one token.
 *
 * The package parser tokenizes values byte-by-byte, which can classify a
 * continuation byte in an unquoted Japanese value as whitespace.
 */
class Utf8ParserV3 extends Parser implements ParserInterface
{
    /**
     * @return array{string, string}
     */
    protected function parseSetterData($data)
    {
        if ($data === null || trim($data) === '') {
            return ['', ''];
        }

        $state = 'initial';
        $value = '';
        $comment = '';

        foreach (mb_str_split($data, 1, 'UTF-8') as $character) {
            if ($state === 'comment') {
                $comment .= $character;
                continue;
            }

            if ($state === 'escape') {
                if (in_array($character, ['"', '\\', '$'], true)) {
                    $value .= $character;
                } elseif (isset(['f' => 1, 'n' => 1, 'r' => 1, 't' => 1, 'v' => 1][$character])) {
                    $value .= stripcslashes('\\' . $character);
                } else {
                    throw new InvalidValueException(self::getErrorMessage('an unexpected escape sequence', $data));
                }

                $state = 'double-quoted';
                continue;
            }

            if ($state === 'single-quoted') {
                $state = $character === "'" ? 'trailing' : $state;
                $value .= $character === "'" ? '' : $character;
                continue;
            }

            if ($state === 'double-quoted') {
                if ($character === '"') {
                    $state = 'trailing';
                } elseif ($character === '\\') {
                    $state = 'escape';
                } else {
                    $value .= $character;
                }
                continue;
            }

            if ($state === 'trailing') {
                if ($character === '#') {
                    $state = 'comment';
                } elseif (!ctype_space($character)) {
                    throw new InvalidValueException(self::getErrorMessage('unexpected whitespace', $data));
                }
                continue;
            }

            if ($character === '#') {
                $state = 'comment';
            } elseif ($state === 'initial' && $character === "'") {
                $state = 'single-quoted';
            } elseif ($state === 'initial' && $character === '"') {
                $state = 'double-quoted';
            } elseif (ctype_space($character)) {
                $state = 'trailing';
            } else {
                $state = 'unquoted';
                $value .= $character;
            }
        }

        if (in_array($state, ['single-quoted', 'double-quoted', 'escape'], true)) {
            throw new InvalidValueException(self::getErrorMessage('a missing closing quote', $data));
        }

        return [$value, $this->normaliseComment($comment)];
    }
}
