<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Mail\PleaseConfirmYourEmail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
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

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // [Eric] bug fix: Symfony\Component\Debug\Exception\FatalThrowableError: Type error: Argument 1 passed to Illuminate\Auth\SessionGuard::login() must implement interface Illuminate\Contracts\Auth\Authenticatable, null given, called in /home/vagrant/Code/forum/vendor/laravel/framework/src/Illuminate/Foundation/Auth/RegistersUsers.php on line 35     this function must have a return value otherwise error above occurred.

        return User::forceCreate([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'confirmation_token' => str_limit(md5($data['email'] . str_random()), 25, '')
        ]);
    }

    /**
     * The user has been registered
     * @param Request $request
     * @param User $user
     *
     * @author Eric
     * @date 2017-12-27
     */
    protected function registered(Request $request, User $user)
    {
        Mail::to($user)->send(new PleaseConfirmYourEmail($user));
    }
}
