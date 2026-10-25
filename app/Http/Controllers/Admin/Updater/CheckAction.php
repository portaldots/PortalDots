<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Updater;

use App\Http\Controllers\Controller;
use App\Services\Updater\UpdaterManager;

class CheckAction extends Controller
{
    public function __invoke(UpdaterManager $updater)
    {
        try {
            $release = $updater->check();
            return view('admin.updater.index')
                ->with('updater', $updater->overview())
                ->with('release', $release['signed'])
                ->with('manifest_digest', $release['digest']);
        } catch (\Throwable $exception) {
            return redirect()->route('admin.updater.index')->with([
                'topAlert.title' => $updater->safeMessage($exception),
                'topAlert.type' => 'danger',
                'topAlert.keepVisible' => true,
            ]);
        }
    }
}
