<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();

        $_SERVER["REMOTE_ADDR"] = '127.0.0.1';
        $_SERVER['SERVER_NAME'] = 'PHP.UNIT.TEST';
        $_SERVER['REQUEST_URI'] = 'PHPUNITTEST';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUNITTEST';
    }
}
