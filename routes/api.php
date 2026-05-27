<?php

use App\Http\Controllers\Api\ClassroomApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\V1\WhatsAppWebhookController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// =========================================================================
// Meta WhatsApp Webhook (NO AUTH - required by Meta)
// =========================================================================
Route::prefix('v1/webhook')->name('v1.webhook.')->middleware('throttle:whatsapp-webhook')->group(function () {
    Route::get('whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.verify');
    Route::post('whatsapp', [WhatsAppWebhookController::class, 'handle'])->name('whatsapp.handle');
});

// =========================================================================
// API Token Management (NO AUTH for token creation)
// =========================================================================
Route::post('sanctum/token', function (Request $request) {
    if ($request->user()) {
        $user = $request->user();
    } else {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'code' => 401,
            ], 401);
        }
    }

    $deviceName = $request->input('device_name', 'API Token');
    $token = $user->createToken($deviceName);

    return response()->json([
        'success' => true,
        'code' => 200,
        'message' => 'Token created successfully',
        'data' => ['token' => $token->plainTextToken],
        'timestamp' => now(),
    ]);
})->name('api.tokens.create');

Route::middleware(['auth:sanctum', 'throttle:api-general'])->group(function () {
    // User endpoint
    Route::get('user', function (Request $request) {
        return $request->user();
    })->name('api.user');

    // Token management
    Route::get('tokens', function (Request $request) {
        return $request->user()->tokens;
    })->name('api.tokens.index');

    Route::delete('tokens/{id}', function (Request $request, $id) {
        $request->user()->tokens()->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Token deleted successfully',
            'data' => null,
            'timestamp' => now(),
        ]);
    })->name('api.tokens.destroy');

    // WhatsApp Management (Admin only)
    Route::get('v1/webhook/whatsapp/templates', [WhatsAppWebhookController::class, 'getTemplate'])->name('v1.webhook.whatsapp.templates')->middleware('role:admin');
    Route::get('v1/webhook/whatsapp/history', [WhatsAppWebhookController::class, 'getMessagesHistory'])->name('v1.webhook.whatsapp.history')->middleware('role:admin');

    // Classrooms API
    Route::get('classrooms', [ClassroomApiController::class, 'index'])->name('api.classrooms.index');
    Route::get('classrooms/{classroom}', [ClassroomApiController::class, 'show'])->name('api.classrooms.show');
    Route::post('classrooms', [ClassroomApiController::class, 'store'])->name('api.classrooms.store')->middleware('can:create,App\\Models\\Classroom');
    Route::put('classrooms/{classroom}', [ClassroomApiController::class, 'update'])->name('api.classrooms.update')->middleware('can:update,App\Models\Classroom');
    Route::delete('classrooms/{classroom}', [ClassroomApiController::class, 'destroy'])->name('api.classrooms.destroy')->middleware('can:delete,App\Models\Classroom');

    // Students API
    Route::get('students', [StudentApiController::class, 'index'])->name('api.students.index')->middleware('role:admin|teacher');
    Route::get('students/{student}', [StudentApiController::class, 'show'])->name('api.students.show')->middleware('role:admin|teacher');
    Route::post('students', [StudentApiController::class, 'store'])->name('api.students.store')->middleware('role:admin');
    Route::put('students/{student}', [StudentApiController::class, 'update'])->name('api.students.update')->middleware('role:admin');
    Route::delete('students/{student}', [StudentApiController::class, 'destroy'])->name('api.students.destroy')->middleware('role:admin');

    // Payments API
    Route::get('payments', [PaymentApiController::class, 'index'])->name('api.payments.index')->middleware('role:admin|finance');
    Route::get('payments/{payment}', [PaymentApiController::class, 'show'])->name('api.payments.show')->middleware('role:admin|finance');
    Route::post('payments', [PaymentApiController::class, 'store'])->name('api.payments.store')->middleware('role:admin|finance');
    Route::put('payments/{payment}', [PaymentApiController::class, 'update'])->name('api.payments.update')->middleware('role:admin|finance');
    Route::delete('payments/{payment}', [PaymentApiController::class, 'destroy'])->name('api.payments.destroy')->middleware('role:admin|finance');
});
