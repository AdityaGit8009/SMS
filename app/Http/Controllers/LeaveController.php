<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role === 'admin') {
            $items = LeaveRequest::with(['user', 'reviewer'])->latest()->paginate(25);
            return view('admin.leaves.index', compact('items'));
        }

        $items = LeaveRequest::where('user_id', $request->user()->id)->latest()->paginate(20);
        return view('leave.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);
        $data['user_id'] = $request->user()->id;
        $data['attachment'] = $request->file('attachment')?->store('leave-attachments', 'public');
        LeaveRequest::create($data);
        return back()->with('success', 'Leave request submitted.');
    }

    public function review(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($request->user()->role === 'admin', 403);
        $data = $request->validate(['status' => ['required', 'in:approved,rejected'], 'review_note' => ['nullable', 'string']]);
        $leaveRequest->update([
            'status' => $data['status'],
            'review_note' => $data['review_note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);
        return back()->with('success', 'Leave request updated.');
    }
}
