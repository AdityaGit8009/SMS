<?php

use App\Models\Course;
use App\Models\Notice;
use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('publishes active notices on their scheduled date across audience pages', function (string $role, string $routeName) {
    $this->travelTo(today()->setDate(2026, 11, 10));
    $this->seed(SmsDemoSeeder::class);
    foreach ([['Today notice', today(), true], ['Future notice', today()->addDay(), true], ['Unpublished notice', today(), false]] as [$title, $date, $status]) {
        Notice::create(['title' => $title, 'content' => $title, 'publish_date' => $date, 'status' => $status]);
    }

    $this->actingAs(User::where('role', $role)->sole())->get(route($routeName))
        ->assertViewHas($routeName === 'student.notices' ? 'items' : 'notices', function ($notices) {
            $titles = $notices->pluck('title');

            return $titles->contains('Today notice') && ! $titles->contains('Future notice') && ! $titles->contains('Unpublished notice');
        });
})->with([['student', 'student.notices'], ['student', 'student.dashboard'], ['teacher', 'teacher.dashboard']]);

it('limits teacher notices to assigned courses and general announcements', function () {
    $this->seed(SmsDemoSeeder::class);
    $course = Course::sole();
    $other = $course->replicate();
    $other->code = 'OTHER';
    $other->save();
    foreach ([['Assigned announcement', $course->id], ['Other announcement', $other->id], ['General announcement', null]] as [$title, $courseId]) {
        Notice::create(['course_id' => $courseId, 'title' => $title, 'content' => $title, 'publish_date' => today(), 'status' => true]);
    }

    $this->actingAs(User::where('role', 'teacher')->sole())->get(route('teacher.dashboard'))
        ->assertViewHas('notices', fn ($notices) => $notices->pluck('title')->contains('Assigned announcement')
            && $notices->pluck('title')->contains('General announcement')
            && ! $notices->pluck('title')->contains('Other announcement'));
});
