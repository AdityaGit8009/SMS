<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\FeeStructure;
use App\Models\Notice;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class SmsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $this->call(UserSeeder::class);
        $admin = User::where('email', 'admin@example.test')->firstOrFail();

        $session = AcademicSession::updateOrCreate(['name' => '2026-27'], [
            'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_current' => true,
        ]);
        $department = Department::updateOrCreate(['code' => 'CSE'], ['name' => 'Computer Science & Engineering', 'status' => true]);
        $course = Course::updateOrCreate(['code' => 'BTECH-CSE'], [
            'department_id' => $department->id, 'name' => 'B.Tech CSE', 'duration_years' => 4, 'total_semesters' => 8, 'course_fee' => 80000, 'status' => true,
        ]);
        $semester = Semester::updateOrCreate(['course_id' => $course->id, 'number' => 1], ['name' => 'Semester 1']);
        $section = Section::updateOrCreate(['course_id' => $course->id, 'code' => 'A'], ['name' => 'Section A']);

        $teacherUser = User::where('email', 'teacher@example.test')->firstOrFail();
        $teacher = Teacher::updateOrCreate(['user_id' => $teacherUser->id], [
            'employee_id' => 'TCH001', 'department_id' => $department->id, 'qualification' => 'M.Tech CSE', 'designation' => 'Assistant Professor', 'joining_date' => '2026-07-01', 'phone' => '9999999991',
        ]);

        $subject1 = Subject::updateOrCreate(['code' => 'CS101'], [
            'course_id' => $course->id, 'semester_id' => $semester->id, 'teacher_id' => $teacher->id, 'name' => 'Programming Fundamentals', 'credits' => 4,
        ]);
        $subject2 = Subject::updateOrCreate(['code' => 'CS102'], [
            'course_id' => $course->id, 'semester_id' => $semester->id, 'teacher_id' => $teacher->id, 'name' => 'Computer Fundamentals', 'credits' => 3,
        ]);

        $studentUser = User::where('email', 'student@example.test')->firstOrFail();
        $student = Student::updateOrCreate(['user_id' => $studentUser->id], [
            'admission_no' => 'ADM2026001', 'roll_no' => 'CSE001', 'course_id' => $course->id, 'semester_id' => $semester->id,
            'section_id' => $section->id, 'academic_session_id' => $session->id, 'dob' => '2007-01-10', 'gender' => 'male', 'phone' => '9999999992', 'status' => true,
        ]);

        FeeStructure::updateOrCreate([
            'course_id' => $course->id, 'academic_session_id' => $session->id, 'name' => 'Tuition Fee',
        ], ['type' => 'tuition', 'amount' => 70000]);
        FeeStructure::updateOrCreate([
            'course_id' => $course->id, 'academic_session_id' => $session->id, 'name' => 'Examination Fee',
        ], ['type' => 'exam', 'amount' => 5000]);

        $exam = Exam::updateOrCreate(['name' => 'Mid Semester Examination', 'course_id' => $course->id, 'semester_id' => $semester->id], [
            'academic_session_id' => $session->id, 'start_date' => '2026-10-10', 'end_date' => '2026-10-20', 'status' => 'published',
        ]);
        ExamSchedule::updateOrCreate(['exam_id' => $exam->id, 'subject_id' => $subject1->id], [
            'exam_date' => '2026-10-10', 'start_time' => '10:00', 'end_time' => '13:00', 'max_marks' => 100, 'passing_marks' => 33,
        ]);

        Notice::updateOrCreate(['title' => 'Welcome to Student Management System'], [
            'created_by' => $admin->id, 'content' => 'The SMS portal is ready for the 2026-27 academic session.', 'type' => 'general', 'target_role' => null, 'publish_date' => now()->toDateString(), 'status' => true,
        ]);
    }
}
