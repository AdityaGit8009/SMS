@extends('layouts.panel')
@section('title','Notices') @section('page-title','Notices') @section('page-subtitle','Announcements for you and your course')
@section('content')
<div class="sms-panel">@forelse($items as $n)<div class="sms-notice-card"><span class="sms-role-badge sms-role-student">{{ ucfirst($n->type) }}</span><h5>{{ $n->title }}</h5><p>{{ $n->content }}</p><small>{{ $n->publish_date->format('d M Y') }} · {{ $n->creator?->name ?? 'Administration' }}{{ $n->course?' · '.$n->course->name:'' }}</small></div>@empty<p class="sms-empty">No notices available.</p>@endforelse{{ $items->links() }}</div>
@endsection
