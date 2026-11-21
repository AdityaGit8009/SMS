<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('signs inactive accounts out of shared account pages', function (string $routeName) {
    $user = User::factory()->create(['status' => false]);

    $this->actingAs($user)->get(route($routeName))
        ->assertRedirectToRoute('login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
})->with(['profile.edit', 'password.change']);

it('keeps account settings available to active users', function () {
    $user = User::factory()->create(['role' => 'student', 'status' => true]);

    $this->actingAs($user)->get(route('profile.edit'))->assertViewIs('profile.edit');
});
