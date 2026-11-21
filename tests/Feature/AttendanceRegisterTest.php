<?php

use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('edits a saved attendance register without adding another row for the day', function () {
    $this->seed(SmsDemoSeeder::class);
    $teacher = User::where('role', 'teacher')->sole();
    $subject = Subject::where('code', 'CS101')->firstOrFail();
    $student = Student::sole();
    foreach (['absent', 'present'] as $status) {
        $this->actingAs($teacher)->post(route('teacher.attendance.store'), [
            'date' => '2026-11-10', 'subject_id' => $subject->id, 'attendance' => [$student->id => $status],
        ])->assertSessionHasNoErrors();
    }

    $this->assertDatabaseCount('attendances', 1);
    $this->assertDatabaseHas('attendances', ['student_id' => $student->id, 'status' => 'present', 'marked_by' => $teacher->id]);
});

it('returns a form error for attendance on a student who left the roster', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    $student->update(['status' => false]);

    $this->actingAs(User::where('role', 'admin')->sole())->post(route('admin.attendance.store'), [
        'date' => '2026-11-10', 'subject_id' => Subject::where('code', 'CS101')->firstOrFail()->id,
        'attendance' => [$student->id => 'leave'],
    ])->assertSessionHasErrors('attendance');

    $this->assertDatabaseEmpty('attendances');
});

it('returns invalid register filters to the attendance form', function () {
    $user = User::factory()->create(['role' => 'admin', 'status' => true]);

    $this->actingAs($user)->from(route('admin.attendance.index'))
        ->get(route('admin.attendance.index', ['subject_id' => 'unknown', 'date' => 'not-a-date']))
        ->assertRedirectToRoute('admin.attendance.index')
        ->assertSessionHasErrors(['subject_id', 'date']);
});
