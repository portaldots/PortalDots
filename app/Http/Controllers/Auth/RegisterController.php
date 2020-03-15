<?php

namespace App\Http\Controllers\Auth;

use App\Eloquents\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\EmailService;
use App\Services\Auth\VerifyService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use RegistersUsers;

    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @var VerifyService
     */
    private $verifyService;

    /**
     * Create a new controller instance.
     *
     * @param  EmailService  $emailService
     * @param  VerifyService  $verifyService
     * @return void
     */
    public function __construct(EmailService $emailService, VerifyService $verifyService)
    {
        $this->middleware('guest');
        $this->emailService = $emailService;
        $this->verifyService = $verifyService;
    }

    public function showRegistrationForm()
    {
        return view('v2.users.register');
    }

    /**
     * ユーザー登録を実行する
     *
     * @param  RegisterRequest  $request
     * @return Response
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = new User();
        $user->student_id = $request->student_id;
        $user->name = $request->name;
        $user->name_yomi = $request->name_yomi;
        $user->email = $request->email;
        $user->tel = $request->tel;
        $user->password = Hash::make($request->password);
        $user->save();

        event(new Registered($user));

        // メール認証に関する処理
        if ($user->univemail === $user->email) {
            $this->verifyService->markEmailAsVerified($user, $user->email);
        }
        $this->emailService->sendAll($user);

        $this->guard()->login($user);

        // return $this->registered($request, $user)
        //     ?: redirect($this->redirectPath());
        return redirect()
            ->route('verification.notice');
    }
}
