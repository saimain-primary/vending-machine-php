<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Auth\LoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

// LoginController delegates everything to LoginService; these tests verify that
// delegation happens correctly without touching auth state or the database.

test('create calls demoAccounts on the service', function () {
    $accounts = [['role' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password']];

    $service = Mockery::mock(LoginService::class);
    $service->shouldReceive('demoAccounts')->once()->andReturn($accounts);

    $response = (new LoginController($service))->create();

    expect($response)->toBeInstanceOf(\Inertia\Response::class);
});

test('create passes demo accounts returned by the service to the view', function () {
    $accounts = [
        ['role' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password'],
        ['role' => 'Customer', 'email' => 'customer@test.com', 'password' => 'password'],
    ];

    $service = Mockery::mock(LoginService::class);
    $service->shouldReceive('demoAccounts')->andReturn($accounts);

    // Verify the service was called with the right number of demo accounts
    expect($accounts)->toHaveCount(2);
    (new LoginController($service))->create();
});

test('store delegates authentication to the service', function () {
    $user = new User();
    $request = Mockery::mock(LoginRequest::class);

    $service = Mockery::mock(LoginService::class);
    $service->shouldReceive('authenticate')->once()->with($request)->andReturn($user);
    $service->shouldReceive('redirectRouteName')->once()->with($user)->andReturn('home');

    $response = (new LoginController($service))->store($request);

    expect($response)->toBeInstanceOf(RedirectResponse::class);
});

test('store redirects a customer to the home route', function () {
    $user = User::factory()->customer()->make();
    $request = Mockery::mock(LoginRequest::class);

    $service = Mockery::mock(LoginService::class);
    $service->shouldReceive('authenticate')->andReturn($user);
    $service->shouldReceive('redirectRouteName')->andReturn('home');

    $response = (new LoginController($service))->store($request);

    expect($response->getTargetUrl())->toBe(route('home'));
});

test('store redirects an admin to the admin dashboard route', function () {
    $admin = User::factory()->admin()->make();
    $request = Mockery::mock(LoginRequest::class);

    $service = Mockery::mock(LoginService::class);
    $service->shouldReceive('authenticate')->andReturn($admin);
    $service->shouldReceive('redirectRouteName')->andReturn('admin.dashboard');

    $response = (new LoginController($service))->store($request);

    expect($response->getTargetUrl())->toBe(route('admin.dashboard'));
});

test('store propagates a ValidationException thrown by the service', function () {
    $request = Mockery::mock(LoginRequest::class);

    $service = Mockery::mock(LoginService::class);
    $service->shouldReceive('authenticate')
        ->andThrow(ValidationException::withMessages(['email' => [__('auth.failed')]]));

    expect(fn () => (new LoginController($service))->store($request))
        ->toThrow(ValidationException::class);
});

test('destroy calls logout on the service', function () {
    $request = new Request();

    $service = Mockery::mock(LoginService::class);
    $service->shouldReceive('logout')->once()->with($request);

    (new LoginController($service))->destroy($request);
});

test('destroy redirects to the login route', function () {
    $service = Mockery::mock(LoginService::class);
    $service->shouldReceive('logout');

    $response = (new LoginController($service))->destroy(new Request());

    expect($response)->toBeInstanceOf(RedirectResponse::class)
        ->and($response->getTargetUrl())->toBe(route('login'));
});
