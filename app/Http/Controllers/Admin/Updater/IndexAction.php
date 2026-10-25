<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Updater;

use App\Http\Controllers\Controller;
use App\Services\Updater\UpdaterManager;

class IndexAction extends Controller
{
    public function __invoke(UpdaterManager $updater)
    {
        return view('admin.updater.index')->with('updater', $updater->overview());
    }
}
