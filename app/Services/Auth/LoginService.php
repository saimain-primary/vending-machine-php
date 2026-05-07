<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function authenticate(LoginRequest $request): User
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return Auth::user();
    }

    public function redirectRouteName(User $user): string
    {
        return match ($user->role) {
            UserRole::Admin => 'admin.dashboard',
            default => 'home',
        };
    }

    /**
     * @return array<int, array{role: string, email: string, password: string}>
     */
    public function demoAccounts(): array
    {
        return [
            ['role' => 'Admin', 'email' => 'admin@vending.test', 'password' => 'password'],
            ['role' => 'Customer', 'email' => 'customer@vending.test', 'password' => 'password'],
        ];
    }

    public function logout(Request $request): void
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
