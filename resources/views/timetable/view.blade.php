@extends('layouts.panel')
@section('title','Timetable') @section('page-title','My Timetable') @section('page-subtitle','Weekly class schedule')
@section('content')
@foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)<div class="sms-panel mb-3"><div class="sms-panel-header"><div><h5>{{ $day }}</h5><span>Scheduled classes</span></div></div><div class="table-responsive"><table class="table sms-table"><thead><tr><th>Time</th><th>Subject</th><th>Teacher</th><th>Room</th></tr></thead><tbody>@forelse($items->get($day,collect()) as $row)<tr><td>{{ substr($row->start_time,0,5) }} - {{ substr($row->end_time,0,5) }}</td><td>{{ $row->subject->name }}</td><td>{{ $row->teacher->user->name }}</td><td>{{ $row->room ?: '-' }}</td></tr>@empty<tr><td colspan="4">No classes.</td></tr>@endforelse</tbody></table></div></div>@endforeach
@endsection
