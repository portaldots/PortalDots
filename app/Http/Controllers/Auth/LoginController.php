<?php

namespace App\Http\Controllers\Auth;

use Request as FacadeRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;
use App\Eloquents\Schedule;
use App\Eloquents\Document;

class LoginController extends Controller
{
    use AuthenticatesUsers;

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
    protected $redirectTo = '/home';

    public function username()
    {
        return 'login_id';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout', 'showLogout');
    }

    /**
     * ログインページに GET リクエストされた場合
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        if ((int)FacadeRequest::input('new') === 1) {
            // GETパラメータで ?new=1 が渡されたら、新しいログイン画面をオプトインする
            return view('v2.home')
                ->with('pages', Page::take(5)->get())
                ->with('next_schedule', Schedule::startOrder()->notStarted()->first())
                ->with('documents', Document::public()->get());
        }
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
    }
}
