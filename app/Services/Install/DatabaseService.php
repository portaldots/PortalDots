<?php

declare(strict_types=1);

namespace App\Services\Install;

use mysqli;
use Exception;
use Jackiedo\DotenvEditor\DotenvEditor;

class DatabaseService extends AbstractService
{
    protected function getEnvKeys(): array
    {
        return [
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
        ];
    }

    public function canConnectDatabase($host, int $port, $database, $username, $password)
    {
        try {
            $mysqli = new mysqli($host, $username, $password, $database, $port);
            $result = empty($mysqli->connect_error);
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
}
