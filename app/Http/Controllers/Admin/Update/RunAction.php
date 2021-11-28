<?php

namespace App\Http\Controllers\Admin\Update;

use App\Http\Controllers\Controller;
use Codedge\Updater\UpdaterManager;

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
            // Get the new version available
            $versionAvailable = $this->updater->source()->getVersionAvailable();

            // Create a release
            $release = $this->updater->source()->fetch($versionAvailable);

            // Run the update process
            $this->updater->source()->update($release);

            return view('admin.update.done');
        } else {
            return redirect()
                ->route('staff.about')
                ->with('topAlert.title', 'すでに最新バージョンのPortalDotsがインストールされています。');
        }
    }
}
