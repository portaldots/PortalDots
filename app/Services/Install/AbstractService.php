<?php

declare(strict_types=1);

namespace App\Services\Install;

use App\Services\Utils\DotenvService;

abstract class AbstractService
{
    abstract protected function getEnvKeys(): array;

    abstract public function getValidationRules(): array;

    abstract public function getFormLabels(): array;

    /**
     * @var DotenvService
     */
    private $dotenvService;

    public function __construct(DotenvService $dotenvService)
    {
        $this->dotenvService = $dotenvService;
    }

    public function getInfo()
    {
        $result = [];

        foreach ($this->getEnvKeys() as $key) {
            // $key に PASSWORD という文字列が含まれている場合は、
            // セキュリティのため値を取得しない
            if (strpos($key, 'PASSWORD') !== false) {
                $result[$key] = '';
                continue;
            }
            $result[$key] = $this->dotenvService->getValue($key);
        }

        return $result;
    }

    public function updateInfo(array $info)
    {
        $save_keys = [];
        foreach ($this->getEnvKeys() as $key) {
            if (empty($info[$key])) {
                continue;
            }
            $save_keys[$key] = $info[$key];
        }

        $this->dotenvService->saveKeys($save_keys);
    }
}
