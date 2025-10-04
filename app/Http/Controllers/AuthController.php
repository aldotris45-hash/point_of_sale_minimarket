<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly AuthServiceInterface $auth) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $ok = $this->auth->login(
            $validated['email'],
            $validated['password'],
            (bool) ($validated['remember'] ?? false)
        );

        if (!$ok) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'Kredensial tidak valid.');
        }

        return redirect()->intended('/');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->auth->logout();

        return redirect('/login')->with('status', 'Anda telah keluar.');
    }
}
