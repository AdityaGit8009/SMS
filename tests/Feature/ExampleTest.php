<?php

use App\Models\User;

it('directs guests from the home page to sign in', function () {
    $this->get('/')->assertRedirectToRoute('login');
});

it('directs an authenticated user to the dashboard for their role', function (string $role) {
    $user = User::factory()->make(['role' => $role, 'status' => true]);

    $this->actingAs($user)->get('/')->assertRedirectToRoute($role.'.dashboard');
})->with(['admin', 'teacher', 'student']);
