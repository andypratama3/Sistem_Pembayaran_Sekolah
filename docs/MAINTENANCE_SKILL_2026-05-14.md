# ProductSchool - Maintenance & Debugging Skill Guide
**Date**: May 14, 2026  
**Version**: 1.0  
**Purpose**: Complete guide for maintaining, debugging, and extending ProductSchool

---

## TABLE OF CONTENTS

1. [Quick Start](#quick-start)
2. [Architecture Overview](#architecture-overview)
3. [Common Tasks](#common-tasks)
4. [Debugging Guide](#debugging-guide)
5. [Adding New Features](#adding-new-features)
6. [Performance Optimization](#performance-optimization)
7. [Security Hardening](#security-hardening)
8. [Troubleshooting](#troubleshooting)

---

## QUICK START

### Local Development Setup

```bash
# Clone repository
git clone <repo-url>
cd ProductSchool

# Install dependencies
cd src
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Start development server
php artisan serve
npm run dev

# Start Reverb for real-time updates
php artisan reverb:start
```

### Docker Setup

```bash
# Build and start containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --seed

# Access application
http://localhost
```

---

## ARCHITECTURE OVERVIEW

### Directory Structure

```
src/
├── app/
│   ├── Console/Commands/        # Artisan commands
│   ├── Events/                  # Broadcasting events
│   ├── Exceptions/              # Exception handling
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/             # REST API controllers
│   │   │   ├── Auth/            # Authentication controllers
│   │   │   └── Dashboard/       # Dashboard controllers
│   │   ├── Middleware/          # Custom middleware
│   │   └── Requests/            # Form request validation
│   ├── Jobs/                    # Queue jobs
│   ├── Listeners/               # Event listeners
│   ├── Mail/                    # Mailable classes
│   ├── Models/                  # Eloquent models
│   ├── Providers/               # Service providers
│   ├── Services/                # Business logic services
│   └── Traits/                  # Reusable traits
├── bootstrap/app.php            # Application bootstrap
├── config/                      # Configuration files
├── database/
│   ├── migrations/              # Database migrations
│   └── seeders/                 # Database seeders
├── resources/
│   ├── views/                   # Blade templates
│   ├── css/                     # Stylesheets
│   └── js/                      # JavaScript files
├── routes/
│   ├── web.php                  # Web routes
│   ├── api.php                  # API routes
│   ├── auth.php                 # Auth routes
│   ├── channels.php             # Broadcasting channels
│   └── console.php              # Console commands
└── storage/                     # Application storage
```

### Request Flow

```
HTTP Request
    ↓
Route Matching (routes/web.php or routes/api.php)
    ↓
Middleware Stack
    ├─ auth (verify user is logged in)
    ├─ verified (verify email)
    ├─ CheckUserStatus (verify user has role and is active)
    ├─ SetLocale (set application locale)
    └─ role/permission (verify user has required role/permission)
    ↓
Controller (thin - only HTTP concerns)
    ├─ Validate input (FormRequest)
    ├─ Authorize action (policy)
    └─ Call service
    ↓
Service (fat - all business logic)
    ├─ Query models
    ├─ Perform calculations
    ├─ Dispatch events
    └─ Queue jobs
    ↓
Model (relationships, scopes, accessors)
    ├─ Query database
    ├─ Apply relationships
    └─ Return data
    ↓
Response
    ├─ JSON (for API)
    ├─ View (for web)
    └─ Redirect (for forms)
```

---

## COMMON TASKS

### 1. Adding a New Model

```bash
# Generate model with migration and factory
php artisan make:model Student -mf

# Or with controller
php artisan make:model Student -mcf
```

**Model Template**:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'nisn',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

### 2. Adding a New Service

**Service Template**:

```php
<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Log;

class StudentService
{
    public function create(array $data): Student
    {
        try {
            $student = Student::create($data);
            
            Log::info('Student created', ['student_id' => $student->id]);
            
            return $student;
        } catch (\Exception $e) {
            Log::error('Failed to create student', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(Student $student, array $data): Student
    {
        try {
            $student->update($data);
            
            Log::info('Student updated', ['student_id' => $student->id]);
            
            return $student;
        } catch (\Exception $e) {
            Log::error('Failed to update student', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(Student $student): bool
    {
        try {
            $student->delete();
            
            Log::info('Student deleted', ['student_id' => $student->id]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete student', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

### 3. Adding a New Controller

**Controller Template**:

```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index()
    {
        $this->authorize('viewAny', Student::class);
        
        $students = Student::paginate(15);
        
        return view('dashboard.students.index', compact('students'));
    }

    public function create()
    {
        $this->authorize('create', Student::class);
        
        return view('dashboard.students.create');
    }

    public function store(StudentRequest $request)
    {
        $this->authorize('create', Student::class);
        
        $student = $this->studentService->create($request->validated());
        
        return redirect()->route('dashboard.students.show', $student)
            ->with('success', 'Student created successfully');
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);
        
        return view('dashboard.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $this->authorize('update', $student);
        
        return view('dashboard.students.edit', compact('student'));
    }

    public function update(StudentRequest $request, Student $student)
    {
        $this->authorize('update', $student);
        
        $student = $this->studentService->update($student, $request->validated());
        
        return redirect()->route('dashboard.students.show', $student)
            ->with('success', 'Student updated successfully');
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);
        
        $this->studentService->delete($student);
        
        return redirect()->route('dashboard.students.index')
            ->with('success', 'Student deleted successfully');
    }
}
```

### 4. Adding a New Route

**In routes/web.php**:

```php
Route::prefix('dashboard')->as('dashboard.')->middleware(['auth', 'verified', CheckUserStatus::class])->group(function () {
    // Students
    Route::resource('students', StudentController::class)->names('students');
    
    // Custom routes
    Route::post('students/{student}/enroll', [StudentController::class, 'enroll'])->name('students.enroll');
});
```

**In routes/api.php**:

```php
Route::middleware(['auth:sanctum', 'throttle:api-general'])->group(function () {
    Route::apiResource('students', StudentApiController::class);
});
```

### 5. Adding a New Event

**Event Template**:

```php
<?php

namespace App\Events;

use App\Models\Student;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Student $student)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin-notifications'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'student.created';
    }

    public function broadcastWith(): array
    {
        return [
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'created_at' => $this->student->created_at,
        ];
    }
}
```

**Dispatch Event**:

```php
// In service
StudentCreated::dispatch($student);

// In controller
event(new StudentCreated($student));
```

### 6. Adding a New Job

**Job Template**:

```php
<?php

namespace App\Jobs;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStudentImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function handle(): void
    {
        try {
            foreach ($this->data as $row) {
                Student::create($row);
            }
            
            Log::info('Student import completed', ['count' => count($this->data)]);
        } catch (\Exception $e) {
            Log::error('Student import failed', ['error' => $e->getMessage()]);
            $this->fail($e);
        }
    }
}
```

**Dispatch Job**:

```php
// In service
ProcessStudentImportJob::dispatch($data);

// With delay
ProcessStudentImportJob::dispatch($data)->delay(now()->addMinutes(5));
```

---

## DEBUGGING GUIDE

### 1. Enable Debug Mode

```bash
# In .env
APP_DEBUG=true
APP_ENV=local
```

### 2. View Application Logs

```bash
# Real-time logs
tail -f storage/logs/laravel.log

# Filter by error
grep ERROR storage/logs/laravel.log

# Filter by specific date
grep "2026-05-14" storage/logs/laravel.log
```

### 3. Database Debugging

```php
// Enable query logging
DB::enableQueryLog();

// Get queries
$queries = DB::getQueryLog();
dd($queries);

// Or use Laravel Debugbar
// composer require barryvdh/laravel-debugbar --dev
```

### 4. Tinker (Interactive Shell)

```bash
# Start tinker
php artisan tinker

# Query models
$student = Student::find('uuid');
$student->classrooms;

# Create records
Student::create(['name' => 'John', 'email' => 'john@example.com']);

# Update records
$student->update(['name' => 'Jane']);

# Delete records
$student->delete();
```

### 5. Common Issues & Solutions

**Issue**: "Call to undefined method"
```php
// Solution: Check if method exists in model/service
// Add method to model or service
public function methodName() {
    // implementation
}
```

**Issue**: "Relationship not found"
```php
// Solution: Add relationship to model
public function classrooms(): BelongsToMany
{
    return $this->belongsToMany(Classroom::class);
}
```

**Issue**: "Unauthorized action"
```php
// Solution: Check authorization policy
// Add policy method or update middleware
public function view(User $user, Student $student): bool
{
    return $user->hasRole('admin') || $user->id === $student->user_id;
}
```

**Issue**: "CSRF token mismatch"
```php
// Solution: Include CSRF token in form
<form method="POST" action="/students">
    @csrf
    <!-- form fields -->
</form>
```

**Issue**: "N+1 query problem"
```php
// Solution: Use eager loading
// Bad
$students = Student::all();
foreach ($students as $student) {
    echo $student->classroom->name; // Query per student
}

// Good
$students = Student::with('classroom')->get();
foreach ($students as $student) {
    echo $student->classroom->name; // Single query
}
```

---

## ADDING NEW FEATURES

### Example: Add Student Promotion Feature

**Step 1: Create Migration**

```bash
php artisan make:migration create_student_promotions_table
```

```php
Schema::create('student_promotions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('student_id');
    $table->uuid('from_classroom_id');
    $table->uuid('to_classroom_id');
    $table->string('status')->default('pending'); // pending, approved, rejected
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
    $table->foreign('from_classroom_id')->references('id')->on('classrooms')->cascadeOnDelete();
    $table->foreign('to_classroom_id')->references('id')->on('classrooms')->cascadeOnDelete();
});
```

**Step 2: Create Model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentPromotion extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'student_id',
        'from_classroom_id',
        'to_classroom_id',
        'status',
        'notes',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'from_classroom_id');
    }

    public function toClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'to_classroom_id');
    }
}
```

**Step 3: Create Service**

```php
<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentPromotion;
use Illuminate\Support\Facades\Log;

class PromotionService
{
    public function promote(Student $student, string $toClassroomId): StudentPromotion
    {
        try {
            $currentClassroom = $student->classrooms()
                ->wherePivot('status', 'active')
                ->first();

            $promotion = StudentPromotion::create([
                'student_id' => $student->id,
                'from_classroom_id' => $currentClassroom->id,
                'to_classroom_id' => $toClassroomId,
                'status' => 'pending',
            ]);

            Log::info('Student promotion created', [
                'student_id' => $student->id,
                'promotion_id' => $promotion->id,
            ]);

            return $promotion;
        } catch (\Exception $e) {
            Log::error('Failed to create promotion', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function approve(StudentPromotion $promotion): void
    {
        try {
            // Update current classroom status
            $promotion->student->classrooms()
                ->wherePivot('status', 'active')
                ->update(['status' => 'transferred']);

            // Add to new classroom
            $promotion->student->classrooms()->attach(
                $promotion->to_classroom_id,
                ['status' => 'active']
            );

            // Update promotion status
            $promotion->update(['status' => 'approved']);

            Log::info('Student promotion approved', ['promotion_id' => $promotion->id]);
        } catch (\Exception $e) {
            Log::error('Failed to approve promotion', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

**Step 4: Create Controller**

```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StudentPromotion;
use App\Services\PromotionService;

class PromotionController extends Controller
{
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    public function index()
    {
        $promotions = StudentPromotion::with(['student', 'fromClassroom', 'toClassroom'])
            ->paginate(15);

        return view('dashboard.promotions.index', compact('promotions'));
    }

    public function approve(StudentPromotion $promotion)
    {
        $this->authorize('update', $promotion);

        $this->promotionService->approve($promotion);

        return redirect()->back()->with('success', 'Promotion approved');
    }
}
```

**Step 5: Add Route**

```php
// In routes/web.php
Route::resource('promotions', PromotionController::class)->names('promotions');
Route::post('promotions/{promotion}/approve', [PromotionController::class, 'approve'])->name('promotions.approve');
```

**Step 6: Run Migration**

```bash
php artisan migrate
```

---

## PERFORMANCE OPTIMIZATION

### 1. Database Query Optimization

```php
// Bad: N+1 queries
$students = Student::all();
foreach ($students as $student) {
    echo $student->classroom->name; // Query per student
}

// Good: Eager loading
$students = Student::with('classroom')->get();
foreach ($students as $student) {
    echo $student->classroom->name; // Single query
}

// Better: Select specific columns
$students = Student::with('classroom:id,name')
    ->select('id', 'name', 'classroom_id')
    ->get();
```

### 2. Caching

```php
// Cache query results
$students = Cache::remember('students', 3600, function () {
    return Student::with('classroom')->get();
});

// Cache with tags
Cache::tags(['students'])->put('students', $students, 3600);

// Invalidate cache
Cache::tags(['students'])->flush();
```

### 3. Database Indexes

```php
// Add indexes in migration
Schema::table('students', function (Blueprint $table) {
    $table->index('nisn');
    $table->index('email');
    $table->index('classroom_id');
});
```

### 4. Pagination

```php
// Use pagination instead of loading all records
$students = Student::paginate(15);

// In view
{{ $students->links() }}
```

### 5. Lazy Loading

```php
// Load relationships only when needed
$student = Student::find($id);
$student->load('classroom', 'grades');
```

---

## SECURITY HARDENING

### 1. Input Validation

```php
// Always validate user input
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed',
]);
```

### 2. Authorization

```php
// Always authorize actions
$this->authorize('update', $student);

// Or use policy
if ($user->cannot('update', $student)) {
    abort(403);
}
```

### 3. CSRF Protection

```php
// Include CSRF token in forms
<form method="POST">
    @csrf
    <!-- form fields -->
</form>
```

### 4. SQL Injection Prevention

```php
// Use parameterized queries
$students = Student::where('name', $name)->get();

// Not this
$students = DB::select("SELECT * FROM students WHERE name = '$name'");
```

### 5. XSS Prevention

```php
// Escape output in views
{{ $student->name }}

// Not this
{!! $student->name !!}
```

---

## TROUBLESHOOTING

### Common Issues

**Issue**: Application not starting
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check permissions
chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:clear
```

**Issue**: Database connection error
```bash
# Check .env file
cat .env | grep DB_

# Test connection
php artisan tinker
DB::connection()->getPdo();
```

**Issue**: Migrations not running
```bash
# Check migration status
php artisan migrate:status

# Rollback and re-run
php artisan migrate:rollback
php artisan migrate
```

**Issue**: Queue jobs not processing
```bash
# Check queue status
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Start queue worker
php artisan queue:work
```

**Issue**: Broadcasting not working
```bash
# Check Reverb status
php artisan reverb:start

# Check channels.php configuration
cat routes/channels.php

# Test broadcasting
php artisan tinker
event(new \App\Events\TestEvent());
```

---

## USEFUL COMMANDS

```bash
# Artisan commands
php artisan migrate                 # Run migrations
php artisan migrate:rollback        # Rollback migrations
php artisan seed                    # Run seeders
php artisan tinker                  # Interactive shell
php artisan queue:work              # Start queue worker
php artisan reverb:start            # Start WebSocket server
php artisan cache:clear             # Clear cache
php artisan config:clear            # Clear config cache
php artisan route:list              # List all routes
php artisan make:model Model        # Create model
php artisan make:controller Controller # Create controller
php artisan make:migration migration # Create migration
php artisan make:seeder Seeder      # Create seeder
php artisan make:event Event        # Create event
php artisan make:job Job            # Create job
php artisan make:mail Mail          # Create mailable

# Composer commands
composer install                    # Install dependencies
composer update                     # Update dependencies
composer require package            # Add package
composer remove package             # Remove package

# NPM commands
npm install                         # Install dependencies
npm run dev                         # Development build
npm run build                       # Production build
npm run watch                       # Watch for changes

# Git commands
git status                          # Check status
git add .                           # Stage changes
git commit -m "message"             # Commit changes
git push                            # Push to remote
git pull                            # Pull from remote
```

---

## CONCLUSION

This skill guide provides everything needed to maintain, debug, and extend ProductSchool. For more information, refer to:

- [Laravel Documentation](https://laravel.com/docs)
- [Eloquent Documentation](https://laravel.com/docs/eloquent)
- [Spatie Permissions](https://spatie.be/docs/laravel-permission)
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)

---

**Last Updated**: May 14, 2026  
**Maintained By**: Kiro AI Agent
