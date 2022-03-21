<?php

namespace Tests\Feature\Services\Utils\ValueObjects;

use Tests\TestCase;
use App\Services\Utils\ValueObjects\Version;

class VersionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function versionProvider()
    {
        return [
            ['1.0.0', new Version(1, 0, 0)],
            ['0.1.2', new Version(0, 1, 2)],
            ['v2.2.4', new Version(2, 2, 4)],
            ['1.0', null],
            ['.0.', null],
            ['..', null],
            ['.', null],
            ['12345', null],
            ['4.0.0-beta.1', new Version(4, 0, 0, 'beta.1')],
            ['v4.0.2-alpha.4', new Version(4, 0, 2, 'alpha.4')],
        ];
    }

    /**
     * @test
     * @dataProvider versionProvider
     */
    public function parse(string $input, ?Version $expected)
    {
        if ($expected === null) {
            $this->assertNull(Version::parse($input));
        } else {
            $this->assertTrue(
                $expected->equals(Version::parse($input)),
                "expected: {$expected->getFullversion()}, actual: $input"
            );
        }
    }
}
