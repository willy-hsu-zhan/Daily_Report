<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class SocialiteController extends Controller {

    public $data = []; //AlertData

    public function googleLogin()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
    public function googleLoginCallback()
    {
        // try
        // {
        //     $user = Socialite::driver('google')->stateless()->user();
        // }
        // catch (Exception $e)
        // {
        //     Log::info('googleLoginCallback error: ' . $e);
        //     return redirect()->intended('/');
        // }

        //$user      = Socialite::driver('google')->stateless()->user();
        //$existUser = User::where('email', $user->email)->first();
        $findUser  = User::where('google_account', '105594449468640124809')->first();

        // if( !User::isValidateEmail($user) ) // 未經許可的的email
        // {
        //     Log::info('Invalid Email: ' . $user?->email);
        //     $this->showInvalidateEmailAlert();

        //     return view('errors.unauthorized', ['data' => $this->data]);
        // }

        if( $findUser )
        {
            Auth::login($findUser);
            return view('welcome');
        }
        //如果會員資料庫中沒有 Google 帳戶資料，將檢查資料庫中有無會員 email，如果有僅加入 Google 帳戶資料後導向主控台
        // if( $existUser != null && $existUser->email === $user->email )
        // {
        //     $existUser->google_account = $user->id;
        //     $existUser->save();
        //     return redirect()->intended('/'); //重登
        // }
        // else
        // {
        //     //資料庫無會員資料時註冊會員資料，然後導向google登入重新讀取會員資料
        //     User::create([
        //         'name'           => $user->name,
        //         'email'          => $user->email,
        //         'google_account' => $user->id,
        //         'password'       => encrypt('fromsocialwebsite'),
        //     ]);
        //     return redirect()->intended('/');
        // }
    }

    public function showInvalidateEmailAlert()
    {
        $tag = 'sweetalert.invalidate_mail';

        $data = [
            'title' => config($tag . '.' . 'title'),
            'text'  => config($tag . '.' . 'text'),
            'icon'  => config($tag . '.' . 'icon'),
        ];

        $this->data = $data;
    }
}
