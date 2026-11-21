<?php

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SmsDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the dashboard links and their destinations for a seeded role', function (string $role, array $destinations) {
    $this->seed(SmsDemoSeeder::class);
    $this->actingAs(User::where('role', $role)->sole());
    $dashboard = $this->get(route($role.'.dashboard'))->assertOk();

    foreach ([...$destinations, 'profile.edit', 'password.change'] as $name) {
        $dashboard->assertSee(route($name));
        $this->get(route($name))->assertOk();
    }
})->with([
    'admin panel' => ['admin', [
        'admin.academic-sessions.index', 'admin.departments.index', 'admin.courses.index', 'admin.semesters.index',
        'admin.sections.index', 'admin.subjects.index', 'admin.teachers.index', 'admin.students.index',
        'admin.attendance.index', 'admin.fees.index', 'admin.exams.index', 'admin.marks.index', 'admin.timetable.index',
        'admin.notices.index', 'admin.leaves.index', 'admin.reports.index', 'admin.settings.edit',
    ]],
    'teacher panel' => ['teacher', [
        'teacher.attendance.index', 'teacher.assignments.index', 'teacher.timetable', 'teacher.notices.index', 'teacher.leaves.index',
    ]],
    'student panel' => ['student', [
        'student.attendance', 'student.fees', 'student.results', 'student.assignments.index', 'student.timetable',
        'student.notices', 'student.leaves.index', 'student.id-card',
    ]],
]);

it('shows a submitted assignment, its feedback and the resubmission form', function () {
    $this->seed(SmsDemoSeeder::class);
    $student = User::where('role', 'student')->sole()->student;
    $subject = Subject::where('code', 'CS101')->firstOrFail();
    $assignment = Assignment::create(['teacher_id' => $subject->teacher_id, 'subject_id' => $subject->id, 'title' => 'Lab workbook', 'due_date' => '2026-12-10 12:00', 'max_marks' => 10]);
    AssignmentSubmission::create(['assignment_id' => $assignment->id, 'student_id' => $student->id, 'file_path' => 'assignment-submissions/sample.txt', 'submitted_at' => '2026-12-01', 'marks' => 0, 'status' => 'graded', 'remarks' => 'Please revise the introduction.']);

    $this->actingAs($student->user)->get(route('student.assignments.index'))
        ->assertSeeText('Lab workbook')->assertSeeText('Graded')->assertSeeText('Please revise the introduction.')
        ->assertSeeText('Resubmit')->assertSee(route('student.assignments.submit', $assignment));
});
