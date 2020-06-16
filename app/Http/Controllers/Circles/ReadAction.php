<?php

namespace App\Http\Controllers\Circles;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReadAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.belongsTo', $circle);

        if (Gate::allows('circle.update', $circle)) {
            return redirect()
                ->route('circles.confirm', ['circle' => $circle]);
        }

        $circle->load('users');

        return view('v2.circles.read')
            ->with('circle', $circle);
    }
}
