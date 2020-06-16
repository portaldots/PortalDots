<?php

namespace App\Http\Controllers\Circles;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShowAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.belongsTo', $circle);

        $circle->load('users');

        return view('v2.circles.show')
            ->with('circle', $circle);
    }
}
