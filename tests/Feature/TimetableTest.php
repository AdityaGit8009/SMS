<?php

use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function smsTimetableSlot(Subject $subject, array $attributes = []): array
{
    return array_replace([
        'course_id' => $subject->course_id,
        'semester_id' => $subject->semester_id,
        'subject_id' => $subject->id,
        'teacher_id' => $subject->teacher_id,
        'day_of_week' => 'Monday',
        'start_time' => '09:00',
        'end_time' => '10:00',
    ], $attributes);
}

it('lists seeded classes in week and time order for the administrator', function () {
    $this->seed(SmsDemoSeeder::class);
    $subject = Subject::where('code', 'CS101')->firstOrFail();
    foreach ([['Friday', '09:00'], ['Monday', '14:00'], ['Monday', '11:00']] as [$day, $time]) {
        Timetable::create(smsTimetableSlot($subject, ['day_of_week' => $day, 'start_time' => $time, 'end_time' => '16:00']));
    }

    $this->actingAs(User::where('role', 'admin')->sole())->get(route('admin.timetable.index'))
        ->assertViewHas('items', fn ($items) => $items->map(fn ($item) => substr($item->start_time, 0, 5))->all() === ['11:00', '14:00', '09:00']);
});

it('shows earlier classes first on a personal timetable', function (string $role) {
    $this->seed(SmsDemoSeeder::class);
    $subject = Subject::where('code', 'CS101')->firstOrFail();
    Timetable::create(smsTimetableSlot($subject, ['start_time' => '13:00', 'end_time' => '14:00', 'room' => 'Afternoon lab']));
    Timetable::create(smsTimetableSlot($subject, ['room' => 'Morning lab']));

    $this->actingAs(User::where('role', $role)->sole())->get(route($role.'.timetable'))
        ->assertSeeInOrder(['Morning lab', 'Afternoon lab']);
})->with(['teacher', 'student']);

it('handles an account without a linked timetable profile', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'status' => true]);

    $this->actingAs($user)->get(route($role.'.timetable'))->assertNotFound();
})->with(['teacher', 'student']);

it('rejects a class-wide timetable overlap in either insertion order', function (bool $sectionFirst) {
    $this->seed(SmsDemoSeeder::class);
    $subject = Subject::where('code', 'CS101')->firstOrFail();
    $section = Section::sole();
    $teacher = Teacher::create(['user_id' => User::factory()->create(['role' => 'teacher'])->id, 'employee_id' => 'T2']);
    $other = Subject::where('code', 'CS102')->firstOrFail();
    $other->update(['teacher_id' => $teacher->id]);
    Timetable::create(smsTimetableSlot($subject, ['section_id' => $sectionFirst ? $section->id : null]));

    $this->actingAs(User::where('role', 'admin')->sole())->post(route('admin.timetable.store'),
        smsTimetableSlot($other, ['section_id' => $sectionFirst ? null : $section->id])
    )->assertSessionHasErrors('start_time');

    $this->assertDatabaseCount('timetables', 1);
})->with([true, false]);

it('allows a back-to-back class after a stored second-precision end time', function () {
    $this->seed(SmsDemoSeeder::class);
    $subject = Subject::where('code', 'CS101')->firstOrFail();
    Timetable::create(smsTimetableSlot($subject, ['start_time' => '09:00:00', 'end_time' => '10:00:00']));

    $this->actingAs(User::where('role', 'admin')->sole())->post(route('admin.timetable.store'),
        smsTimetableSlot($subject, ['start_time' => '10:00', 'end_time' => '11:00'])
    )->assertSessionHasNoErrors();

    $this->assertDatabaseCount('timetables', 2);
});
