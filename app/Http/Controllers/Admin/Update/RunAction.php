<?php

namespace App\Http\Controllers\Admin\Update;

use App\Http\Controllers\Controller;
use Codedge\Updater\UpdaterManager;
use Illuminate\Support\Facades\Artisan;

class RunAction extends Controller
{
    /**
     * @var UpdaterManager
     */
    private $updater;

    public function __construct(UpdaterManager $updater)
    {
        $this->updater = $updater;
    }

    public function __invoke()
    {
        // Check if new version is available
        if ($this->updater->source()->isNewVersionAvailable()) {
            try {
                Artisan::call('down --message="ソフトウェアアップデート中です"');

                // Get the new version available
                $versionAvailable = $this->updater->source()->getVersionAvailable();

                // Create a release
                $release = $this->updater->source()->fetch($versionAvailable);

                // Run the update process
                $this->updater->source()->update($release);

                Artisan::call('up');

                return view('admin.update.done');
            } catch (\Exception $e) {
                Artisan::call('up');

                return view('admin.update.error')
                    ->with('error', $e->getMessage());
            }
        } else {
            return redirect()
                ->route('staff.about')
                ->with('topAlert.title', 'すでに最新バージョンのPortalDotsがインストールされています。');
        }
    }
}
