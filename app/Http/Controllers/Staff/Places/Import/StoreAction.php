<?php

namespace App\Http\Controllers\Staff\Places\Import;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Places\PlacesImportRequest;
use App\Imports\PlacesImport;
use App\Imports\PlacesImportValidationException;
use Throwable;

class StoreAction extends Controller
{
    public function __invoke(PlacesImportRequest $request, PlacesImport $placesImport)
    {
        try {
            $placesImport->import($request->file('importFile'));
        } catch (PlacesImportValidationException $exception) {
            return redirect()
                ->route('staff.places.import.index')
                ->withInput()
                ->withErrors(['importFile' => 'CSVファイルの内容にエラーがあります。'])
                ->with('importErrors', $exception->validationErrors());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('staff.places.import.index')
                ->withInput()
                ->withErrors(['importFile' => 'インポートに失敗しました。もう一度お試しください。']);
        }

        return redirect()
            ->route('staff.places.index')
            ->with('topAlert.title', '場所情報をインポートしました');
    }
}
