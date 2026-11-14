<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('it creates an admin who can sign in', function () {
    $this->artisan('admin:create', [
        '--name' => 'Summit Admin',
        '--email' => 'admin@example.com',
        '--password' => 'secret-password',
    ])->assertSuccessful();

    $admin = User::sole();

    expect($admin->name)->toBe('Summit Admin')
        ->and($admin->email)->toBe('admin@example.com')
        ->and(Hash::check('secret-password', $admin->password))->toBeTrue();
});

test('it resets the password of an existing admin', function () {
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    $this->artisan('admin:create', [
        '--email' => 'admin@example.com',
        '--password' => 'new-secret-password',
    ])->assertSuccessful();

    expect(User::count())->toBe(1)
        ->and(Hash::check('new-secret-password', $admin->fresh()->password))->toBeTrue();
});

test('it rejects a password shorter than 8 characters', function () {
    $this->artisan('admin:create', [
        '--name' => 'Summit Admin',
        '--email' => 'admin@example.com',
        '--password' => 'short',
    ])->assertFailed();

    expect(User::count())->toBe(0);
});
