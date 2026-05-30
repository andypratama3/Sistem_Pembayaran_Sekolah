<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as Trail;

// Dashboard
Breadcrumbs::for('dashboard.index', function (Trail $trail) {
    $trail->push('Home', route('dashboard.index'));
});

// Dashboard > Settings
Breadcrumbs::for('dashboard.settings', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Settings');
});

// ── Audit Log ─────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.audit_log.index', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Audit Log', route('dashboard.audit_log.index'));
});

// ── Roles ─────────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.settings.roles', function (Trail $trail) {
    $trail->parent('dashboard.settings');
    $trail->push('Roles', route('dashboard.settings.roles.index'));
});

Breadcrumbs::for('dashboard.settings.roles.index', function (Trail $trail) {
    $trail->parent('dashboard.settings.roles');
});

Breadcrumbs::for('dashboard.settings.roles.create', function (Trail $trail) {
    $trail->parent('dashboard.settings.roles');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.settings.roles.edit', function (Trail $trail) {
    $trail->parent('dashboard.settings.roles');
    $trail->push('Edit');
});

Breadcrumbs::for('dashboard.settings.roles.show', function (Trail $trail, $role) {
    $trail->parent('dashboard.settings.roles');
    $trail->push('Detail');
});

// ── Permissions ───────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.settings.permissions', function (Trail $trail) {
    $trail->parent('dashboard.settings');
    $trail->push('Permission', route('dashboard.settings.permissions.index'));
});

Breadcrumbs::for('dashboard.settings.permissions.index', function (Trail $trail) {
    $trail->parent('dashboard.settings.permissions');
});

Breadcrumbs::for('dashboard.settings.permissions.create', function (Trail $trail) {
    $trail->parent('dashboard.settings.permissions');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.settings.permissions.show', function (Trail $trail, $permission) {
    $trail->parent('dashboard.settings.permissions');
    $trail->push('Detail');
});

Breadcrumbs::for('dashboard.settings.permissions.edit', function (Trail $trail, $permission) {
    $trail->parent('dashboard.settings.permissions');
    $trail->push('Edit');
});

// ── Users ─────────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.settings.users', function (Trail $trail) {
    $trail->parent('dashboard.settings');
    $trail->push('User', route('dashboard.settings.users.index'));
});

Breadcrumbs::for('dashboard.settings.users.index', function (Trail $trail) {
    $trail->parent('dashboard.settings.users');
});

Breadcrumbs::for('dashboard.settings.users.create', function (Trail $trail) {
    $trail->parent('dashboard.settings.users');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.settings.users.show', function (Trail $trail, $user) {
    $trail->parent('dashboard.settings.users');
    $trail->push('Detail');
});

Breadcrumbs::for('dashboard.settings.users.edit', function (Trail $trail, $user) {
    $trail->parent('dashboard.settings.users');
    $trail->push('Edit');
});

// ── Notification Preferences ──────────────────────────────────────────────
Breadcrumbs::for('dashboard.settings.notification-preferences.edit', function (Trail $trail) {
    $trail->parent('dashboard.settings');
    $trail->push('Notification Preferences');
});

// ── Students ──────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.students', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Students', route('dashboard.students.index'));
});

Breadcrumbs::for('dashboard.students.index', function (Trail $trail) {
    $trail->parent('dashboard.students');
});

Breadcrumbs::for('dashboard.students.create', function (Trail $trail) {
    $trail->parent('dashboard.students');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.students.show', function (Trail $trail, $student) {
    $trail->parent('dashboard.students');
    $trail->push($student->name);
});

Breadcrumbs::for('dashboard.students.edit', function (Trail $trail, $student) {
    $trail->parent('dashboard.students');
    $trail->push('Edit');
});

// ── Payments ──────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.payments', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Payments', route('dashboard.payments.index'));
});

Breadcrumbs::for('dashboard.payments.index', function (Trail $trail) {
    $trail->parent('dashboard.payments');
});

Breadcrumbs::for('dashboard.payments.create', function (Trail $trail) {
    $trail->parent('dashboard.payments');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.payments.show', function (Trail $trail, $payment) {
    $trail->parent('dashboard.payments');
    $trail->push('Detail');
});

Breadcrumbs::for('dashboard.payments.edit', function (Trail $trail, $payment) {
    $trail->parent('dashboard.payments');
    $trail->push('Edit');
});

// ── Payment Titles ────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.payment-titles', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Pembayaran', route('dashboard.payments.index'));
    $trail->push('Judul Pembayaran', route('dashboard.payment-titles.index'));
});

Breadcrumbs::for('dashboard.payment-titles.index', function (Trail $trail) {
    $trail->parent('dashboard.payment-titles');
});

Breadcrumbs::for('dashboard.payment-titles.create', function (Trail $trail) {
    $trail->parent('dashboard.payment-titles');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.payment-titles.edit', function (Trail $trail, $paymentTitle) {
    $trail->parent('dashboard.payment-titles');
    $trail->push('Edit');
});

Breadcrumbs::for('dashboard.payment-titles.show', function (Trail $trail, $paymentTitle) {
    $trail->parent('dashboard.payment-titles');
    $trail->push('Detail');
});

// ── Classrooms ────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.classrooms', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Kelas', route('dashboard.classrooms.index'));
});

Breadcrumbs::for('dashboard.classrooms.index', function (Trail $trail) {
    $trail->parent('dashboard.classrooms');
});

Breadcrumbs::for('dashboard.classrooms.create', function (Trail $trail) {
    $trail->parent('dashboard.classrooms');
    $trail->push('Tambah Kelas');
});

Breadcrumbs::for('dashboard.classrooms.edit', function (Trail $trail, $classroom) {
    $trail->parent('dashboard.classrooms');
    $trail->push('Edit Kelas');
});

Breadcrumbs::for('dashboard.classrooms.show', function (Trail $trail, $classroom) {
    $trail->parent('dashboard.classrooms');
    $trail->push('Detail Kelas');
});

// ── Academic Years ────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.academic-years', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Academic Years', route('dashboard.academic-years.index'));
});

Breadcrumbs::for('dashboard.academic-years.index', function (Trail $trail) {
    $trail->parent('dashboard.academic-years');
});

// Backward-compatible aliases used by some views.
Breadcrumbs::for('dashboard.academic_years.index', function (Trail $trail) {
    $trail->parent('dashboard.academic-years.index');
});

Breadcrumbs::for('dashboard.academic_years.create', function (Trail $trail) {
    $trail->parent('dashboard.academic-years.index');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.academic-years.create', function (Trail $trail) {
    $trail->parent('dashboard.academic-years');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.academic-years.edit', function (Trail $trail, $academicYearRecord) {
    $trail->parent('dashboard.academic-years');
    $trail->push('Edit');
});

Breadcrumbs::for('dashboard.academic-years.show', function (Trail $trail, $academicYearRecord) {
    $trail->parent('dashboard.index');
    $trail->push('Tahun Akademik', route('dashboard.academic-years.index'));
    $trail->push('Detail');
});

// ── WhatsApp Chat ─────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.whatsapp-chat', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('WhatsApp Chat', route('dashboard.whatsapp-chat.index'));
});

Breadcrumbs::for('dashboard.whatsapp-chat.index', function (Trail $trail) {
    $trail->parent('dashboard.whatsapp-chat');
});

Breadcrumbs::for('dashboard.whatsapp-chat.show', function (Trail $trail, $conversation) {
    $trail->parent('dashboard.whatsapp-chat');
    $trail->push('Conversation');
});

// ── Profile ───────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.profile.edit', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Profile', route('dashboard.profile.edit'));
});

// ── Notifications ─────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.notifications.index', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Notifications', route('dashboard.notifications.index'));
});
