<?php

use App\Http\Controllers\Dashboard\AcademicYearController;
use App\Http\Controllers\Dashboard\AuditLogController;
use App\Http\Controllers\Dashboard\ClassroomController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\MidtransController;
use App\Http\Controllers\Dashboard\NotificationController;
use App\Http\Controllers\Dashboard\NotificationPreferenceController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\PaymentTitleController;
use App\Http\Controllers\Dashboard\PermissionController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\ScheduleController;
use App\Http\Controllers\Dashboard\SearchController;
use App\Http\Controllers\Dashboard\StudentController;
use App\Http\Controllers\Dashboard\StudentImportController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\WhatsAppChatController;
use App\Http\Middleware\CheckUserStatus;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard.index'))->name('home');

Route::get('/locale/{locale}', fn (string $locale) => in_array($locale, config('app.supported_locales', ['en', 'id']), true)
    ? tap(session(['locale' => $locale]), fn () => back())
    : abort(404))->name('locale.switch');

Route::prefix('dashboard')->as('dashboard.')->middleware(['auth', 'verified', CheckUserStatus::class])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
    Route::get('/search', [SearchController::class, 'search'])->name('settings.search');

    Route::prefix('settings')->middleware('role_or_permission:manage settings')->group(function () {
        Route::resource('users', UserController::class)->names('settings.users')->parameters(['users' => 'userRecord']);
        Route::post('users/{userRecord}/status', [UserController::class, 'updateStatus'])->name('settings.users.update-status');

        Route::resource('roles', RoleController::class)->names('settings.roles');
        Route::resource('permission', PermissionController::class)->names('settings.permissions');
        Route::delete('permissions/bulk-destroy', [PermissionController::class, 'bulkDestroy'])->name('settings.permissions.bulk-destroy');

        Route::get('notification-preferences', [NotificationPreferenceController::class, 'edit'])->name('settings.notification-preferences.edit');
        Route::put('notification-preferences', [NotificationPreferenceController::class, 'update'])->name('settings.notification-preferences.update');
    });

    Route::middleware(['can:view-students'])->get('students/search/available', [StudentController::class, 'searchAvailable'])->name('students.search');
    Route::resource('students', StudentController::class)->parameters(['students' => 'studentRecord']);

    Route::middleware(['can:create-students'])->prefix('students/import')->as('students.import.')->group(function () {
        Route::post('/', [StudentImportController::class, 'store'])->name('store');
        Route::get('progress/{batchId}', [StudentImportController::class, 'progress'])->name('progress');
        Route::get('data', [StudentImportController::class, 'data'])->name('data');
        Route::get('{batchId}/status', [StudentImportController::class, 'status'])->name('status');
        Route::delete('{batchId}', [StudentImportController::class, 'cancel'])->name('cancel');
        Route::get('template/download', [StudentImportController::class, 'downloadTemplate'])->name('template.download');
    });

    Route::post('students/{studentRecord}/status', [StudentController::class, 'updateStatus'])->name('students.update-status');
    Route::post('students/{studentRecord}/assign-class', [StudentController::class, 'assignClassroom'])->name('students.assign-classroom');
    Route::post('students/{studentRecord}/unassign-class', [StudentController::class, 'unassignClassroom'])->name('students.unassign-classroom');

    Route::resource('classrooms', ClassroomController::class)->parameters(['classrooms' => 'classroomRecord']);
    Route::get('classrooms/{classroomRecord}/students', [ClassroomController::class, 'getStudents'])->name('classrooms.get-students');
    Route::post('classrooms/{classroomRecord}/add-student', [ClassroomController::class, 'addStudent'])->name('classrooms.add-student');
    Route::delete('classrooms/{classroomRecord}/remove-student/{studentRecord}', [ClassroomController::class, 'removeStudent'])->name('classrooms.remove-student');

    Route::resource('academic-years', AcademicYearController::class)->parameters(['academic-years' => 'academicYearRecord']);

    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
    });

    Route::get('payments/outstanding/{studentRecord}', [PaymentController::class, 'getOutstanding'])->name('payments.outstanding');
    Route::resource('payments', PaymentController::class)->parameters(['payments' => 'paymentRecord']);
    Route::resource('payment-titles', PaymentTitleController::class)->parameters(['payment-titles' => 'paymentTitleRecord']);
    Route::post('payments/{paymentRecord}/mark-paid', [PaymentController::class, 'markPaid'])->name('payments.mark-paid');

    Route::get('/notification', [NotificationController::class, 'list_notification'])->name('notifications.list');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/read/{notificationRecord}', [NotificationController::class, 'read'])->name('notifications.read');
    Route::delete('/notifications/{notificationRecord}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit_log.index');
    Route::get('audit-log/datatable', [AuditLogController::class, 'datatable'])->name('audit_log.datatable');
    Route::get('audit-log/{auditLogRecord}', [AuditLogController::class, 'show'])->name('audit_log.show');

    Route::prefix('midtrans')->name('midtrans.')->group(function () {
        Route::post('snap-token/{paymentRecord}', [MidtransController::class, 'getSnapToken'])->name('snap-token');
        Route::get('status/{chargeId}', [MidtransController::class, 'getStatus'])->name('status');
        Route::post('refund/{paymentRecord}', [MidtransController::class, 'refund'])->name('refund');
        Route::post('cancel/{paymentRecord}', [MidtransController::class, 'cancel'])->name('cancel');
        Route::get('payment-methods', [MidtransController::class, 'getPaymentMethods'])->name('payment-methods');
    });

    Route::get('payment/success/{chargeId}', [MidtransController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('payment/unfinish/{chargeId}', [MidtransController::class, 'paymentUnfinish'])->name('payment.unfinish');
    Route::get('payment/error/{chargeId}', [MidtransController::class, 'paymentError'])->name('payment.error');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('whatsapp-chat')->name('whatsapp-chat.')->group(function () {
        Route::get('/', [WhatsAppChatController::class, 'index'])->name('index');
        Route::get('/{conversationRecord}', [WhatsAppChatController::class, 'show'])->name('show');
        Route::post('/{conversationRecord}/send', [WhatsAppChatController::class, 'sendMessage'])->name('send');
        Route::post('/{conversationRecord}/send-template', [WhatsAppChatController::class, 'sendTemplate'])->name('send-template');
        Route::post('/{conversationRecord}/assign', [WhatsAppChatController::class, 'assign'])->name('assign');
        Route::post('/{conversationRecord}/close', [WhatsAppChatController::class, 'close'])->name('close');
        Route::post('/{conversationRecord}/reopen', [WhatsAppChatController::class, 'reopen'])->name('reopen');
        Route::get('/{conversationRecord}/messages', [WhatsAppChatController::class, 'getMessages'])->name('get-messages');
        Route::get('/{conversationRecord}/search', [WhatsAppChatController::class, 'searchMessages'])->name('search-messages');
        Route::get('/templates/by-category', [WhatsAppChatController::class, 'getTemplates'])->name('get-templates');
        Route::post('/messages/{messageRecord}/edit', [WhatsAppChatController::class, 'editMessage'])->name('edit-message');
        Route::post('/messages/{messageRecord}/delete', [WhatsAppChatController::class, 'deleteMessage'])->name('delete-message');
        Route::post('/messages/{messageRecord}/react', [WhatsAppChatController::class, 'addReaction'])->name('add-reaction');
        Route::post('/messages/{messageRecord}/read', [WhatsAppChatController::class, 'markAsRead'])->name('mark-read');
        Route::post('/{conversationRecord}/mark-read', [WhatsAppChatController::class, 'markConversationAsRead'])->name('mark-conversation-read');
    });

    Route::post('cache/flush', function () {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Cache berhasil dibersihkan.']);
        }

        return back()->with('success', 'Cache berhasil dibersihkan.');
    })->name('cache.flush')->middleware('role:superadmin|admin');
});

Route::post('midtrans/notification', [MidtransController::class, 'notification'])
    ->withoutMiddleware([VerifyCsrfToken::class, CheckUserStatus::class])
    ->middleware('throttle:midtrans-webhook')
    ->name('midtrans.notification');

require __DIR__.'/auth.php';
