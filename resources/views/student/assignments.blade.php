@extends('layouts.panel')
@section('title', 'Assignments')
@section('page-title', 'Assignments')
@section('page-subtitle', 'View coursework and submit your work')

@section('content')
<div class="row g-4">
    @forelse($items as $assignment)
        @php($submission = $submissions->get($assignment->id))
        <div class="col-lg-6">
            <div class="sms-panel h-100">
                <div class="sms-panel-header">
                    <div>
                        <h5>{{ $assignment->title }}</h5>
                        <span>{{ $assignment->subject->name }} · Due {{ $assignment->due_date->format('d M Y h:i A') }}</span>
                    </div>
                    <span class="sms-status {{ $submission ? 'active' : 'warning' }}">{{ $submission ? ucfirst($submission->status) : 'Pending' }}</span>
                </div>
                <p class="sms-muted-text">{{ $assignment->description ?: 'No description.' }}</p>
                @if($assignment->file_path)
                    <a class="d-inline-block mb-3" href="{{ asset('storage/'.$assignment->file_path) }}" target="_blank"><i class="bi bi-paperclip"></i> Download Assignment</a>
                @endif
                @if($submission)
                    <div class="sms-submission-box">
                        <strong>Submitted:</strong> {{ $submission->submitted_at->format('d M Y h:i A') }}<br>
                        <strong>Marks:</strong> {{ $submission->marks !== null ? $submission->marks.' / '.$assignment->max_marks : 'Not graded yet' }}
                        @if($submission->remarks)
                            <br><strong>Remarks:</strong> {{ $submission->remarks }}
                        @endif
                    </div>
                @endif
                <form class="mt-3" method="POST" enctype="multipart/form-data" action="{{ route('student.assignments.submit', $assignment) }}">
                    @csrf
                    <div class="d-flex gap-2">
                        <input type="file" class="form-control sms-plain-control" name="file" aria-label="File for {{ $assignment->title }}" required>
                        <button class="btn btn-dark">{{ $submission ? 'Resubmit' : 'Submit' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="sms-panel"><p class="sms-empty">No assignments available.</p></div></div>
    @endforelse
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
