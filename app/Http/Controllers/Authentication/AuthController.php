<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthFormRequest;
use App\Models\Token;
use App\Models\User;
use App\Services\Email;
use App\Services\Encryption;
use App\Services\FileManagement;
use App\Services\Generator;
use App\Services\ResponseFormatter;
use App\Services\Translations;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $generator, $encryption, $user, $token, $email, $translation, $fileManagement, $responseFormatter;

    public function __construct(
        Generator $generator,
        Encryption $encryption,
        User $user,
        Token $token,
        Email $email,
        Translations $translation,
        FileManagement $fileManagement,
        ResponseFormatter $responseFormatter,
    ) {
        $this->generator = $generator;
        $this->encryption = $encryption;
        $this->user = $user;
        $this->token = $token;
        $this->email = $email;
        $this->translation = $translation;
        $this->fileManagement = $fileManagement;
        $this->responseFormatter = $responseFormatter;
    }

    public function login_page()
    {
        $translation_login = $this->translation->authLogin;
        $translation_messages = $this->translation->authMessages;
        $translation_validation = $this->translation->authValidation;
        return view('template.default.authentication.login', array_merge($translation_login, $translation_messages, $translation_validation));
    }

    public function register_page()
    {
        $translation_registration = $this->translation->authRegistration;
        $translation_messages = $this->translation->authMessages;
        $translation_validation = $this->translation->authValidation;
        return view('template.default.authentication.registration', array_merge($translation_registration, $translation_messages, $translation_validation));
    }

    public function resend_verification_page()
    {
        $translation_verification = $this->translation->authVerification;
        $translation_messages = $this->translation->authMessages;
        $translation_validation = $this->translation->authValidation;
        return view('template.default.authentication.resend-verification', array_merge($translation_verification, $translation_messages, $translation_validation));
    }

    public function forgot_password_page()
    {
        $translation_forgot_password = $this->translation->authForgotPassword;
        $translation_messages = $this->translation->authMessages;
        $translation_validation = $this->translation->authValidation;
        return view('template.default.authentication.forgot-password', array_merge($translation_forgot_password, $translation_messages, $translation_validation));
    }

    public function reset_password_page($token)
    {
        $data_token = $this->token->GetUUIDByToken($this->encryption->DecryptToken($token));
        $dataUser = $this->user->GetUserByID($data_token->user_id);

        $translation_resetpassword = $this->translation->authResetPassword;
        $translation_messages = $this->translation->authMessages;
        $translation_validation = $this->translation->authValidation;

        try {
            if (is_null($dataUser)) {
                throw new Exception($this->translation->authMessages['token_invalid']);
            } else {
                return view('template.default.authentication.reset-password', array_merge([
                    'token' => $token,
                ], $translation_resetpassword, $translation_messages, $translation_validation));
            }
        } catch (\Throwable$th) {
            return redirect()->route('forgot.password.page')->with([
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function login(AuthFormRequest $request)
    {
        $request->validated();
        $user = $request->only(['email', 'password']);
        $remember_me = $request->only(['remember_me']);
        Auth::attempt($user, $remember_me != null ? true : false);
        $auth = Auth::user();
        try {
            if ($auth != null) {
                if ($auth->hasRole('Client')) {
                    if ($auth->email_verified_at != null) {
                        return redirect()->route('login.page')->with([
                            'success' => $this->translation->authMessages['login_success'],
                        ]);
                    } else {
                        Auth::logout();
                        return redirect()->route('resend.verification.page')->with([
                            'error' => $this->translation->authMessages['email_not_verified'],
                        ]);
                    }
                } elseif (($auth->hasRole('Administrator'))) {
                    return redirect()->route('dashboard.main')->with([
                        'success' => $this->translation->authMessages['login_success'],
                    ]);
                } else {
                    Auth::logout();
                    throw new Exception($this->translation->authMessages["user_not_registered"], 1);
                }
            } else {
                throw new Exception($this->translation->authMessages["user_not_found"]);
            }
        } catch (\Throwable$th) {
            return redirect()->route('login.page')->with([
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function logout()
    {
        return Auth::logout();
    }

    public function register_client(AuthFormRequest $request, $type)
    {
        $request->validated();
        $client = $request->only(["name", "email", "password", "password_confirmation"]);

        DB::beginTransaction();
        try {
            switch ($type) {
                case 'client':
                    $user_result = $this->user->StoreUser($client, 'Client');
                    $token_data = $this->token->StoreToken($user_result->id, $this->generator->GenerateWord());
                    $this->email->EmailVerification($user_result->email, $this->encryption->EncryptToken($token_data->token));
                    DB::commit();
                    return redirect()->route('resend.verification.page');
                    break;

                default:
                    return redirect()->route('login.page');
                    break;
            }
        } catch (\Throwable$th) {
            DB::rollBack();
            return redirect()->route('register.page')->with([
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function resend_verification(AuthFormRequest $request)
    {
        $request->validated();
        $email = $request->only(['email']);

        DB::beginTransaction();
        try {
            $getUser = $this->user->GetUserByEmail($email['email']);
            $getToken = $this->token->StoreToken($getUser->id, $this->generator->GenerateWord());
            $this->email->EmailVerification($getUser->email, $this->encryption->EncryptToken($getToken->token));
            DB::commit();
            return redirect()->route('resend.verification.page')->with([
                'success' => $this->translation->authMessages['email_sent'],
            ]);
        } catch (\Throwable$th) {
            DB::rollBack();
            return redirect()->route('resend.verification.page')->with([
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function verify($token)
    {
        DB::beginTransaction();
        try {
            $data_token = $this->token->GetUUIDByToken($this->encryption->DecryptToken($token));
            $dataUser = $this->user->GetUserByID($data_token->user_id);
            $dataUser->update([
                'email_verified_at' => date("Y-m-d H:i:s"),
            ]);
            $this->token->DeleteToken($dataUser->id);
            DB::commit();
            return redirect()->route('login.page')->with([
                'success' => $this->translation->authMessages['user_verified'],
            ]);
        } catch (\Throwable$th) {
            DB::rollBack();
            return redirect()->route('login.page')->with([
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function forgot_password(AuthFormRequest $request)
    {
        $request->validated();
        $email = $request->only(['email']);

        DB::beginTransaction();
        try {
            $getUser = $this->user->GetUserByEmail($email['email']);
            if (is_null($getUser)) {
                throw new Exception($this->translation->authMessages['user_not_found']);
            }
            $getToken = $this->token->StoreToken($getUser->id, $this->generator->GenerateWord());
            $this->email->EmailResetPassword($getUser->email, $this->encryption->EncryptToken($getToken->token));
            DB::commit();

            return redirect()->route('forgot.password.page')->with([
                'success' => $this->translation->authMessages['email_sent'],
            ]);
        } catch (\Throwable$th) {
            DB::rollBack();
            return redirect()->route('forgot.password.page')->with([
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function reset_password(AuthFormRequest $request)
    {
        $request->validated();
        $data = $request->only(['token', 'old_password', 'new_password']);
        DB::beginTransaction();
        try {
            $data_token = $this->token->GetUUIDByToken($this->encryption->DecryptToken($data["token"]));
            $dataUser = $this->user->GetUserByID($data_token->user_id);
            $match_password = Hash::check($data['old_password'], $dataUser->password);
            if ($match_password == true) {
                $dataUser->password = Hash::make($data['new_password']);
                $dataUser->save();
                DB::commit();
                return redirect()->route('login.page')->with([
                    'success' => $this->translation->authMessages['password_change'],
                ]);
            } else {
                throw new Exception($this->translation->authMessages['password_not_match'], 1);
            }
        } catch (\Throwable$th) {
            DB::rollBack();
            return redirect()->back()->with(['error' => $th->getMessage()]);
        }
    }

}
