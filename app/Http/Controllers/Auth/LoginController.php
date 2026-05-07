<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function __construct(private readonly LoginService $loginService) {}

    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'demoAccounts' => $this->loginService->demoAccounts(),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $this->loginService->authenticate($request);

        return redirect()->route($this->loginService->redirectRouteName($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->loginService->logout($request);

        return redirect()->route('login');
    }
}
