<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | SMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/sms.css') }}" rel="stylesheet">
    @stack('head')
</head>
<body class="sms-admin-body">
@php
    $user = auth()->user();
    $collegeName = \App\Models\Setting::valueOf('college_name', 'Student Management System');
    $logo = \App\Models\Setting::valueOf('logo');
@endphp
<div class="sms-sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sms-sidebar" id="smsSidebar">
    <div class="sms-sidebar-brand">
        <div class="sms-brand-icon">
            @if($logo)<img src="{{ asset('storage/'.$logo) }}" alt="Logo">@else<i class="bi bi-mortarboard-fill"></i>@endif
        </div>
        <div><h4>SMS</h4><span>{{ Str::limit($collegeName, 24) }}</span></div>
    </div>
    <div class="sms-sidebar-menu">
        <span class="sms-menu-label">MAIN</span>
        @if($user->role === 'admin')
            <a class="sms-nav-link {{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-grid-fill"></i><span>Dashboard</span></a>
            <span class="sms-menu-label">ACADEMIC</span>
            <a class="sms-nav-link {{ request()->routeIs('admin.academic-sessions.*')?'active':'' }}" href="{{ route('admin.academic-sessions.index') }}"><i class="bi bi-calendar3"></i><span>Academic Sessions</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.departments.*')?'active':'' }}" href="{{ route('admin.departments.index') }}"><i class="bi bi-building"></i><span>Departments</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.courses.*')?'active':'' }}" href="{{ route('admin.courses.index') }}"><i class="bi bi-journal-bookmark"></i><span>Courses</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.semesters.*')?'active':'' }}" href="{{ route('admin.semesters.index') }}"><i class="bi bi-layers"></i><span>Semesters</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.sections.*')?'active':'' }}" href="{{ route('admin.sections.index') }}"><i class="bi bi-diagram-3"></i><span>Sections</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.subjects.*')?'active':'' }}" href="{{ route('admin.subjects.index') }}"><i class="bi bi-book"></i><span>Subjects</span></a>
            <span class="sms-menu-label">PEOPLE</span>
            <a class="sms-nav-link {{ request()->routeIs('admin.teachers.*')?'active':'' }}" href="{{ route('admin.teachers.index') }}"><i class="bi bi-person-workspace"></i><span>Teachers</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.students.*')?'active':'' }}" href="{{ route('admin.students.index') }}"><i class="bi bi-people"></i><span>Students</span></a>
            <span class="sms-menu-label">MANAGEMENT</span>
            <a class="sms-nav-link {{ request()->routeIs('admin.attendance.*')?'active':'' }}" href="{{ route('admin.attendance.index') }}"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.fees.*')?'active':'' }}" href="{{ route('admin.fees.index') }}"><i class="bi bi-wallet2"></i><span>Fees</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.exams.*')?'active':'' }}" href="{{ route('admin.exams.index') }}"><i class="bi bi-file-earmark-text"></i><span>Exams</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.marks.*')?'active':'' }}" href="{{ route('admin.marks.index') }}"><i class="bi bi-bar-chart"></i><span>Marks & Results</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.timetable.*')?'active':'' }}" href="{{ route('admin.timetable.index') }}"><i class="bi bi-clock-history"></i><span>Timetable</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.notices.*')?'active':'' }}" href="{{ route('admin.notices.index') }}"><i class="bi bi-megaphone"></i><span>Notices</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.leaves.*')?'active':'' }}" href="{{ route('admin.leaves.index') }}"><i class="bi bi-calendar2-minus"></i><span>Leaves</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.reports.*')?'active':'' }}" href="{{ route('admin.reports.index') }}"><i class="bi bi-graph-up-arrow"></i><span>Reports</span></a>
            <a class="sms-nav-link {{ request()->routeIs('admin.settings.*')?'active':'' }}" href="{{ route('admin.settings.edit') }}"><i class="bi bi-gear"></i><span>Settings</span></a>
        @elseif($user->role === 'teacher')
            <a class="sms-nav-link {{ request()->routeIs('teacher.dashboard')?'active':'' }}" href="{{ route('teacher.dashboard') }}"><i class="bi bi-grid-fill"></i><span>Dashboard</span></a>
            <a class="sms-nav-link {{ request()->routeIs('teacher.attendance.*')?'active':'' }}" href="{{ route('teacher.attendance.index') }}"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
            <a class="sms-nav-link {{ request()->routeIs('teacher.assignments.*')?'active':'' }}" href="{{ route('teacher.assignments.index') }}"><i class="bi bi-journal-text"></i><span>Assignments</span></a>
            <a class="sms-nav-link {{ request()->routeIs('teacher.timetable')?'active':'' }}" href="{{ route('teacher.timetable') }}"><i class="bi bi-clock-history"></i><span>Timetable</span></a>
            <a class="sms-nav-link {{ request()->routeIs('teacher.notices.*')?'active':'' }}" href="{{ route('teacher.notices.index') }}"><i class="bi bi-megaphone"></i><span>Notices</span></a>
            <a class="sms-nav-link {{ request()->routeIs('teacher.leaves.*')?'active':'' }}" href="{{ route('teacher.leaves.index') }}"><i class="bi bi-calendar2-minus"></i><span>Leave</span></a>
        @else
            <a class="sms-nav-link {{ request()->routeIs('student.dashboard')?'active':'' }}" href="{{ route('student.dashboard') }}"><i class="bi bi-grid-fill"></i><span>Dashboard</span></a>
            <a class="sms-nav-link {{ request()->routeIs('student.attendance')?'active':'' }}" href="{{ route('student.attendance') }}"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
            <a class="sms-nav-link {{ request()->routeIs('student.fees')?'active':'' }}" href="{{ route('student.fees') }}"><i class="bi bi-wallet2"></i><span>Fees</span></a>
            <a class="sms-nav-link {{ request()->routeIs('student.results')?'active':'' }}" href="{{ route('student.results') }}"><i class="bi bi-bar-chart"></i><span>Results</span></a>
            <a class="sms-nav-link {{ request()->routeIs('student.assignments.*')?'active':'' }}" href="{{ route('student.assignments.index') }}"><i class="bi bi-journal-text"></i><span>Assignments</span></a>
            <a class="sms-nav-link {{ request()->routeIs('student.timetable')?'active':'' }}" href="{{ route('student.timetable') }}"><i class="bi bi-clock-history"></i><span>Timetable</span></a>
            <a class="sms-nav-link {{ request()->routeIs('student.notices')?'active':'' }}" href="{{ route('student.notices') }}"><i class="bi bi-megaphone"></i><span>Notices</span></a>
            <a class="sms-nav-link {{ request()->routeIs('student.leaves.*')?'active':'' }}" href="{{ route('student.leaves.index') }}"><i class="bi bi-calendar2-minus"></i><span>Leave</span></a>
            <a class="sms-nav-link {{ request()->routeIs('student.id-card')?'active':'' }}" href="{{ route('student.id-card') }}"><i class="bi bi-person-badge"></i><span>ID Card</span></a>
        @endif
        <span class="sms-menu-label">ACCOUNT</span>
        <a class="sms-nav-link {{ request()->routeIs('profile.*')?'active':'' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i><span>Profile</span></a>
        <a class="sms-nav-link {{ request()->routeIs('password.change*')?'active':'' }}" href="{{ route('password.change') }}"><i class="bi bi-key"></i><span>Change Password</span></a>
    </div>
    <div class="sms-sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="sms-logout-button"><i class="bi bi-box-arrow-right"></i> Logout</button></form>
    </div>
</aside>
<div class="sms-main-wrapper">
    <header class="sms-topbar">
        <div class="sms-topbar-left">
            <button class="sms-sidebar-toggle" id="sidebarToggle" type="button"><i class="bi bi-list"></i></button>
            <div><h5>@yield('page-title','Dashboard')</h5><span>@yield('page-subtitle', ucfirst($user->role).' Panel')</span></div>
        </div>
        <div class="sms-topbar-right">
            <div class="dropdown">
                <button class="sms-user-menu dropdown-toggle" data-bs-toggle="dropdown">
                    <span class="sms-user-avatar">{{ strtoupper(substr($user->name,0,1)) }}</span>
                    <span class="sms-user-information"><strong>{{ $user->name }}</strong><small>{{ ucfirst($user->role) }}</small></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end sms-user-dropdown">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('password.change') }}"><i class="bi bi-key"></i> Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button></form></li>
                </ul>
            </div>
        </div>
    </header>
    <main class="sms-main-content">
        @if(session('success'))<div class="alert alert-success sms-alert"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>@endif
        @if(session('status'))<div class="alert alert-success sms-alert"><i class="bi bi-check-circle-fill"></i>{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger sms-alert"><i class="bi bi-exclamation-circle-fill"></i><div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div></div>@endif
        @yield('content')
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/sms.js') }}"></script>
@stack('scripts')
</body>
</html>
