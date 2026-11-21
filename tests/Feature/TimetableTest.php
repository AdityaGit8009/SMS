<?php

use App\Models\Subject;
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
