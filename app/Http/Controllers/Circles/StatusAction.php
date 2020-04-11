<?php

namespace App\Http\Controllers\Circles;

use Auth;
use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatusAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $circle = Auth::user()->circles()->rejected()->findOrFail($circle->id);

        // status_reason が空の場合、このページはアクセス不可(というより 404 )とする
        if (empty($circle->status_reason)) {
            abort(404);
        }

        return view('v2.circles.status')
            ->with('circle', $circle);
    }
}
