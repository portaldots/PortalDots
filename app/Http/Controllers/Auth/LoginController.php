<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;
use App\Eloquents\Schedule;
use App\Eloquents\Document;
use App\Services\Circles\SelectorService;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * @var SelectorService
     */
    private $selectorService;

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    public function username()
    {
        return 'login_id';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SelectorService $selectorService)
    {
        $this->middleware('guest')->except('logout', 'showLogout');

        $this->selectorService = $selectorService;
    }

    /**
     * ログインページに GET リクエストされた場合
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * ログアウトページに GET リクエストされた場合
     *
     * @return \Illuminate\Http\Response
     */
    public function showLogout()
    {
        return view('auth.logout');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        // スタッフモードの二段階認証状態を解除する
        $request->session()->forget('staff_authorized');

        // 選択中の企画からもログアウトする
        $this->selectorService->reset();
    }
}
