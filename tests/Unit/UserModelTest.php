<?php

use App\Enums\UserRole;
use App\Models\User;

test('isAdmin returns true for admin role', function () {
    $user = new User(['role' => UserRole::Admin]);

    expect($user->isAdmin())->toBeTrue();
});

test('isAdmin returns false for customer role', function () {
    $user = new User(['role' => UserRole::Customer]);

    expect($user->isAdmin())->toBeFalse();
});
