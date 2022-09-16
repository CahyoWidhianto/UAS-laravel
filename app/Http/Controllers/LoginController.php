<?php


namespace App\Http\Controllers;

use App\Mail\RegisterConfirmMail;
use App\Model\Auth;
use App\Model\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ChangePasswordRequest;

class LoginController extends Controller
{
    public function verifikasiUser()
    {
        $username = request('username');
        $paswword = request('password');
        $user = Auth::verifyUSer($username, $paswword);
        if ($user === null) {
            return redirect(route('login.index'))
                ->withErrors(["Username atau Password salah"]);
        }
        if ($user->active === "N") {
            return redirect(route('login.index'))
                ->withErrors(["Akun anda tidak aktif"]);
        }
        $token = Auth::createToken($user->id);
        session(
            [
                'token' => $token,
                'nama' => $user->nama
            ]
        );
        return redirect(route('dashboard'));
    }

    public function logout()
    {
        session()->forget('token');
        session()->forget('nama');
        return redirect(route('login.index'));

    }

    public function register()
    {
        return view('layout/register');
    }

    public function createRegister()
    {
        $emailConfirmToken = md5(date('Y-m-d H:i:s') . '' . request('username'));
        $userCreate = [
            'nama' => request('nama'),
            'username' => request('username'),
            'password' => password_hash(request('password'), PASSWORD_DEFAULT),
            'role' => 'admin',
            'email_confirm_token' => md5($emailConfirmToken),
            'active' => 'N'
        ];
        User::createNew($userCreate);
        Mail::to(request('username'))->send(new RegisterConfirmMail($userCreate));
        return redirect(route('login.index'));
    }

    public function confirmEmail($emailConfirmToken)
    {
        $user = User::getByEmailConfirmToken($emailConfirmToken);
        if ($user !== NULL) {
            User::setUserToActive($user->id);
            return redirect(route('login.index'));
        }
        return redirect(route('login.index'))
            ->withErrors(["Akun anda tidak di temukan"]);
    }



}
