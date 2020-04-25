<?php

declare(strict_types=1);

namespace App\Services\Install;

use Jackiedo\DotenvEditor\DotenvEditor;

abstract class AbstractService
{
    abstract protected function getEnvKeys(): array;

    /**
     * @var DotenvEditor
     */
    private $editor;

    public function __construct(DotenvEditor $editor)
    {
        $this->editor = $editor;
    }

    public function getInfo()
    {
        $result = [];

        foreach ($this->getEnvKeys() as $key) {
            $result[$key] = $this->editor->getValue($key);
        }

        return $result;
    }

    public function updateInfo(array $info)
    {
        foreach ($this->getEnvKeys() as $key) {
            if (empty($info[$key])) {
                continue;
            }
            $this->editor->setKey($key, $info[$key]);
        }

        $this->editor->save();
    }
}
