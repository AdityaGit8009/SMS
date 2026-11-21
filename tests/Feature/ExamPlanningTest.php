<?php

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps the old schedule when the new subject or date is outside the exam', function () {
    $this->seed(SmsDemoSeeder::class);
    $exam = Exam::sole();
    $semester = Semester::create(['course_id' => $exam->course_id, 'number' => 2, 'name' => 'Semester 2']);
    $subject = Subject::create(['course_id' => $exam->course_id, 'semester_id' => $semester->id, 'name' => 'Later subject', 'code' => 'LATER']);

    $this->actingAs(User::where('role', 'admin')->sole())->from(route('admin.exams.index'))
        ->post(route('admin.exams.schedules.store', $exam), [
            'subject_id' => $subject->id, 'exam_date' => $exam->end_date->addDay()->toDateString(), 'max_marks' => 100, 'passing_marks' => 40,
        ])->assertRedirectToRoute('admin.exams.index')->assertSessionHasErrors(['subject_id', 'exam_date']);

    $this->assertDatabaseCount('exam_schedules', 1);
    expect(ExamSchedule::sole()->subject_id)->not->toBe($subject->id);
});

it('returns a semester from another course as an exam form error', function () {
    $this->seed(SmsDemoSeeder::class);
    $exam = Exam::sole();
    $course = Course::sole()->replicate()->fill(['code' => 'NEW']);
    $course->save();

    $this->actingAs(User::where('role', 'admin')->sole())->post(route('admin.exams.store'), [
        'name' => 'New exam', 'course_id' => $course->id, 'semester_id' => $exam->semester_id,
        'academic_session_id' => $exam->academic_session_id, 'start_date' => '2026-12-01', 'end_date' => '2026-12-10', 'status' => 'draft',
    ])->assertSessionHasErrors('semester_id');

    $this->assertDatabaseCount('exams', 1);
});
