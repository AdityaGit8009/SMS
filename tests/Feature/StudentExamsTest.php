<?php

use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Mark;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows only upcoming published examinations in the current enrollment', function () {
    $this->travelTo(today()->setDate(2026, 10, 1));
    $this->seed(SmsDemoSeeder::class);
    $exam = Exam::sole();
    $schedule = ExamSchedule::sole();
    $other = AcademicSession::create(['name' => 'Other year', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31']);
    foreach ([['Unannounced exam', $exam->academic_session_id, 'draft'], ['Other cohort exam', $other->id, 'published']] as [$name, $sessionId, $status]) {
        $copy = $exam->replicate()->fill(['name' => $name, 'academic_session_id' => $sessionId, 'status' => $status]);
        $copy->save();
        $schedule->replicate()->fill(['exam_id' => $copy->id])->save();
    }

    $this->actingAs(User::where('role', 'student')->sole())->get(route('student.dashboard'))
        ->assertViewHas('upcomingExams', fn ($exams) => $exams->modelKeys() === [$schedule->id]);
});

it('excludes draft results while retaining published and completed exam marks', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = Student::sole();
    $exam = Exam::sole();
    $schedule = ExamSchedule::sole();
    $publicExamIds = [$exam->id];
    Mark::create(['exam_schedule_id' => $schedule->id, 'student_id' => $student->id, 'total_marks' => 75]);
    foreach (['draft', 'completed'] as $status) {
        $copy = $exam->replicate()->fill(['status' => $status]);
        $copy->save();
        $next = $schedule->replicate()->fill(['exam_id' => $copy->id]);
        $next->save();
        Mark::create(['exam_schedule_id' => $next->id, 'student_id' => $student->id, 'total_marks' => 60]);
        if ($status === 'completed') {
            $publicExamIds[] = $copy->id;
        }
    }

    $this->actingAs($student->user)->get(route('student.results'))
        ->assertViewHas('marks', fn ($marks) => $marks->keys()->sort()->values()->all() === $publicExamIds);
});
