<?php

use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\ExamSchedule;
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
