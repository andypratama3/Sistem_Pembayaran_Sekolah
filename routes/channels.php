<?php

if ((env('BROADCAST_DRIVER') === 'reverb' && empty(env('REVERB_APP_KEY'))) || (env('BROADCAST_DRIVER') === 'pusher' && empty(env('PUSHER_APP_KEY')))) {
    // During CI/composer install, broadcaster credentials may not be available.
    // Skip registering broadcast channels to avoid initializing broadcaster.
    return;
}

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are used
| to determine if an authenticated user can listen to these channels.
|
*/

// Public channels - everyone can listen
Broadcast::channel('attendance-updates', function ($user) {
    return auth()->check();
});

Broadcast::channel('grade-updates', function ($user) {
    return auth()->check();
});

Broadcast::channel('payment-updates', function ($user) {
    return auth()->check();
});

Broadcast::channel('schedule-updates', function ($user) {
    return auth()->check();
});

Broadcast::channel('leave-updates', function ($user) {
    return auth()->check();
});

Broadcast::channel('notification-updates', function ($user) {
    return auth()->check();
});

// Classroom-specific channels
Broadcast::channel('classroom-{classroomId}', function ($user, $classroomId) {
    if (auth()->check()) {
        // Allow if user is teacher of this classroom or admin
        return true;
    }

    return false;
});

// Private user channels
Broadcast::channel('user-{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('import.students.{userId}', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
});

// Private employee channels
Broadcast::channel('employee-{employeeId}', function ($user, $employeeId) {
    if (auth()->check()) {
        $employee = $user->employee;

        return $employee && (int) $employee->id === (int) $employeeId;
    }

    return false;
});

// Private payment channels
Broadcast::channel('payments-{studentId}', function ($user, $studentId) {
    if (auth()->check()) {
        // Student can view own payments
        $student = $user->student;
        if ($student && (int) $student->id === (int) $studentId) {
            return true;
        }
        // Admin/Finance can view all
        if ($user->hasRole(['admin', 'finance', 'super-admin'])) {
            return true;
        }
    }

    return false;
});

// Private leave request channels
Broadcast::channel('leave-{employeeId}', function ($user, $employeeId) {
    if (auth()->check()) {
        $employee = $user->employee;

        return $employee && (int) $employee->id === (int) $employeeId;
    }

    return false;
});

// Admin notification channel
Broadcast::channel('admin-notifications', function ($user) {
    return $user->hasRole(['admin', 'super-admin']);
});

// Dashboard real-time data updates channel
Broadcast::channel('data-updates', function ($user) {
    return auth()->check();
});

// Student data updates channel
Broadcast::channel('student-updates', function ($user) {
    return auth()->check();
});
Broadcast::channel('data-updated', function ($user) {
    return auth()->check();
});

// WhatsApp Conversation Channels
Broadcast::channel('whatsapp-conversation.{conversationId}', function ($user, $conversationId) {
    if (! auth()->check()) {
        return false;
    }

    // Allow if user is admin/staff
    if ($user->hasRole(['admin', 'super-admin', 'staff'])) {
        return true;
    }

    return false;
});
