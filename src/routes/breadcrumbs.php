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

// Dashboard > Settings > Roles (BASE)
Breadcrumbs::for('dashboard.settings.roles', function (Trail $trail) {
    $trail->parent('dashboard.settings');
    $trail->push('Roles', route('dashboard.settings.roles.index'));
});

// Roles children
Breadcrumbs::for('dashboard.settings.roles.index', function (Trail $trail) {
    $trail->parent('dashboard.settings.roles');
});

Breadcrumbs::for('dashboard.audit_log.index', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Audit Log', route('dashboard.audit_log.index'));
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

// ── Permission ────────────────────────────────────────────────────────────────
// dashboard.settings.permissions (BASE)
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

// ── Staff Positions ────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.settings.staff-positions', function (Trail $trail) {
    $trail->parent('dashboard.settings');
    $trail->push('Jabatan Staf', route('dashboard.settings.staff-positions.index'));
});

Breadcrumbs::for('dashboard.settings.staff-positions.index', function (Trail $trail) {
    $trail->parent('dashboard.settings.staff-positions');
});

Breadcrumbs::for('dashboard.settings.staff-positions.create', function (Trail $trail) {
    $trail->parent('dashboard.settings.staff-positions');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.settings.staff-positions.show', function (Trail $trail, $staffPosition) {
    $trail->parent('dashboard.settings.staff-positions');
    $trail->push($staffPosition->name);
});

Breadcrumbs::for('dashboard.settings.staff-positions.edit', function (Trail $trail, $staffPosition) {
    $trail->parent('dashboard.settings.staff-positions');
    $trail->push('Edit');
});

// ── User ────────────────────────────────────────────────────────────────
// dashboard.settings.users (BASE)
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

// ── Students ────────────────────────────────────────────────────────────────
// dashboard.students (BASE)
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

// ── Attendance ────────────────────────────────────────────────────────────────
// dashboard.attendances (BASE)
Breadcrumbs::for('dashboard.attendances', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Attendance', route('dashboard.attendances.index'));
});

Breadcrumbs::for('dashboard.attendances.index', function (Trail $trail) {
    $trail->parent('dashboard.attendances');
});

Breadcrumbs::for('dashboard.attendances.create', function (Trail $trail) {
    $trail->parent('dashboard.attendances');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.attendances.show', function (Trail $trail, $attendance) {
    $trail->parent('dashboard.attendances');
    $trail->push('Detail');
});

Breadcrumbs::for('dashboard.attendances.edit', function (Trail $trail, $attendance) {
    $trail->parent('dashboard.attendances');
    $trail->push('Edit');
});

// ── Grades ────────────────────────────────────────────────────────────────
// dashboard.grades (BASE)
Breadcrumbs::for('dashboard.grades', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Grades', route('dashboard.grades.index'));
});

Breadcrumbs::for('dashboard.grades.index', function (Trail $trail) {
    $trail->parent('dashboard.grades');
});

Breadcrumbs::for('dashboard.grades.group-show', function (Trail $trail) {
    $trail->parent('dashboard.grades');
    $trail->push('Detail Kelas');
});

Breadcrumbs::for('dashboard.grades.create', function (Trail $trail) {
    $trail->parent('dashboard.grades');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.grades.show', function (Trail $trail, $grade) {
    $trail->parent('dashboard.grades');
    $trail->push('Detail');
});

Breadcrumbs::for('dashboard.grades.edit', function (Trail $trail, $grade) {
    $trail->parent('dashboard.grades');
    $trail->push('Edit');
});

Breadcrumbs::for('dashboard.grades.bulk-import', function (Trail $trail) {
    $trail->parent('dashboard.grades');
    $trail->push('Import Nilai');
});

Breadcrumbs::for('dashboard.grades.weights.index', function (Trail $trail) {
    $trail->parent('dashboard.grades');
    $trail->push('Konfigurasi Bobot');
});

// ── Payments ────────────────────────────────────────────────────────────────
// dashboard.payments (BASE)
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

// ── Leave Requests ────────────────────────────────────────────────────────────────
// dashboard.leave-requests (BASE)
Breadcrumbs::for('dashboard.leave-requests', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Leave Requests', route('dashboard.leave-requests.index'));
});

Breadcrumbs::for('dashboard.leave-requests.index', function (Trail $trail) {
    $trail->parent('dashboard.leave-requests');
});

Breadcrumbs::for('dashboard.leave-requests.create', function (Trail $trail) {
    $trail->parent('dashboard.leave-requests');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.leave-requests.show', function (Trail $trail, $leaveRequest) {
    $trail->parent('dashboard.leave-requests');
    $trail->push('Detail');
});

Breadcrumbs::for('dashboard.leave-requests.edit', function (Trail $trail, $leaveRequest) {
    $trail->parent('dashboard.leave-requests');
    $trail->push('Edit');
});

// ── Schedules ────────────────────────────────────────────────────────────────
// dashboard.schedules (BASE)
Breadcrumbs::for('dashboard.schedules', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Schedules', route('dashboard.schedules.index'));
});

Breadcrumbs::for('dashboard.schedules.index', function (Trail $trail) {
    $trail->parent('dashboard.schedules');
});
Breadcrumbs::for('dashboard.schedules.create', function (Trail $trail) {
    $trail->parent('dashboard.schedules');
    $trail->push('Create');
});
Breadcrumbs::for('dashboard.schedules.edit', function (Trail $trail, $schedule) {
    $trail->parent('dashboard.schedules');
    $trail->push('Edit');
});
Breadcrumbs::for('dashboard.schedules.show', function (Trail $trail, $schedule) {
    $trail->parent('dashboard.schedules');
    $subjName = ($schedule->subject ? $schedule->subject->name : 'Schedule');
    $trail->push($subjName);
});

// ── Teachers ────────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.teachers', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Teachers', route('dashboard.teachers.index'));
});
Breadcrumbs::for('dashboard.teachers.index', function (Trail $trail) {
    $trail->parent('dashboard.teachers');
});
Breadcrumbs::for('dashboard.teachers.create', function (Trail $trail) {
    $trail->parent('dashboard.teachers');
    $trail->push('Create');
});
Breadcrumbs::for('dashboard.teachers.show', function (Trail $trail, $teacherRecord) {
    $trail->parent('dashboard.teachers');
    $trail->push($teacherRecord->name);
});
Breadcrumbs::for('dashboard.teachers.edit', function (Trail $trail, $teacherRecord) {
    $trail->parent('dashboard.teachers');
    $trail->push('Edit');
});

// ── Employees ───────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.employees', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Employees', route('dashboard.employees.index'));
});
Breadcrumbs::for('dashboard.employees.index', function (Trail $trail) {
    $trail->parent('dashboard.employees');
});
Breadcrumbs::for('dashboard.employees.create', function (Trail $trail) {
    $trail->parent('dashboard.employees');
    $trail->push('Create');
});
Breadcrumbs::for('dashboard.employees.show', function (Trail $trail, $employeeRecord) {
    $trail->parent('dashboard.employees');
    $trail->push($employeeRecord->name);
});
Breadcrumbs::for('dashboard.employees.edit', function (Trail $trail, $employeeRecord) {
    $trail->parent('dashboard.employees');
    $trail->push('Edit');
});

// ── Employee Attendance ───────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.employee-attendances', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Pegawai', route('dashboard.employees.index'));
    $trail->push('Absensi Pegawai', route('dashboard.employee-attendances.index'));
});
Breadcrumbs::for('dashboard.employee-attendances.index', function (Trail $trail) {
    $trail->parent('dashboard.employee-attendances');
});
Breadcrumbs::for('dashboard.employee-attendances.create', function (Trail $trail) {
    $trail->parent('dashboard.employee-attendances');
    $trail->push('Create');
});
Breadcrumbs::for('dashboard.employee-attendances.show', function (Trail $trail, $attendance) {
    $trail->parent('dashboard.employee-attendances');
    $trail->push('Detail');
});
Breadcrumbs::for('dashboard.employee-attendances.edit', function (Trail $trail, $attendance) {
    $trail->parent('dashboard.employee-attendances');
    $trail->push('Edit');
});

Breadcrumbs::for('dashboard.my-attendance', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Absensi Saya', route('dashboard.my-attendance'));
});

// ── Subjects ────────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.subjects', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Subjects', route('dashboard.subjects.index'));
});
Breadcrumbs::for('dashboard.subjects.index', function (Trail $trail) {
    $trail->parent('dashboard.subjects');
});
Breadcrumbs::for('dashboard.subjects.create', function (Trail $trail) {
    $trail->parent('dashboard.subjects');
    $trail->push('Create');
});
Breadcrumbs::for('dashboard.subjects.edit', function (Trail $trail, $subjectRecord) {
    $trail->parent('dashboard.subjects');
    $trail->push('Edit');
});
Breadcrumbs::for('dashboard.subjects.show', function (Trail $trail, $subjectRecord) {
    $trail->parent('dashboard.subjects');
    $trail->push($subjectRecord->name ?? 'Subject');
});

// ── Classrooms ──────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.classrooms', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Classrooms', route('dashboard.classrooms.index'));
});
Breadcrumbs::for('dashboard.classrooms.index', function (Trail $trail) {
    $trail->parent('dashboard.classrooms');
});
Breadcrumbs::for('dashboard.classrooms.create', function (Trail $trail) {
    $trail->parent('dashboard.classrooms');
    $trail->push('Create');
});
Breadcrumbs::for('dashboard.classrooms.show', function (Trail $trail, $classroom) {
    $trail->parent('dashboard.classrooms');
    $trail->push($classroom->name);
});
Breadcrumbs::for('dashboard.classrooms.edit', function (Trail $trail, $classroom) {
    $trail->parent('dashboard.classrooms');
    $trail->push('Edit');
});

// ── Academic Years ─────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.academic-years', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Academic Years', route('dashboard.academic-years.index'));
});
Breadcrumbs::for('dashboard.academic-years.index', function (Trail $trail) {
    $trail->parent('dashboard.academic-years');
});

// Backward-compatible alias used by some views.
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

// ── Academic Calendar ───────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.academic-calendar', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Kalender Akademik', route('dashboard.academic-calendar.index'));
});

Breadcrumbs::for('dashboard.academic-calendar.index', function (Trail $trail) {
    $trail->parent('dashboard.academic-calendar');
});

Breadcrumbs::for('dashboard.academic-calendar.create', function (Trail $trail) {
    $trail->parent('dashboard.academic-calendar');
    $trail->push('Buat Kalender');
});

Breadcrumbs::for('dashboard.academic-calendar.show', function (Trail $trail, $calendarModel) {
    $trail->parent('dashboard.academic-calendar');
    $trail->push($calendarModel->academicYear?->name ?? 'Kalender Akademik');
});

Breadcrumbs::for('dashboard.academic-calendar.edit', function (Trail $trail, $calendar) {
    $trail->parent('dashboard.academic-calendar.show', $calendar);
    $trail->push('Edit');
});

Breadcrumbs::for('dashboard.academic-calendar.edit-event', function (Trail $trail, $event) {
    $trail->parent('dashboard.academic-calendar.show', $event->calendar);
    $trail->push('Edit Acara');
});

// ── Report Cards ────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.report-cards', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Rapot', route('dashboard.report-cards.index'));
});

Breadcrumbs::for('dashboard.report-cards.index', function (Trail $trail) {
    $trail->parent('dashboard.report-cards');
});

Breadcrumbs::for('dashboard.report-cards.create', function (Trail $trail) {
    $trail->parent('dashboard.report-cards');
    $trail->push('Buat Rapot');
});

Breadcrumbs::for('dashboard.report-cards.show', function (Trail $trail, $reportCard) {
    $trail->parent('dashboard.report-cards');
    $trail->push('Detail');
});

Breadcrumbs::for('dashboard.report-cards.edit', function (Trail $trail, $reportCard) {
    $trail->parent('dashboard.report-cards');
    $trail->push('Edit');
});

// ── Payment Titles Show ────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.payment-titles.show', function (Trail $trail, $paymentTitle) {
    $trail->parent('dashboard.payment-titles');
    $trail->push('Detail');
});

// ── Academic Years Show ────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.academic-years.show', function (Trail $trail, $academicYearRecord) {
    $trail->parent('dashboard.index');
    $trail->push('Tahun Akademik', route('dashboard.academic-years.index'));
    $trail->push('Detail');
});

// ── P5 Assessment (Profil Pelajar Pancasila) ───────────────────────────────────
Breadcrumbs::for('dashboard.p5-assessment', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Penilaian P5', route('dashboard.p5-assessment.index'));
});

Breadcrumbs::for('dashboard.p5-assessment.index', function (Trail $trail) {
    $trail->parent('dashboard.p5-assessment');
});

Breadcrumbs::for('dashboard.p5-assessment.create', function (Trail $trail, $student) {
    $trail->parent('dashboard.p5-assessment');
    $trail->push('Penilaian '.$student->name);
});

Breadcrumbs::for('dashboard.p5-assessment.show', function (Trail $trail, $assessment) {
    $trail->parent('dashboard.p5-assessment');
    $trail->push('Detail Penilaian');
});

Breadcrumbs::for('dashboard.p5-assessment.edit', function (Trail $trail, $assessment) {
    $trail->parent('dashboard.p5-assessment');
    $trail->push('Edit Penilaian');
});

Breadcrumbs::for('dashboard.p5-assessment.bulk-create', function (Trail $trail, $classroom) {
    $trail->parent('dashboard.p5-assessment');
    $trail->push('Penilaian Massal - '.$classroom->name);
});

Breadcrumbs::for('dashboard.p5-assessment.report', function (Trail $trail, $student) {
    $trail->parent('dashboard.p5-assessment');
    $trail->push('Laporan P5 - '.$student->name);
});

// ── Student Analytics ───────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.analytics.index', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Analitik Siswa', route('dashboard.analytics.index'));
});

Breadcrumbs::for('dashboard.analytics.progress', function (Trail $trail, $student) {
    $trail->parent('dashboard.analytics.index');
    $trail->push('Detail Progress - '.$student);
});

Breadcrumbs::for('dashboard.analytics.class-comparison', function (Trail $trail, $classroom) {
    $trail->parent('dashboard.analytics.index');
    $trail->push('Perbandingan Kelas - '.$classroom->name);
});

Breadcrumbs::for('dashboard.analytics.export', function (Trail $trail) {
    $trail->parent('dashboard.analytics.index');
    $trail->push('Export');
});

// ── Early Warning ──────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.early-warning.index', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Early Warning', route('dashboard.early-warning.index'));
});
Breadcrumbs::for('dashboard.early-warning.show', function (Trail $trail, $earlyWarning) {
    $trail->parent('dashboard.early-warning.index');
    $studentName = ($earlyWarning->student ? $earlyWarning->student->name : 'Early Warning');
    $trail->push($studentName);
});

// ── Rapor Distribution ──────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.rapor-distribution.index', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Pendistribusian Rapor', route('dashboard.rapor-distribution.index'));
});

Breadcrumbs::for('dashboard.rapor-distribution.create', function (Trail $trail) {
    $trail->parent('dashboard.rapor-distribution.index');
    $trail->push('Distribusi Tunggal');
});

Breadcrumbs::for('dashboard.rapor-distribution.bulk-create', function (Trail $trail) {
    $trail->parent('dashboard.rapor-distribution.index');
    $trail->push('Distribusi Massal');
});

// ── Templates (Universal Document Templates) ──────────────────────────────────────
Breadcrumbs::for('dashboard.templates', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Template Dokumen', route('dashboard.templates.index'));
});

Breadcrumbs::for('dashboard.templates.index', function (Trail $trail) {
    $trail->parent('dashboard.templates');
});

Breadcrumbs::for('dashboard.templates.create', function (Trail $trail) {
    $trail->parent('dashboard.templates');
    $trail->push('Buat Template');
});

Breadcrumbs::for('dashboard.templates.show', function (Trail $trail, $template) {
    $trail->parent('dashboard.templates');
    $trail->push($template->name);
});

Breadcrumbs::for('dashboard.templates.edit', function (Trail $trail, $template) {
    $trail->parent('dashboard.templates');
    $trail->push('Edit - '.$template->name);
});

// ── Template Instances (Data & Document Generation) ───────────────────
Breadcrumbs::for('dashboard.template-instances', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Template Data', route('dashboard.template-instances.index'));
});

Breadcrumbs::for('dashboard.template-instances.index', function (Trail $trail) {
    $trail->parent('dashboard.template-instances');
});

Breadcrumbs::for('dashboard.template-instances.create', function (Trail $trail) {
    $trail->parent('dashboard.template-instances');
    $trail->push('Create Data');
});

Breadcrumbs::for('dashboard.template-instances.show', function (Trail $trail, $instance) {
    $trail->parent('dashboard.template-instances');
    $trail->push($instance->template->name.' - '.($instance->student?->name ?? 'N/A'));
});

Breadcrumbs::for('dashboard.template-instances.edit', function (Trail $trail, $instance) {
    $trail->parent('dashboard.template-instances');
    $trail->push('Edit - '.$instance->template->name);
});

// ── Student Extracurriculars ──────────────────────────────────────────────
Breadcrumbs::for('dashboard.student-extracurriculars', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Nilai Eskul', route('dashboard.student-extracurriculars.index'));
});

Breadcrumbs::for('dashboard.student-extracurriculars.index', function (Trail $trail) {
    $trail->parent('dashboard.student-extracurriculars');
});

Breadcrumbs::for('dashboard.student-extracurriculars.create', function (Trail $trail) {
    $trail->parent('dashboard.student-extracurriculars');
    $trail->push('Tambah');
});

Breadcrumbs::for('dashboard.student-extracurriculars.edit', function (Trail $trail, $record) {
    $trail->parent('dashboard.student-extracurriculars');
    $trail->push('Edit');
});

Breadcrumbs::for('dashboard.articles', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Articles', route('dashboard.articles.index'));
});

Breadcrumbs::for('dashboard.articles.index', function (Trail $trail) {
    $trail->parent('dashboard.articles');
});

Breadcrumbs::for('dashboard.articles.create', function (Trail $trail) {
    $trail->parent('dashboard.articles');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.articles.edit', function (Trail $trail, $articleRecord) {
    $trail->parent('dashboard.articles');
    $trail->push('Edit');
});

// ── Categories ────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.categories', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Categories', route('dashboard.categories.index'));
});

Breadcrumbs::for('dashboard.categories.index', function (Trail $trail) {
    $trail->parent('dashboard.categories');
});

Breadcrumbs::for('dashboard.categories.create', function (Trail $trail) {
    $trail->parent('dashboard.categories');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.categories.edit', function (Trail $trail, $category) {
    $trail->parent('dashboard.categories');
    $trail->push('Edit');
});

Breadcrumbs::for('dashboard.heroes.index', function ($trail) {
    $trail->parent('dashboard.index');
    $trail->push('Heroes', route('dashboard.heroes.index'));
});
Breadcrumbs::for('dashboard.heroes.create', function ($trail) {
    $trail->parent('dashboard.heroes.index');
    $trail->push('Create', route('dashboard.heroes.create'));
});
Breadcrumbs::for('dashboard.heroes.edit', function ($trail, $model) {
    $trail->parent('dashboard.heroes.index');
    $trail->push('Edit', route('dashboard.heroes.edit', $model->id));
});

Breadcrumbs::for('dashboard.achievements.index', function ($trail) {
    $trail->parent('dashboard.index');
    $trail->push('Achievements', route('dashboard.achievements.index'));
});
Breadcrumbs::for('dashboard.achievements.create', function ($trail) {
    $trail->parent('dashboard.achievements.index');
    $trail->push('Create', route('dashboard.achievements.create'));
});
Breadcrumbs::for('dashboard.achievements.edit', function ($trail, $model) {
    $trail->parent('dashboard.achievements.index');
    $trail->push('Edit', route('dashboard.achievements.edit', $model->id));
});

Breadcrumbs::for('dashboard.galleries.index', function ($trail) {
    $trail->parent('dashboard.index');
    $trail->push('Galleries', route('dashboard.galleries.index'));
});
Breadcrumbs::for('dashboard.galleries.create', function ($trail) {
    $trail->parent('dashboard.galleries.index');
    $trail->push('Create', route('dashboard.galleries.create'));
});
Breadcrumbs::for('dashboard.galleries.edit', function ($trail, $model) {
    $trail->parent('dashboard.galleries.index');
    $trail->push('Edit', route('dashboard.galleries.edit', $model->id));
});

Breadcrumbs::for('dashboard.cooperations.index', function ($trail) {
    $trail->parent('dashboard.index');
    $trail->push('Cooperations', route('dashboard.cooperations.index'));
});
Breadcrumbs::for('dashboard.cooperations.create', function ($trail) {
    $trail->parent('dashboard.cooperations.index');
    $trail->push('Create', route('dashboard.cooperations.create'));
});
Breadcrumbs::for('dashboard.cooperations.edit', function ($trail, $model) {
    $trail->parent('dashboard.cooperations.index');
    $trail->push('Edit', route('dashboard.cooperations.edit', $model->id));
});

Breadcrumbs::for('dashboard.admissions.index', function ($trail) {
    $trail->parent('dashboard.index');
    $trail->push('PPDB Admissions', route('dashboard.admissions.index'));
});
Breadcrumbs::for('dashboard.admissions.create', function ($trail) {
    $trail->parent('dashboard.admissions.index');
    $trail->push('Create', route('dashboard.admissions.create'));
});
Breadcrumbs::for('dashboard.admissions.show', function ($trail, $model) {
    $trail->parent('dashboard.admissions.index');
    $trail->push($model->name);
});
Breadcrumbs::for('dashboard.admissions.edit', function ($trail, $model) {
    $trail->parent('dashboard.admissions.index');
    $trail->push('Edit', route('dashboard.admissions.edit', $model->id));
});

Breadcrumbs::for('dashboard.admissions.workflow', function ($trail) {
    $trail->parent('dashboard.admissions.index');
    $trail->push('Workflow');
});

// ── Articles ──────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.post.articles', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Articles', route('dashboard.post.articles.index'));
});

Breadcrumbs::for('dashboard.post.articles.index', function (Trail $trail) {
    $trail->parent('dashboard.post.articles');
});

Breadcrumbs::for('dashboard.post.articles.create', function (Trail $trail) {
    $trail->parent('dashboard.post.articles.index');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.post.articles.show', function (Trail $trail, $article) {
    $trail->parent('dashboard.post.articles.index');
    $trail->push($article->title);
});

Breadcrumbs::for('dashboard.post.articles.edit', function (Trail $trail, $article) {
    $trail->parent('dashboard.post.articles.index');
    $trail->push('Edit');
});

// ── Categories ────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.post.categories', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Categories', route('dashboard.post.categories.index'));
});

Breadcrumbs::for('dashboard.post.categories.index', function (Trail $trail) {
    $trail->parent('dashboard.post.categories');
});

Breadcrumbs::for('dashboard.post.categories.create', function (Trail $trail) {
    $trail->parent('dashboard.post.categories.index');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.post.categories.edit', function (Trail $trail, $category) {
    $trail->parent('dashboard.post.categories.index');
    $trail->push('Edit');
});

// ── Achievements ────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.post.achievements', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Achievements', route('dashboard.post.achievements.index'));
});

Breadcrumbs::for('dashboard.post.achievements.index', function (Trail $trail) {
    $trail->parent('dashboard.post.achievements');
});

Breadcrumbs::for('dashboard.post.achievements.create', function (Trail $trail) {
    $trail->parent('dashboard.post.achievements.index');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.post.achievements.edit', function (Trail $trail, $model) {
    $trail->parent('dashboard.post.achievements.index');
    $trail->push('Edit');
});

// ── Galleries ────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.post.galleries', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Galleries', route('dashboard.post.galleries.index'));
});

Breadcrumbs::for('dashboard.post.galleries.index', function (Trail $trail) {
    $trail->parent('dashboard.post.galleries');
});

Breadcrumbs::for('dashboard.post.galleries.create', function (Trail $trail) {
    $trail->parent('dashboard.post.galleries.index');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.post.galleries.edit', function (Trail $trail, $model) {
    $trail->parent('dashboard.post.galleries.index');
    $trail->push('Edit');
});

// ── Heroes ────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.post.heroes', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Heroes', route('dashboard.post.heroes.index'));
});

Breadcrumbs::for('dashboard.post.heroes.index', function (Trail $trail) {
    $trail->parent('dashboard.post.heroes');
});

Breadcrumbs::for('dashboard.post.heroes.create', function (Trail $trail) {
    $trail->parent('dashboard.post.heroes.index');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.post.heroes.edit', function (Trail $trail, $model) {
    $trail->parent('dashboard.post.heroes.index');
    $trail->push('Edit');
});

// ── Cooperations ─────────────────────────────────────────────────
Breadcrumbs::for('dashboard.post.cooperations', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Cooperations', route('dashboard.post.cooperations.index'));
});

Breadcrumbs::for('dashboard.post.cooperations.index', function (Trail $trail) {
    $trail->parent('dashboard.post.cooperations');
});

Breadcrumbs::for('dashboard.post.cooperations.create', function (Trail $trail) {
    $trail->parent('dashboard.post.cooperations.index');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.post.cooperations.edit', function (Trail $trail, $model) {
    $trail->parent('dashboard.post.cooperations.index');
    $trail->push('Edit');
});

// ── Document Templates ───────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.document-templates', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Template Dokumen', route('dashboard.document-templates.index'));
});

Breadcrumbs::for('dashboard.document-templates.index', function (Trail $trail) {
    $trail->parent('dashboard.document-templates');
});

Breadcrumbs::for('dashboard.document-templates.create', function (Trail $trail) {
    $trail->parent('dashboard.document-templates');
    $trail->push('Create');
});

Breadcrumbs::for('dashboard.document-templates.edit', function (Trail $trail, $template) {
    $trail->parent('dashboard.document-templates');
    $trail->push('Edit - '.$template->name);
});

// ── Tasks ────────────────────────────────────────────────────────────────────

Breadcrumbs::for('dashboard.tasks.index', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Tasks', route('dashboard.tasks.index'));
});

Breadcrumbs::for('dashboard.tasks.show', function (Trail $trail, $task) {
    $trail->parent('dashboard.tasks.index');
    $trail->push($task->title, route('dashboard.tasks.show', $task));
});

Breadcrumbs::for('dashboard.tasks.create', function (Trail $trail) {
    $trail->parent('dashboard.tasks.index');
    $trail->push('Create New Task', route('dashboard.tasks.create'));
});

Breadcrumbs::for('dashboard.tasks.edit', function (Trail $trail, $task) {
    $trail->parent('dashboard.tasks.show', $task);
    $trail->push('Edit Task', route('dashboard.tasks.edit', $task));
});

Breadcrumbs::for('dashboard.tasks.my-tasks', function (Trail $trail) {
    $trail->parent('dashboard.tasks.index');
    $trail->push('Tugas Saya', route('dashboard.tasks.my-tasks'));
});

Breadcrumbs::for('dashboard.tasks.overdue', function (Trail $trail) {
    $trail->parent('dashboard.tasks.index');
    $trail->push('Tugas Terlambat', route('dashboard.tasks.overdue'));
});

// ── Timesheets ───────────────────────────────────────────────────────────────

Breadcrumbs::for('dashboard.timesheets.index', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Timesheets', route('dashboard.timesheets.index'));
});

// ── Payroll ──────────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.payroll.show', function ($trail, $payroll) {
    $trail->parent('dashboard.index');
    $trail->push('Payroll');
    $empName = (isset($payroll->employee) && $payroll->employee ? $payroll->employee->name : ($payroll->employee_name ?? 'Payroll'));
    $trail->push($empName);
});

// ── WhatsApp Chat ────────────────────────────────────────────────────────────
Breadcrumbs::for('dashboard.whatsapp-chat.show', function ($trail, $conversation) {
    $trail->parent('dashboard.index');
    $trail->push('WhatsApp Conversations');
    $name = ($conversation->contact_name ?: ($conversation->phone_number ?? 'Chat'));
    $trail->push($name);
});

// ── Payroll ─────────────────────────────────────────────────
Breadcrumbs::for('dashboard.payroll', function ($trail) {
    $trail->parent('dashboard.index');
    $trail->push('Payroll');
});

// ── Payroll Runs ─────────────────────────────────────────────────
Breadcrumbs::for('dashboard.payroll.index', function ($trail) {
    $trail->parent('dashboard.payroll');
    $trail->push('Daftar Penggajian', route('dashboard.payroll.index'));
});

Breadcrumbs::for('dashboard.payroll.create', function ($trail) {
    $trail->parent('dashboard.payroll.index');
    $trail->push('Buat Penggajian');
});

Breadcrumbs::for('dashboard.payroll.process', function ($trail, $payrollRun) {
    $trail->parent('dashboard.payroll.show', $payrollRun);
    $trail->push('Proses Penggajian');
});

// ── Salary Grades ────────────────────────────────────────
Breadcrumbs::for('dashboard.payroll.salary-grades', function ($trail) {
    $trail->parent('dashboard.payroll');
    $trail->push('Grade Gaji', route('dashboard.payroll.salary-grades.index'));
});

Breadcrumbs::for('dashboard.payroll.salary-grades.index', function ($trail) {
    $trail->parent('dashboard.payroll.salary-grades');
});

Breadcrumbs::for('dashboard.payroll.salary-grades.create', function ($trail) {
    $trail->parent('dashboard.payroll.salary-grades');
    $trail->push('Tambah');
});

Breadcrumbs::for('dashboard.payroll.salary-grades.edit', function ($trail, $salaryGrade) {
    $trail->parent('dashboard.payroll.salary-grades');
    $trail->push('Edit');
});

// ── Education Allowances ─────────────────────────────────
Breadcrumbs::for('dashboard.payroll.education-allowances', function ($trail) {
    $trail->parent('dashboard.payroll');
    $trail->push('Tunjangan Pendidikan', route('dashboard.payroll.education-allowances.index'));
});

Breadcrumbs::for('dashboard.payroll.education-allowances.index', function ($trail) {
    $trail->parent('dashboard.payroll.education-allowances');
});

Breadcrumbs::for('dashboard.payroll.education-allowances.create', function ($trail) {
    $trail->parent('dashboard.payroll.education-allowances');
    $trail->push('Tambah');
});

Breadcrumbs::for('dashboard.payroll.education-allowances.edit', function ($trail, $educationAllowance) {
    $trail->parent('dashboard.payroll.education-allowances');
    $trail->push('Edit');
});

// ── Structural Allowances ────────────────────────────────
Breadcrumbs::for('dashboard.payroll.structural-allowances', function ($trail) {
    $trail->parent('dashboard.payroll');
    $trail->push('Tunjangan Struktural', route('dashboard.payroll.structural-allowances.index'));
});

Breadcrumbs::for('dashboard.payroll.structural-allowances.index', function ($trail) {
    $trail->parent('dashboard.payroll.structural-allowances');
});

Breadcrumbs::for('dashboard.payroll.structural-allowances.create', function ($trail) {
    $trail->parent('dashboard.payroll.structural-allowances');
    $trail->push('Tambah');
});

Breadcrumbs::for('dashboard.payroll.structural-allowances.edit', function ($trail, $structuralAllowance) {
    $trail->parent('dashboard.payroll.structural-allowances');
    $trail->push('Edit');
});

// ── Functional Allowances ───────────────────────────────
Breadcrumbs::for('dashboard.payroll.functional-allowances', function ($trail) {
    $trail->parent('dashboard.payroll');
    $trail->push('Tunjangan Fungsional', route('dashboard.payroll.functional-allowances.index'));
});

Breadcrumbs::for('dashboard.payroll.functional-allowances.index', function ($trail) {
    $trail->parent('dashboard.payroll.functional-allowances');
});

Breadcrumbs::for('dashboard.payroll.functional-allowances.create', function ($trail) {
    $trail->parent('dashboard.payroll.functional-allowances');
    $trail->push('Tambah');
});

Breadcrumbs::for('dashboard.payroll.functional-allowances.edit', function ($trail, $functionalAllowance) {
    $trail->parent('dashboard.payroll.functional-allowances');
    $trail->push('Edit');
});

// ── Salary Rates ────────────────────────────────────────
Breadcrumbs::for('dashboard.payroll.salary-rates', function ($trail) {
    $trail->parent('dashboard.payroll');
    $trail->push('Tarif Gaji', route('dashboard.payroll.salary-rates.index'));
});

Breadcrumbs::for('dashboard.payroll.salary-rates.index', function ($trail) {
    $trail->parent('dashboard.payroll.salary-rates');
});

Breadcrumbs::for('dashboard.payroll.salary-rates.create', function ($trail) {
    $trail->parent('dashboard.payroll.salary-rates');
    $trail->push('Tambah');
});

Breadcrumbs::for('dashboard.payroll.salary-rates.edit', function ($trail, $salaryRate) {
    $trail->parent('dashboard.payroll.salary-rates');
    $trail->push('Edit');
});

// ── Bulk Operations ─────────────────────────────────────
Breadcrumbs::for('dashboard.bulk-operations.index', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Bulk Operations');
});

// ── Employee Salary Config ────────────────────────────────────────
Breadcrumbs::for('dashboard.employees.salary-config', function (Trail $trail, $employee) {
    $trail->parent('dashboard.employees.show', $employee);
    $trail->push('Konfigurasi Gaji');
});

// ── Functional Allowances ─────────────────────────────────────────
Breadcrumbs::for('dashboard.functional-allowances.index', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Tunjangan Fungsional', route('dashboard.functional-allowances.index'));
});

// ── Notifications ─────────────────────────────────────────────────
Breadcrumbs::for('dashboard.notifications', function (Trail $trail) {
    $trail->parent('dashboard.index');
    $trail->push('Notifikasi', route('dashboard.notifications.index'));
});
