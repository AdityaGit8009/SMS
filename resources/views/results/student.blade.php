@extends('layouts.panel')
@section('title','Result') @section('page-title','Student Result') @section('page-subtitle',$student->user->name.' · '.$student->roll_no)
@section('content')
@php
    $resultSummaries = collect();
    foreach ($marks as $examId => $examMarks) {
        $exam = $examMarks->first()->examSchedule->exam;
        $obtained = (float) $examMarks->sum('total_marks');
        $maximum = (float) $examMarks->sum(fn($m) => (float) $m->examSchedule->max_marks);
        $percentage = $maximum ? round(($obtained / $maximum) * 100, 2) : 0;
        $pass = $examMarks->every(fn($m) => (float)$m->total_marks >= (float)$m->examSchedule->passing_marks);
        $grade = $percentage >= 90 ? 'A+' : ($percentage >= 80 ? 'A' : ($percentage >= 70 ? 'B+' : ($percentage >= 60 ? 'B' : ($percentage >= 50 ? 'C' : ($percentage >= 40 ? 'D' : 'F')))));
        $weightedPoints = 0; $totalCredits = 0;
        foreach ($examMarks as $m) {
            $max = (float) $m->examSchedule->max_marks;
            $subjectPercent = $max ? ((float)$m->total_marks / $max) * 100 : 0;
            $gp = $subjectPercent >= 90 ? 10 : ($subjectPercent >= 80 ? 9 : ($subjectPercent >= 70 ? 8 : ($subjectPercent >= 60 ? 7 : ($subjectPercent >= 50 ? 6 : ($subjectPercent >= 40 ? 5 : 0)))));
            $credits = max(1, (int) $m->examSchedule->subject->credits);
            $weightedPoints += $gp * $credits; $totalCredits += $credits;
        }
        $sgpa = $totalCredits ? round($weightedPoints / $totalCredits, 2) : 0;
        $resultSummaries->push(compact('exam','examMarks','obtained','maximum','percentage','pass','grade','sgpa'));
    }
    $cgpa = $resultSummaries->count() ? round($resultSummaries->avg('sgpa'), 2) : 0;
@endphp
<div class="sms-page-actions no-print"><div><h2>Academic Result</h2><p>{{ $student->course->name }} · {{ $student->semester->name }} · {{ $student->academicSession->name }} · CGPA {{ number_format($cgpa,2) }}</p></div><button onclick="window.print()" class="btn btn-dark"><i class="bi bi-printer"></i> Print / Save PDF</button></div>
@forelse($resultSummaries as $r)
<div class="sms-panel sms-result-block"><div class="sms-result-header"><div><h4>{{ $r['exam']->name }}</h4><p>{{ $student->user->name }} · {{ $student->roll_no }}</p></div><div><span class="sms-status {{ $r['pass']?'active':'inactive' }}">{{ $r['pass']?'PASS':'FAIL' }}</span></div></div><div class="table-responsive"><table class="table sms-table"><thead><tr><th>Subject</th><th>Credits</th><th>Theory</th><th>Practical</th><th>Total</th><th>Max</th><th>Pass</th></tr></thead><tbody>@foreach($r['examMarks'] as $m)<tr><td>{{ $m->examSchedule->subject->name }}</td><td>{{ $m->examSchedule->subject->credits }}</td><td>{{ $m->theory_marks }}</td><td>{{ $m->practical_marks }}</td><td><strong>{{ $m->total_marks }}</strong></td><td>{{ $m->examSchedule->max_marks }}</td><td>{{ $m->examSchedule->passing_marks }}</td></tr>@endforeach</tbody></table></div><div class="sms-result-summary"><div><span>Obtained</span><strong>{{ $r['obtained'] }} / {{ $r['maximum'] }}</strong></div><div><span>Percentage</span><strong>{{ $r['percentage'] }}%</strong></div><div><span>Grade</span><strong>{{ $r['grade'] }}</strong></div><div><span>SGPA</span><strong>{{ number_format($r['sgpa'],2) }}</strong></div><div><span>CGPA</span><strong>{{ number_format($cgpa,2) }}</strong></div><div><span>Result</span><strong>{{ $r['pass']?'Pass':'Fail' }}</strong></div></div></div>
@empty<div class="sms-panel"><p class="sms-empty">No marks/results available.</p></div>@endforelse
<p class="sms-muted-text no-print">SGPA/CGPA in this starter implementation use a 10-point credit-weighted grade mapping. Replace the formula if your institution follows a different university regulation.</p>
@endsection
