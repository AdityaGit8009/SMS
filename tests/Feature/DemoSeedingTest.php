<?php

use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('provisions three local roles and preserves changed passwords on repeat seeds', function () {
    $password = Str::random(32);
    config(['sms.demo_password' => $password]);
    $this->seed(SmsDemoSeeder::class);
    $admin = User::where('email', 'admin@example.test')->firstOrFail();
    expect(Hash::check($password, $admin->password))->toBeTrue();
    $newPassword = Str::random(32);
    $admin->update(['password' => $newPassword]);

    $this->seed(SmsDemoSeeder::class);

    $this->assertDatabaseCount('users', 3);
    expect(Hash::check($newPassword, $admin->fresh()->password))->toBeTrue();
    expect(User::where('email', 'teacher@example.test')->firstOrFail()->teacher)->not->toBeNull();
    expect(User::where('email', 'student@example.test')->firstOrFail()->student)->not->toBeNull();
});

it('leaves production databases without demo accounts or fixtures', function (string $seeder) {
    app()->detectEnvironment(fn () => 'production');

    app($seeder)->run();

    $this->assertDatabaseEmpty('users');
    $this->assertDatabaseEmpty('academic_sessions');
})->with([UserSeeder::class, SmsDemoSeeder::class]);
