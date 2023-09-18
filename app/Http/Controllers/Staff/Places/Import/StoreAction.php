<?php

namespace App\Http\Controllers\Staff\Places\Import;

use App\Http\Controllers\Controller;
use App\Imports\PlacesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StoreAction extends Controller
{
    public function __invoke(Request $request)
    {
        $this->validate(
            $request,
            [
                'importFile' => ['required', 'file'],
            ],
            [],
            [
                'importFile' => 'CSVファイル',
            ]
        );
        $file = $request->file('importFile');

        try {
            (new PlacesImport)->import($file->path(), null, \Maatwebsite\Excel\Excel::CSV);
        } catch (\Exception $e) {
            dd($e);
        }

        return redirect()
            ->route('staff.places.index')
            ->with('topAlert.title', '場所情報をインポートしました');
    }
}
