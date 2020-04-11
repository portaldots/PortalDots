<?php

namespace App\Http\Controllers\Circles\Selector;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;

class ShowAction extends Controller
{
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function __invoke(Request $request)
    {
        $redirect = $request->redirect;
        if (isset($redirect) && $this->router->has($redirect)) {
            $user = Auth::user();
            $circles = $user->circles()->approved()->get();

            if (count($circles) <= 1) {
                return redirect()
                    ->route($redirect);
            }

            return view('v2.circles.selector')
                ->with('url', route($redirect))
                ->with('circles', $circles)
                ->with('error_message', session('error_message'));
        }

        return redirect()
            ->route('home');
    }
}
