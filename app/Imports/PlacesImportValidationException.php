<?php

namespace App\Imports;

use RuntimeException;

class PlacesImportValidationException extends RuntimeException
{
    public function __construct(private array $validationErrors)
    {
        parent::__construct('The places CSV contains invalid data.');
    }

    public function validationErrors(): array
    {
        return $this->validationErrors;
    }
}
