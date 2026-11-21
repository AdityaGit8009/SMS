<?php

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders panel page links and lets administrators reach the second page', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
    foreach (range(1, 20) as $number) {
        Department::create(['code' => 'D'.$number, 'name' => 'Department '.$number]);
    }

    $this->actingAs($admin)->get(route('admin.departments.index'))
        ->assertSee('class="pagination"', false)
        ->assertSee('page=2', false);
    $this->get(route('admin.departments.index', ['page' => 2]))
        ->assertViewHas('items', fn ($items) => $items->currentPage() === 2 && $items->count() === 5);
});
