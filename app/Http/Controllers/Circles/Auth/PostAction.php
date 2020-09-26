<?php

namespace App\Http\Controllers\Circles\Auth;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Circles\AuthRequest;
use Illuminate\Http\Request;

class PostAction extends Controller
{
    public function __invoke(Circle $circle, AuthRequest $request)
    {
        $this->authorize('circle.belongsTo', $circle);

        session(['circle_reauthorized_at' => now()]);
        return redirect()
            ->route('circles.show', ['circle' => $circle]);
    }
}
