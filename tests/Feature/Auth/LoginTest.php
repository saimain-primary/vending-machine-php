<?php

use App\Models\User;

test('login page is accessible to guests', function () {
    $this->get('/login')->assertStatus(200);
});

test('authenticated users are redirected away from login page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/login')->assertRedirect();
});

test('customer is redirected to home after login', function () {
    $user = User::factory()->customer()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('home'));
});

test('admin is redirected to admin dashboard after login', function () {
    $admin = User::factory()->admin()->create();

    $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

test('login fails with wrong password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');
});

test('login fails with non-existent email', function () {
    $this->post('/login', [
        'email' => 'nobody@example.com',
        'password' => 'password',
    ])->assertSessionHasErrors('email');
});

test('login requires email field', function () {
    $this->post('/login', [
        'password' => 'password',
    ])->assertSessionHasErrors('email');
});

test('login requires password field', function () {
    $this->post('/login', [
        'email' => 'user@example.com',
    ])->assertSessionHasErrors('password');
});

test('logout clears the session and redirects to login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
