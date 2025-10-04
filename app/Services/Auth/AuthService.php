<?php

namespace App\Services\Auth;

use App\Enums\RoleStatus;
use App\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    protected StatefulGuard $guard;

    public function __construct(AuthManager $auth)
    {
        $this->guard = $auth->guard();
    }

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => RoleStatus::CASHIER->value,
            ]);
        });
    }

    public function login(string $email, string $password, bool $remember = false): bool
    {
        $ok = $this->guard->attempt(
            ['email' => $email, 'password' => $password],
            $remember
        );

        if ($ok) {
            session()->regenerate();
        }

        return $ok;
    }

    public function logout(): void
    {
        $this->guard->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return $this->guard->user();
    }
}