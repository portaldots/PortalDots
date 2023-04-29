<?php

namespace App\Http\Controllers\Auth;

use App\Eloquents\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterService;
use App\Services\Auth\EmailService;
use App\Services\Auth\VerifyService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Swift_RfcComplianceException;

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
     * @var RegisterService
     */
    private $registerService;

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
     * @param  RegisterService  $registerService
     * @param  EmailService  $emailService
     * @param  VerifyService  $verifyService
     * @return void
     */
    public function __construct(
        RegisterService $registerService,
        EmailService $emailService,
        VerifyService $verifyService
    ) {
        $this->middleware('guest');
        $this->registerService = $registerService;
        $this->emailService = $emailService;
        $this->verifyService = $verifyService;
    }

    public function showRegistrationForm()
    {
        return view('users.register');
    }

    /**
     * ユーザー登録を実行する
     *
     * @param  RegisterRequest  $request
     * @return Response
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        $user = $this->registerService->create(
            student_id: $request->student_id,
            name: $request->name,
            name_yomi: $request->name_yomi,
            email: $request->email,
            univemail_local_part: $request->univemail_local_part,
            univemail_domain_part: $request->univemail_domain_part,
            tel: $request->tel,
            plain_password: $request->password
        );

        event(new Registered($user));

        try {
            // メール認証に関する処理
            if ($user->univemail === $user->email) {
                $this->verifyService->markEmailAsVerified($user, $user->email);
            }
            $this->emailService->sendAll($user);
        } catch (Swift_RfcComplianceException $e) {
            DB::rollBack();
            return redirect()
                ->route('register')
                ->withInput()
                ->withErrors(['student_id' => config('portal.student_id_name') . 'を正しく入力してください']);
        }

        DB::commit();

        $this->guard()->login($user);

        // return $this->registered($request, $user)
        //     ?: redirect($this->redirectPath());
        return redirect()
            ->route('verification.notice');
    }
}
