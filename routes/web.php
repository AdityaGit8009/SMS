<?php

use App\Http\Controllers\Admin\AcademicSessionController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\FeeController as AdminFeeController;
use App\Http\Controllers\Admin\MarkController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TimetableController as AdminTimetableController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\FeeController as StudentFeeController;
use App\Http\Controllers\Student\IdCardController;
use App\Http\Controllers\Student\NoticeController as StudentNoticeController;
use App\Http\Controllers\Student\ResultController as StudentResultController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\NoticeController as TeacherNoticeController;
use App\Http\Controllers\TimetableViewController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return match (Auth::user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'teacher' => redirect()->route('teacher.dashboard'),
        'student' => redirect()->route('student.dashboard'),
        default => redirect()->route('login'),
    };
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('/forgot-password', [PasswordController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'resetPassword'])->name('password.update');
});

Route::middleware(['auth', 'role:admin,teacher,student'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/change-password', [PasswordController::class, 'showChangePasswordForm'])->name('password.change');
    Route::put('/change-password', [PasswordController::class, 'changePassword'])->name('password.change.update');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('academic-sessions', AcademicSessionController::class)->except('show');
    Route::resource('departments', DepartmentController::class)->except('show');
    Route::resource('courses', CourseController::class)->except('show');
    Route::resource('semesters', SemesterController::class)->except('show');
    Route::resource('sections', SectionController::class)->except('show');
    Route::resource('subjects', SubjectController::class)->except('show');
    Route::resource('teachers', TeacherController::class);
    Route::resource('students', StudentController::class);

    Route::post('/students/{student}/documents', [StudentController::class, 'uploadDocument'])->name('students.documents.store');
    Route::get('/students/{student}/documents/{document}', [StudentController::class, 'downloadDocument'])->name('students.documents.show');
    Route::delete('/students/{student}/documents/{document}', [StudentController::class, 'deleteDocument'])->name('students.documents.destroy');
    Route::get('/students/{student}/id-card', [StudentController::class, 'idCard'])->name('students.id-card');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('/fees', [AdminFeeController::class, 'index'])->name('fees.index');
    Route::post('/fees/structures', [AdminFeeController::class, 'storeStructure'])->name('fees.structures.store');
    Route::delete('/fees/structures/{feeStructure}', [AdminFeeController::class, 'deleteStructure'])->name('fees.structures.destroy');
    Route::post('/fees/payments', [AdminFeeController::class, 'storePayment'])->name('fees.payments.store');
    Route::get('/fees/payments/{payment}/receipt', [AdminFeeController::class, 'receipt'])->name('fees.receipt');
    Route::get('/fees/students/{student}/ledger', [AdminFeeController::class, 'studentLedger'])->name('fees.ledger');

    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
    Route::post('/exams/{exam}/schedules', [ExamController::class, 'storeSchedule'])->name('exams.schedules.store');
    Route::delete('/exam-schedules/{schedule}', [ExamController::class, 'deleteSchedule'])->name('exams.schedules.destroy');

    Route::get('/marks', [MarkController::class, 'index'])->name('marks.index');
    Route::post('/marks', [MarkController::class, 'store'])->name('marks.store');
    Route::get('/results/{student}', [MarkController::class, 'result'])->name('results.student');

    Route::get('/notices', [NoticeController::class, 'index'])->name('notices.index');
    Route::post('/notices', [NoticeController::class, 'store'])->name('notices.store');
    Route::put('/notices/{notice}', [NoticeController::class, 'update'])->name('notices.update');
    Route::delete('/notices/{notice}', [NoticeController::class, 'destroy'])->name('notices.destroy');

    Route::get('/timetable', [AdminTimetableController::class, 'index'])->name('timetable.index');
    Route::post('/timetable', [AdminTimetableController::class, 'store'])->name('timetable.store');
    Route::put('/timetable/{timetable}', [AdminTimetableController::class, 'update'])->name('timetable.update');
    Route::delete('/timetable/{timetable}', [AdminTimetableController::class, 'destroy'])->name('timetable.destroy');

    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::put('/leaves/{leaveRequest}/review', [LeaveController::class, 'review'])->name('leaves.review');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/students.csv', [ReportController::class, 'studentsCsv'])->name('reports.students.csv');
    Route::get('/reports/fees.csv', [ReportController::class, 'feesCsv'])->name('reports.fees.csv');
    Route::get('/reports/teachers.csv', [ReportController::class, 'teachersCsv'])->name('reports.teachers.csv');
    Route::get('/reports/attendance.csv', [ReportController::class, 'attendanceCsv'])->name('reports.attendance.csv');
    Route::get('/reports/exams.csv', [ReportController::class, 'examsCsv'])->name('reports.exams.csv');
    Route::get('/reports/results.csv', [ReportController::class, 'resultsCsv'])->name('reports.results.csv');

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});

Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::put('/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('assignments.grade');
    Route::get('/timetable', [TimetableViewController::class, 'index'])->name('timetable');
    Route::get('/notices', [TeacherNoticeController::class, 'index'])->name('notices.index');
    Route::post('/notices', [TeacherNoticeController::class, 'store'])->name('notices.store');
    Route::delete('/notices/{notice}', [TeacherNoticeController::class, 'destroy'])->name('notices.destroy');
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
});

Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendance', [AttendanceController::class, 'studentHistory'])->name('attendance');
    Route::get('/fees', [StudentFeeController::class, 'index'])->name('fees');
    Route::get('/results', [StudentResultController::class, 'index'])->name('results');
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');
    Route::get('/timetable', [TimetableViewController::class, 'index'])->name('timetable');
    Route::get('/notices', [StudentNoticeController::class, 'index'])->name('notices');
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
    Route::get('/id-card', [IdCardController::class, 'show'])->name('id-card');
});
