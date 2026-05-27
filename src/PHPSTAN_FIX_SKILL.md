# SKILL: Laravel PHPStan Error Fixer

## Overview

Skill ini digunakan untuk memperbaiki **semua PHPStan/Larastan errors** pada Laravel project secara sistematis menggunakan sub-agents paralel. Tujuan akhir: **zero PHPStan errors**, CI/CD pipeline hijau, dan kode yang type-safe.

---

## Cara Kerja

Bagi pekerjaan ke **8 sub-agents paralel** berdasarkan kategori error. Setiap sub-agent bertanggung jawab pada satu kategori. Setelah semua selesai, jalankan PHPStan sekali lagi untuk verifikasi.

---

## Langkah 0: Persiapan (Lakukan Dulu Sebelum Sub-agents)

```bash
cd src  # atau root project Laravel

# Backup phpstan.neon jika ada
cp phpstan.neon phpstan.neon.bak 2>/dev/null || true

# Pastikan dependencies terinstall
composer install --no-progress --prefer-dist --no-interaction

# Cek versi PHPStan
vendor/bin/phpstan --version
```

### Setup `phpstan.neon` yang Benar

Buat atau perbarui `phpstan.neon` di root project Laravel:

```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app
    level: 5
    checkMissingIterableValueType: false
    checkGenericClassInNonGenericObjectType: false
    ignoreErrors:
        # Izinkan akses property via magic/dynamic Eloquent
        - '#Access to an undefined property Illuminate\\Database\\Eloquent\\Model::#'
        # Izinkan unresolvable closure types pada Collection::map()
        - '#Parameter \#1 \$callback of method Illuminate\\Database\\Eloquent\\Collection.*contains unresolvable type#'
    excludePaths:
        - vendor
        - bootstrap/cache
        - storage
```

> **Catatan**: Level 5 adalah default yang aman. Jika masih terlalu banyak error, turunkan ke level 3 sambil memperbaiki satu per satu.

---

## Sub-Agent 1: Model Relations (Prioritas Tertinggi)

**Error patterns yang ditangani:**
- `Relation 'xxx' is not found in App\Models\Yyy model.`
- `Access to an undefined property App\Models\Xxx::$property`

### Cara Fix

Untuk setiap model yang error, **tambahkan PHPDoc `@property` dan method relasi yang hilang**.

#### Template Fix untuk Setiap Model

**File: `app/Models/Classroom.php`**

Tambahkan di class body:

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $classroom_type
 * @property int|null $academic_year_id
 * @property-read \App\Models\AcademicYear|null $academicYear
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Teacher> $teachers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subject> $subjects
 * @property-read \App\Models\Teacher|null $homeroomTeacher
 */
class Classroom extends Model
{
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_classrooms')
            ->withPivot(['id', 'academic_year_id', 'status'])
            ->withTimestamps();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'classroom_teacher')
            ->withTimestamps();
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'classroom_subject')
            ->withTimestamps();
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }
}
```

**File: `app/Models/StudentAttendance.php`**

```php
/**
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Classroom $classroom
 */
class StudentAttendance extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
```

**File: `app/Models/Payment.php`**

```php
/**
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Classroom|null $classroom
 * @property-read \App\Models\PaymentTitle $paymentTitle
 */
class Payment extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function paymentTitle(): BelongsTo
    {
        return $this->belongsTo(PaymentTitle::class);
    }
}
```

**File: `app/Models/Grade.php`**

```php
/**
 * @property int $id
 * @property int|null $teacher_id
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Subject $subject
 * @property-read \App\Models\Classroom $classroom
 * @property-read \App\Models\AcademicYear $academicYear
 * @property-read \App\Models\Teacher|null $teacher
 */
class Grade extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
```

**File: `app/Models/Employee.php`**

```php
/**
 * @property int $id
 * @property string|null $device_id
 * @property string|null $email
 * @property string|null $phone
 * @property int|null $staff_position_id
 * @property int|null $work_shift_id
 * @property-read \App\Models\StaffPosition|null $staffPosition
 * @property-read \App\Models\WorkShift|null $workShift
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeAttendance> $attendances
 * @property-read \App\Models\User|null $user
 */
class Employee extends Model
{
    public function staffPosition(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class);
    }

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

**File: `app/Models/EmployeeAttendance.php`**

```php
/**
 * @property-read \App\Models\Employee $employee
 * @property-read \App\Models\Device|null $device
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\WorkShift|null $shift
 */
class EmployeeAttendance extends Model
{
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class, 'shift_id');
    }
}
```

**File: `app/Models/Teacher.php`**

```php
/**
 * @property string|null $nip
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subject> $subjects
 */
class Teacher extends Model
{
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_teacher')
            ->withTimestamps();
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher')
            ->withTimestamps();
    }
}
```

**File: `app/Models/Subject.php`**

```php
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Teacher> $teachers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
 */
class Subject extends Model
{
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher')
            ->withTimestamps();
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_subject')
            ->withTimestamps();
    }
}
```

**File: `app/Models/Student.php`**

```php
/**
 * @property string|null $gender
 * @property string|null $photo
 * @property string|null $birth_place
 * @property string|null $religion
 * @property int|null $user_id
 * @property string|null $parent_email
 * @property string|null $guardian_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Classroom> $classrooms
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Grade> $grades
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StudentAttendance> $attendances
 */
class Student extends Model
{
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'student_classrooms')
            ->withPivot(['id', 'academic_year_id', 'status'])
            ->withTimestamps();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    /**
     * Get current classroom (active enrollment).
     */
    public function currentClassroom(): ?Classroom
    {
        return $this->classrooms()
            ->wherePivot('status', 'active')
            ->latest('student_classrooms.created_at')
            ->first();
    }

    /**
     * Get current enrollment pivot.
     */
    public function currentEnrollment(): ?StudentClassroom
    {
        return StudentClassroom::where('student_id', $this->id)
            ->where('status', 'active')
            ->latest()
            ->first();
    }
}
```

**File: `app/Models/Task.php`**

```php
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $assignedTo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Task> $subtasks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TaskComment> $comments
 * @property-read \App\Models\Task|null $parent
 */
class Task extends Model
{
    public function assignedTo(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withTimestamps();
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }
}
```

**File: `app/Models/AuditLog.php`**

```php
/**
 * @property-read \App\Models\User|null $user
 */
class AuditLog extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

**File: `app/Models/AcademicCalendar.php`**

```php
/**
 * @property-read \App\Models\AcademicYear $academicYear
 */
class AcademicCalendar extends Model
{
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
```

**File: `app/Models/AcademicEvent.php`**

```php
/**
 * @property-read \App\Models\AcademicCalendar $academicCalendar
 */
class AcademicEvent extends Model
{
    public function academicCalendar(): BelongsTo
    {
        return $this->belongsTo(AcademicCalendar::class);
    }
}
```

**File: `app/Models/LeaveRequest.php`**

```php
/**
 * @property-read \App\Models\Employee $employee
 * @property-read \App\Models\User|null $approvedBy
 */
class LeaveRequest extends Model
{
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
```

**File: `app/Models/StudentP5Assessment.php`**

```php
/**
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Classroom $classroom
 * @property-read \App\Models\User $assessor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\P5Score> $scores
 */
class StudentP5Assessment extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(P5Score::class);
    }
}
```

**File: `app/Models/P5Dimension.php`**

```php
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\P5Indicator> $indicators
 */
class P5Dimension extends Model
{
    public function indicators(): HasMany
    {
        return $this->hasMany(P5Indicator::class);
    }
}
```

**File: `app/Models/Notification.php`**

```php
/**
 * @property-read \App\Models\User|null $user
 */
class Notification extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

**File: `app/Models/Schedule.php`**

```php
/**
 * @property-read \App\Models\Classroom $classroom
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ScheduleDetail> $details
 */
class Schedule extends Model
{
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(ScheduleDetail::class);
    }
}
```

**File: `app/Models/ScheduleDetail.php`**

```php
/**
 * @property-read \App\Models\Subject $subject
 * @property-read \App\Models\Teacher $teacher
 * @property-read \App\Models\Schedule $schedule
 */
class ScheduleDetail extends Model
{
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
```

**File: `app/Models/StudentReportCard.php`**

```php
/**
 * @property int $academic_year_id
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\ReportCard $reportCard
 * @property-read \App\Models\ReportCardTemplate|null $reportCardTemplate
 */
class StudentReportCard extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reportCard(): BelongsTo
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function reportCardTemplate(): BelongsTo
    {
        return $this->belongsTo(ReportCardTemplate::class);
    }
}
```

**File: `app/Models/ReportCard.php`**

```php
/**
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Classroom $classroom
 */
class ReportCard extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
```

**File: `app/Models/GradeComponent.php`**

```php
/**
 * @property-read \App\Models\Student|null $student
 */
class GradeComponent extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
```

**File: `app/Models/StaffPosition.php`**

```php
/**
 * @property-read \App\Models\StaffPosition|null $parent
 */
class StaffPosition extends Model
{
    public function parent(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'parent_id');
    }
}
```

**File: `app/Models/StudentAnalyticsSnapshot.php`**

```php
/**
 * @property float|null $grade_average
 * @property string|null $risk_level
 * @property-read \App\Models\Student $student
 */
class StudentAnalyticsSnapshot extends Model
{
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
```

**File: `app/Models/WhatsAppConversation.php`**

```php
/**
 * @property-read \App\Models\WhatsAppMessage|null $latestMessage
 */
class WhatsAppConversation extends Model
{
    public function latestMessage(): HasOne
    {
        return $this->hasOne(WhatsAppMessage::class)->latestOfMany();
    }
}
```

**File: `app/Models/User.php`**

```php
/**
 * @property-read \App\Models\Employee|null $employee
 */
class User extends Authenticatable
{
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
```

---

## Sub-Agent 2: Type Errors & Undefined Properties

**Error patterns:**
- `Access to an undefined property App\Models\Xxx::$property`
- `Access to an undefined property Illuminate\Database\Eloquent\Model::$xxx`

### Cara Fix: Tambahkan PHPDoc `@property` pada model

Untuk setiap model, identifikasi kolom database dan tambahkan PHPDoc. Contoh pola:

```php
/**
 * @property int $id
 * @property string $name
 * @property string|null $nullable_field
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
```

#### Fix spesifik untuk error yang ada:

**`App\Models\StudentAttendance::$total` dan `::$hadir`**

Tambahkan ke model:
```php
/**
 * @property int $total
 * @property int $hadir
 */
```
Atau jika ini computed/aggregate, tambahkan sebagai property di query:
```php
// Di query builder, gunakan selectRaw dan cast
$query->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as hadir');
```

**`App\Models\Employee::$salary_components`, `::$automatic_allowances`, dll.**

Ini adalah accessor/computed properties. Tambahkan di Employee model:
```php
/**
 * @property-read mixed $salary_components
 * @property-read float $automatic_allowances
 * @property-read float $automatic_allowance_total
 * @property-read float $calculated_base_salary
 * @property-read float $config_allowance_total
 */

public function getSalaryComponentsAttribute(): mixed
{
    // implementasi atau kembalikan null
    return null;
}
```

**`Illuminate\Database\Eloquent\Model::$month`, `::$nip`, `::$field_key`, dll.**

Ini terjadi karena query builder mengembalikan `Model` bukan model konkret. Fix dengan menggunakan `/** @var SpecificModel $item */` type assertion atau menggunakan model yang tepat di relasi/query:

```php
// SEBELUM (error):
$items->each(function ($item) {
    echo $item->month; // Model tidak punya $month
});

// SESUDAH (fix):
$items->each(function (EmployeePayroll $item) { // type hint eksplisit
    echo $item->month;
});
```

---

## Sub-Agent 3: Nullsafe & Nullable Fixes

**Error patterns:**
- `Using nullsafe property access "?->name" on left side of ?? is unnecessary. Use -> instead.`
- `Using nullsafe method call on non-nullable type Carbon\Carbon. Use -> instead.`
- `Expression on left side of ?? is not nullable.`

### Cara Fix

Ganti `?->` dengan `->` ketika PHPStan tahu nilai tidak nullable, dan hapus `??` ketika expression tidak nullable.

```php
// SEBELUM (error):
$model?->name ?? 'default'
$carbon?->format('Y-m-d')

// SESUDAH (fix):
$model->name ?? 'default'   // jika $model tidak nullable
$carbon->format('Y-m-d')    // Carbon tidak nullable di sini

// Atau jika model BISA null:
$model?->name               // tanpa ?? jika tidak perlu default
($model?->name) ?? 'default' // atau ini
```

**Script otomatis untuk menemukan semua lokasi:**

```bash
# Cari semua file dengan pattern nullsafe yang tidak perlu
grep -rn "?->" app/ --include="*.php" | grep -v "vendor" | head -50
```

**Fix untuk Carbon:::**

```php
// SEBELUM:
$this->created_at?->format('Y-m-d')

// SESUDAH (Carbon selalu non-null jika kolom tidak nullable):
$this->created_at->format('Y-m-d')

// Atau jika BISA null:
$this->created_at?->format('Y-m-d') // tanpa ??
```

---

## Sub-Agent 4: Controller & Service Method Errors

**Error patterns:**
- `Call to an undefined method App\Http\Controllers\Api\CacheApiController::authorize().`
- `Call to an undefined method App\Http\Controllers\Api\CacheApiController::errorResponse().`
- `Unknown parameter $statusCode in call to method successResponse().`
- `Method XxxService::checkIn() invoked with 1 parameter, 2 required.`

### Fix CacheApiController

Pastikan `CacheApiController` extends controller yang punya method `authorize()`, `successResponse()`, `errorResponse()`:

```php
// app/Http/Controllers/Api/CacheApiController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // atau ApiController base

class CacheApiController extends Controller // HARUS extends Controller yang punya method ini
{
    // Atau tambahkan trait:
    use \App\Traits\ApiResponse;
}
```

**Buat/periksa `ApiResponse` trait:**

```php
// app/Traits/ApiResponse.php
<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(mixed $data, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    protected function errorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
```

**Fix method signature EmployeeAttendanceService:**

```php
// SEBELUM (error - dipanggil dengan 1 param, butuh 2):
$this->attendanceService->checkIn($validatedData);

// SESUDAH - lihat signature method, tambahkan parameter yang kurang:
$this->attendanceService->checkIn($validatedData, $request->user());
// atau perbarui signature method:
public function checkIn(array $data, ?User $user = null): EmployeeAttendance
```

**Fix TaskApiController::created():**

```php
// SEBELUM (dipanggil dengan 3 param, butuh 1-2):
$this->created($task, 'Task created', 201);

// SESUDAH:
return $this->successResponse($task, 'Task created', 201);
// atau sesuaikan signature method:
protected function created(mixed $data, string $message = 'Created', int $status = 201): JsonResponse
```

---

## Sub-Agent 5: Missing Classes & Request Files

**Error patterns:**
- `Parameter $request of method Xxx::checkIn() has invalid type App\Http\Requests\Api\EmployeeCheckInRequest.`
- `Class App\Providers\RaporDistributionStarted not found.`
- `Class App\Providers\AdmissionRejected not found.`

### Fix: Buat Missing Form Request Files

```bash
# Buat Request yang hilang
php artisan make:request Api/EmployeeCheckInRequest
php artisan make:request Api/EmployeeCheckOutRequest
```

**`app/Http/Requests/Api/EmployeeCheckInRequest.php`:**

```php
<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'device_id' => ['nullable', 'string'],
            'photo' => ['nullable', 'string'],
        ];
    }
}
```

**`app/Http/Requests/Api/EmployeeCheckOutRequest.php`:**

```php
<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeCheckOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'photo' => ['nullable', 'string'],
        ];
    }
}
```

### Fix: EventServiceProvider - Missing Event/Listener Classes

```php
// app/Providers/EventServiceProvider.php
// SEBELUM (error - class tidak ada):
'App\Providers\RaporDistributionStarted' => [...],
'App\Providers\AdmissionRejected' => [...],

// SESUDAH - pindahkan ke namespace Events:
\App\Events\RaporDistributionStarted::class => [...],
\App\Events\AdmissionRejected::class => [...],
```

Buat event yang hilang:
```bash
php artisan make:event RaporDistributionStarted
php artisan make:event AdmissionRejected
```

**Fix EventServiceProvider `$listen` type:**

```php
// app/Providers/EventServiceProvider.php
// Gunakan class-string syntax yang benar:
protected $listen = [
    \App\Events\RaporDistributionStarted::class => [
        \App\Listeners\HandleRaporDistributionStarted::class,
    ],
];
```

---

## Sub-Agent 6: PHPDoc & Type Declaration Fixes

**Error patterns:**
- `PHPDoc tag @var for property Handler::$levels contains unresolvable type`
- `PHPDoc type array<...> is not covariant with...`
- `PHPDoc tag @return has invalid value`
- `Unsafe usage of new static()`

### Fix Handler::$levels

```php
// app/Exceptions/Handler.php
// SEBELUM:
/**
 * @var array<class-string<Throwable>, mixed>
 */
protected $levels = [...];

// SESUDAH (sesuaikan dengan parent):
/**
 * @var array<class-string<\Throwable>, 'alert'|'critical'|'debug'|'emergency'|'error'|'info'|'notice'|'warning'>
 */
protected $levels = [];
```

### Fix PHPDoc @return invalid syntax

```php
// SEBELUM (error - sintaks array tidak valid di PHPDoc):
/**
 * @return array['valid' => bool, 'error' => string|null]
 */

// SESUDAH:
/**
 * @return array{valid: bool, error: string|null}
 */
```

### Fix `new static()` unsafe usage

```php
// SEBELUM:
return new static($args);

// SESUDAH:
return new self($args);
// atau jika inheritance dibutuhkan:
/** @phpstan-consistent-constructor */
class MyClass {}
```

### Fix `Illuminate\Contracts\Auth\Authenticatable` property access

```php
// SEBELUM (error - interface tidak punya $id, $email, $name):
auth()->user()->id
auth()->user()->email

// SESUDAH - cast dulu:
/** @var \App\Models\User $user */
$user = auth()->user();
$user->id;
$user->email;

// Atau gunakan helper:
auth()->id(); // untuk ID
```

---

## Sub-Agent 7: Third-Party Library Fixes

**Error patterns:**
- `Call to an undefined method PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::getCellByColumnAndRow()`
- `Parameter #1 $color of method Font::setColor() expects Color, array given`
- `Parameter #2 $level of Sentry::captureMessage() expects Severity|null, string given`

### Fix PhpSpreadsheet: getCellByColumnAndRow() deprecated

```php
// SEBELUM (method ini dihapus di PhpSpreadsheet 2.x):
$sheet->getCellByColumnAndRow($col, $row);

// SESUDAH:
$sheet->getCell([$col, $row]);
// atau:
$colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
$sheet->getCell($colLetter . $row);
```

### Fix PhpSpreadsheet: Font::setColor() wrong type

```php
// SEBELUM (error - passing array bukan Color object):
$font->setColor(['argb' => 'FF0000']);

// SESUDAH:
use PhpOffice\PhpSpreadsheet\Style\Color;
$font->setColor(new Color('FF0000'));
// atau:
$font->setColor(new Color(Color::COLOR_RED));
```

### Fix Sentry API

```php
// SEBELUM (error - API lama):
Sentry::captureMessage($message, 'error'); // string level tidak valid
new \Sentry\Breadcrumb($category, $message, ['data' => $data]); // signature berubah

// SESUDAH (Sentry SDK 4.x):
use Sentry\Severity;
\Sentry\captureMessage($message, Severity::error());

// Breadcrumb:
new \Sentry\Breadcrumb(
    level: \Sentry\Breadcrumb::LEVEL_ERROR,
    type: \Sentry\Breadcrumb::TYPE_DEFAULT,
    category: $category,
    message: $message,
    metadata: ['key' => 'value']
);

// setUser - null tidak valid:
\Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($user): void {
    if ($user) {
        $scope->setUser(['id' => $user->id]);
    } else {
        $scope->setUser([]); // empty array, bukan null
    }
});
```

### Fix FilesystemAdapter::makeDirectory()

```php
// SEBELUM (3 params, hanya 1 yang diterima):
Storage::makeDirectory($path, 0755, true);

// SESUDAH:
Storage::makeDirectory($path);
```

### Fix Http::post() dengan 3 params

```php
// SEBELUM:
Http::post($url, $data, $headers);

// SESUDAH:
Http::withHeaders($headers)->post($url, $data);
```

### Fix DateTimeInterface method calls

```php
// SEBELUM (interface tidak punya Carbon methods):
$date->toDateString();
$date->diffInDays($other);

// SESUDAH - type hint ke Carbon atau cast:
/** @var \Carbon\Carbon $date */
if ($date instanceof \Carbon\Carbon) {
    $date->toDateString();
}
// Atau type hint parameter sebagai Carbon, bukan DateTimeInterface
```

### Fix RaporDistributionBuilder::latest() dengan 2 params

```php
// SEBELUM:
RaporDistribution::latest('created_at', 'desc'); // latest() hanya menerima 1 param

// SESUDAH:
RaporDistribution::orderBy('created_at', 'desc');
// atau:
RaporDistribution::latest('created_at');
```

---

## Sub-Agent 8: Logic & Misc Errors

**Error patterns:**
- `Undefined variable: $xxx`
- `Strict comparison always true/false`
- `Match arm always true`
- `Left side of && is always true`
- `Negated boolean expression is always false`
- `Called 'env' outside of config directory`

### Fix Undefined Variables

```php
// Error: Undefined variable: $payment, $report, $id, $category, $payroll, $permission, $modelClass

// Cari di file mana error terjadi, lalu:
// 1. Tambahkan inisialisasi default:
$payment = null;
$report = null;

// 2. Atau tambahkan type check:
if (!isset($payment)) {
    return redirect()->back()->with('error', 'Payment not found');
}
```

### Fix `env()` outside config

```php
// SEBELUM (error - env() tidak bekerja saat config di-cache):
$value = env('MY_VAR', 'default');

// SESUDAH - pindahkan ke config file:
// config/myconfig.php:
'my_var' => env('MY_VAR', 'default'),

// Penggunaan:
$value = config('myconfig.my_var');
```

### Fix Strict Comparison Always True/False

```php
// Error: Strict comparison using === between 'disetujui'|'ditolak'|'menunggu' and 'approved' will always evaluate to false.

// SEBELUM:
if ($status === 'approved') { ... }

// SESUDAH - sesuaikan dengan nilai yang sebenarnya digunakan:
if ($status === 'disetujui') { ... }
// atau perbaiki mapping value:
const STATUS_APPROVED = 'approved';
// dan pastikan di-set dengan nilai yang benar
```

### Fix `str_pad()` dengan int argument

```php
// SEBELUM:
str_pad($intValue, 5, '0', STR_PAD_LEFT);

// SESUDAH:
str_pad((string) $intValue, 5, '0', STR_PAD_LEFT);
```

### Fix `str_repeat()` dengan float

```php
// SEBELUM:
str_repeat(' ', $floatValue);

// SESUDAH:
str_repeat(' ', (int) $floatValue);
```

### Fix `uniqid()` dengan int argument

```php
// SEBELUM:
uniqid($intValue);

// SESUDAH:
uniqid((string) $intValue);
```

### Fix `Charge::$payment` undefined + `$payment` undefined variable

Di controller/service terkait Charge model - ini kemungkinan relasi yang hilang:

```php
// app/Models/Charge.php
/**
 * @property-read \App\Models\Payment|null $payment
 */
class Charge extends Model
{
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
```

### Fix `Access to an undefined property App\Models\Grade::$teacher_id`

```php
// app/Models/Grade.php - tambahkan PHPDoc:
/**
 * @property int|null $teacher_id
 */
```

### Fix `LeaveRequestController` static call errors

```php
// SEBELUM (error - static call ke instance method):
LeaveRequestController::created();
LeaveRequestController::updated();
LeaveRequestController::deleted();

// Ini biasanya di EventServiceProvider atau Observer.
// Buat observer yang benar:
php artisan make:observer LeaveRequestObserver --model=LeaveRequest
```

### Fix `EarlyWarningService::bulkAssessClassroom()` return type

```php
// SEBELUM:
public function bulkAssessClassroom(): \Illuminate\Database\Eloquent\Collection
{
    return collect([...]); // mengembalikan Support\Collection
}

// SESUDAH - sesuaikan return type:
public function bulkAssessClassroom(): \Illuminate\Support\Collection
{
    return collect([...]);
}
```

### Fix `EmployeeAttendanceService::calculateDistance()` type mismatch

```php
// SEBELUM (memanggil dengan string, method butuh float):
$this->calculateDistance($request->latitude, $request->longitude, ...);

// SESUDAH:
$this->calculateDistance(
    (float) $request->latitude,
    (float) $request->longitude,
    ...
);
```

### Fix `PayrollService` type mismatches

```php
// Fix parameter type mismatch untuk PayrollRun|null:
// SEBELUM:
$this->logAuditAction($payrollModel); // Model, bukan PayrollRun

// SESUDAH:
/** @var \App\Models\PayrollRun $payrollRun */
$payrollRun = PayrollRun::findOrFail($id);
$this->logAuditAction($payrollRun);
```

### Fix `GenerateExportJob::dispatch()` wrong parameter type

```php
// SEBELUM (passing array sebagai userId):
GenerateExportJob::dispatch($data, ['user' => $userId]);

// SESUDAH:
GenerateExportJob::dispatch($data, (string) $userId);
```

### Fix `WhatsappMetaService::sendMessage()` parameter count

```php
// SEBELUM (4 params, method butuh 2-3):
$this->whatsappService->sendMessage($to, $template, $params, $extra);

// SESUDAH - sesuaikan dengan signature:
$this->whatsappService->sendMessage($to, $template, $params);
// atau update signature method untuk terima 4 param
```

### Fix Midtrans response object vs array

```php
// Error: Cannot access property $transaction_status on array<mixed>

// SEBELUM:
$notification->transaction_status;
$notification->fraud_status;

// SESUDAH:
$notification['transaction_status'];
$notification['fraud_status'];
// atau cast dulu:
$notification = (object) $notification;
$notification->transaction_status;
```

### Fix `User::$employee` left side of && always true

```php
// Error: Left side of && is always true
// SEBELUM:
if ($user->employee && $user->employee->active) { ... }

// PHPStan tahu $user->employee (HasOne) tidak null karena relasi declared
// SESUDAH - jika relasi BISA null, deklarasikan sebagai nullable:
// Di User model:
/**
 * @property-read \App\Models\Employee|null $employee
 */
// Dan gunakan:
if ($user->employee !== null && $user->employee->active) { ... }
```

---

## Verifikasi Akhir

Setelah semua sub-agent selesai, jalankan:

```bash
# Jalankan PHPStan
vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=512M

# Jika masih ada error, lihat file spesifik:
vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=512M --error-format=table 2>&1 | head -100

# Jalankan tests untuk pastikan tidak ada regression:
php artisan test --env=testing

# Jalankan Pint untuk code style:
vendor/bin/pint --test
```

---

## Tips & Strategi PHPStan untuk Laravel

### 1. Gunakan `phpstan.neon` yang Konfigurasi Larastan dengan Benar

```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app

    level: 5

    # Atur sesuai kebutuhan project
    checkMissingIterableValueType: false
    checkGenericClassInNonGenericObjectType: false

    ignoreErrors:
        # Abaikan error yang tidak kritis atau sulit diperbaiki
        - '#Unsafe usage of new static\(\)#'
        - '#Constant .* is unused#'
        - '#Method .* is unused#'
        - '#Property .* is never read, only written#'
```

### 2. Tambahkan `@phpstan-type` untuk type alias kompleks

```php
/**
 * @phpstan-type PayrollData array{
 *     employee_id: int,
 *     gross_salary: float,
 *     net_salary: float,
 *     month: string
 * }
 */
class PayrollService
{
    /**
     * @param PayrollData $data
     */
    public function process(array $data): void {}
}
```

### 3. Gunakan generics di Collection

```php
/**
 * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student>
 */
public function getStudents(): Collection
{
    return Student::all();
}
```

### 4. Untuk `Illuminate\Database\Eloquent\Model` generic errors

Jika banyak error tentang `Model::$property`, itu karena query tidak typed. Gunakan:

```php
// SEBELUM:
$model = DB::table('employees')->first();
$model->name; // error: undefined property on stdClass

// SESUDAH:
$employee = Employee::first();
$employee?->name; // typed, PHPStan tahu propertinya
```

### 5. Untuk Pivot Relations

```php
// Error: Access to undefined property Model&object{pivot: Pivot}::$id
// Fix dengan menggunakan withPivot():
$this->belongsToMany(Student::class, 'student_classrooms')
    ->withPivot(['id', 'status']) // declare pivot columns
    ->as('enrollment'); // opsional: rename pivot

// Dan akses via:
$student->pivot->id; // sekarang PHPStan tahu $id ada
```

---

## Urutan Eksekusi yang Disarankan

1. **[PERTAMA]** Perbarui `phpstan.neon` dengan ignore rules yang tepat
2. **[PARALEL]** Sub-agent 1 (Relations) + Sub-agent 5 (Missing Classes)
3. **[PARALEL]** Sub-agent 2 (Types) + Sub-agent 3 (Nullsafe) + Sub-agent 4 (Controllers)
4. **[PARALEL]** Sub-agent 6 (PHPDoc) + Sub-agent 7 (Libraries) + Sub-agent 8 (Logic)
5. **[TERAKHIR]** Verifikasi dengan `vendor/bin/phpstan analyse`

---

## Estimated File Count

| Kategori | Jumlah File |
|----------|------------|
| Models (relations + properties) | ~25 files |
| Controllers + Services | ~10 files |
| Request files (new) | 2 files |
| Event files (new) | 2 files |
| Traits (new/update) | 1 file |
| phpstan.neon | 1 file |
| **Total** | **~41 files** |
