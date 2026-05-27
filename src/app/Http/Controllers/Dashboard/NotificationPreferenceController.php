<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationPreferenceController extends ResourceController
{
    protected static string $permissionResource = 'notification-preferences';

    public function edit()
    {
        $this->authorize('view', auth()->user()->notificationPreference ?? NotificationPreference::class);

        $preferences = auth()->user()->notificationPreference ?? new NotificationPreference;

        return view('dashboard.settings.notification-preferences', compact('preferences'));
    }

    public function update(Request $request)
    {
        $this->authorize('update', auth()->user()->notificationPreference ?? NotificationPreference::class);

        $data = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'whatsapp_notifications' => 'boolean',
            'frequency' => 'required|in:immediate,daily,weekly',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
        ]);

        NotificationPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            $data
        );

        return redirect()->back()->with('success', 'Notification preferences updated successfully.');
    }
}
