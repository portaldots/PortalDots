<?php

declare(strict_types=1);

namespace App\Http\Responders;

use Illuminate\Http\Response;

interface Respondable
{
    public function response(): Response;
}
