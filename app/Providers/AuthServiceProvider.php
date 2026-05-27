<?php

namespace App\Providers;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Classroom;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\Payment;
use App\Models\PaymentTitle;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Policies\AcademicYearPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\ClassroomPolicy;
use App\Policies\NotificationPreferencePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PaymentTitlePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\StudentPolicy;
use App\Policies\UserPolicy;
use App\Policies\WhatsAppConversationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        AcademicYear::class => AcademicYearPolicy::class,
        Student::class => StudentPolicy::class,
        Classroom::class => ClassroomPolicy::class,
        Payment::class => PaymentPolicy::class,
        PaymentTitle::class => PaymentTitlePolicy::class,
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        NotificationPreference::class => NotificationPreferencePolicy::class,
        WhatsAppConversation::class => WhatsAppConversationPolicy::class,
    ];

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('superadmin')) {
                return true;
            }

            return null;
        });

        Gate::define('view-students', function ($user) {
            return $user->hasPermissionTo('view-students') || $user->hasRole('admin');
        });

        Gate::define('create-students', function ($user) {
            return $user->hasPermissionTo('create-students') || $user->hasRole('admin');
        });

        Gate::define('edit-students', function ($user, ?Student $student = null) {
            return $user->hasPermissionTo('update-students') || $user->hasRole('admin');
        });

        Gate::define('delete-students', function ($user, ?Student $student = null) {
            return $user->hasPermissionTo('delete-students') || $user->hasRole('admin');
        });

        Gate::define('view-payments', function ($user) {
            return $user->hasPermissionTo('view-payments') || $user->hasRole(['admin', 'finance']);
        });

        Gate::define('create-payments', function ($user) {
            return $user->hasPermissionTo('create-payments') || $user->hasRole(['admin', 'finance']);
        });

        Gate::define('edit-payments', function ($user, ?Payment $payment = null) {
            return $user->hasPermissionTo('update-payments') || $user->hasRole(['admin', 'finance']);
        });

        Gate::define('delete-payments', function ($user, ?Payment $payment = null) {
            return $user->hasPermissionTo('delete-payments') || $user->hasRole('admin');
        });

        Gate::define('view-notifications', function ($user) {
            return true;
        });

        Gate::define('mark-notification-read', function ($user, ?Notification $notification = null) {
            if ($notification) {
                return $notification->user_id === $user->id;
            }

            return true;
        });

        Gate::define('view-conversations', function ($user) {
            return $user->hasPermissionTo('view-whatsapp-conversations') || $user->hasRole(['admin', 'staff']);
        });

        Gate::define('reply-conversation', function ($user, ?WhatsAppConversation $conversation = null) {
            return $user->hasPermissionTo('reply-whatsapp-messages') || $user->hasRole(['admin', 'staff']);
        });

        Gate::define('manage-conversations', function ($user) {
            return $user->hasPermissionTo('manage-whatsapp-conversations') || $user->hasRole('admin');
        });
    }
}
