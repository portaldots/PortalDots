<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes;

use App\Eloquents\ParticipationType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DestroyAction extends Controller
{
    public function __invoke(ParticipationType $participationType)
    {
        return DB::transaction(function () use ($participationType) {
            $participationType->form->delete();
            $participationType->delete();
            return redirect(route('staff.circles.index'));
        });
    }
}
