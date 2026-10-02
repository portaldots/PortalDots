<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Updater;

use App\Http\Controllers\Controller;
use App\Services\Updater\UpdaterManager;
use Illuminate\Http\Request;

class StartAction extends Controller
{
    public function __invoke(Request $request, UpdaterManager $updater)
    {
        try {
            $job = $updater->start($request->user(), (string) $request->input('manifest_digest'));
            return response()->view('admin.updater.started', ['job' => $job])
                ->header('Cache-Control', 'no-store, private, max-age=0')
                ->header('Pragma', 'no-cache');
        } catch (\Throwable $exception) {
            return redirect()->route('admin.updater.index')->with([
                'topAlert.title' => $updater->safeMessage($exception),
                'topAlert.type' => 'danger',
                'topAlert.keepVisible' => true,
            ]);
        }
    }
}
