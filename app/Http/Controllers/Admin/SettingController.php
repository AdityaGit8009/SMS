<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = Setting::pluck('value', 'key');
        $sessions = AcademicSession::latest('start_date')->get();

        return view('admin.settings.edit', compact('settings', 'sessions'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'college_name' => ['required', 'max:191'],
            'college_email' => ['nullable', 'email'],
            'college_phone' => ['nullable', 'max:30'],
            'college_website' => ['nullable', 'max:191'],
            'college_address' => ['nullable', 'string'],
            'currency' => ['required', 'max:10'],
            'date_format' => ['required', 'max:30'],
            'current_academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $data) {
            foreach ($data as $key => $value) {
                if (in_array($key, ['logo', 'current_academic_session_id'], true)) {
                    continue;
                }
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }

            if (! empty($data['current_academic_session_id'])) {
                AcademicSession::query()->update(['is_current' => false]);
                AcademicSession::whereKey($data['current_academic_session_id'])->update(['is_current' => true]);
                Setting::updateOrCreate(
                    ['key' => 'current_academic_session_id'],
                    ['value' => (string) $data['current_academic_session_id']]
                );
            }

            if ($request->hasFile('logo')) {
                $old = Setting::valueOf('logo');
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $path = $request->file('logo')->store('settings', 'public');
                Setting::updateOrCreate(['key' => 'logo'], ['value' => $path]);
            }
        });

        return back()->with('success', 'Settings updated.');
    }
}
