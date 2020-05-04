<?php

namespace App\Http\Controllers\Install\Database;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Install\DatabaseService;

class EditAction extends Controller
{
    /**
     * @var DatabaseService
     */
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function __invoke(Request $request)
    {
        return view('v2.install.database.form')
            ->with('database', $this->databaseService->getInfo());
    }
}
