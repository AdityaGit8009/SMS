<?php

use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('offers fee structures beyond the current management table page', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    foreach (range(1, 14) as $number) {
        FeeStructure::create(['name' => 'Lab '.$number, 'amount' => 10.25, 'course_id' => $student->course_id, 'academic_session_id' => $student->academic_session_id]);
    }

    $this->actingAs(User::where('role', 'admin')->sole())->get(route('admin.fees.index'))
        ->assertViewHas('structures', fn ($structures) => $structures->count() === 12)
        ->assertViewHas('paymentStructures', fn ($items) => $items->count() === 16)
        ->assertSee('name="fee_structure_id" required', false)
        ->assertSeeText('Lab 14 (₹10.25)');
});
