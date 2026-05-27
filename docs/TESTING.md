# Testing Guide - ProductSchool

## Overview

ProductSchool uses **PHPUnit** for unit and feature testing. The test suite is designed to run in CI/CD pipelines and locally during development.

## Test Structure

```
src/tests/
├── bootstrap.php              # Test bootstrap configuration
├── TestCase.php               # Base test case class
├── Bootstrap/
│   └── DisableForeignKeys.php # Foreign key management for tests
├── Unit/
│   └── SalaryCalculationServiceTest.php
└── Feature/
    ├── Auth/                  # Authentication tests (9 tests)
    ├── Payments/              # Payment processing tests (2 tests)
    ├── Grades/                # Grade management tests (1 test)
    ├── Settings/              # Settings tests (1 test)
    ├── BulkOperations/        # Bulk operation tests (1 test)
    ├── Admissions/            # Admission tests
    ├── Analytics/             # Analytics tests
    ├── Api/                   # API endpoint tests
    ├── Attendances/           # Attendance tests
    ├── Classrooms/            # Classroom tests
    ├── Dashboard/             # Dashboard tests
    ├── Employees/             # Employee tests
    ├── Jobs/                  # Job queue tests
    ├── Leave/                 # Leave management tests
    ├── Promotions/            # Promotion tests
    ├── ReportCardDistribution/# Report card tests
    ├── Students/              # Student tests
    ├── Subjects/              # Subject tests
    ├── Teachers/              # Teacher tests
    ├── WhatsApp/              # WhatsApp integration tests
    └── [Root level tests]     # 8 additional feature tests
```

**Total: 87 test files**
- Unit Tests: 1
- Feature Tests: 86

## Running Tests

### Run All Tests
```bash
cd src
php artisan test
```

### Run with Coverage Report
```bash
cd src
php artisan test --coverage
```

### Run Specific Test File
```bash
cd src
php artisan test tests/Feature/Auth/LoginTest.php
```

### Run Specific Test Class
```bash
cd src
php artisan test tests/Feature/Auth/LoginTest.php --filter LoginTest
```

### Run Specific Test Method
```bash
cd src
php artisan test tests/Feature/Auth/LoginTest.php --filter testUserCanLogin
```

### Run Unit Tests Only
```bash
cd src
php artisan test tests/Unit
```

### Run Feature Tests Only
```bash
cd src
php artisan test tests/Feature
```

### Run Tests in Parallel
```bash
cd src
php artisan test --parallel
```

### Run Tests with Verbose Output
```bash
cd src
php artisan test --verbose
```

## Test Configuration

### PHPUnit Configuration (`phpunit.xml`)

```xml
<phpunit>
  <testsuites>
    <testsuite name="Unit">
      <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
      <directory>tests/Feature</directory>
    </testsuite>
  </testsuites>
  
  <php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="MAIL_MAILER" value="array"/>
  </php>
</phpunit>
```

**Key Settings:**
- **Database**: SQLite in-memory (`:memory:`) for fast tests
- **Cache**: Array driver (no persistence)
- **Session**: Array driver (no persistence)
- **Queue**: Sync driver (execute immediately)
- **Mail**: Array driver (no actual emails sent)

### Test Environment (`.env.testing.example`)

```env
APP_ENV=testing
APP_KEY=base64:yqFSd/HfLo+jb+YTyxAr72SWcWQ2BSAPSLU/AkiaOJU=
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
PULSE_ENABLED=false
TELESCOPE_ENABLED=false
SENTRY_LARAVEL_DSN=null
```

## Writing Tests

### Basic Feature Test

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    public function test_user_can_view_profile()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get('/profile');
        
        $response->assertStatus(200);
        $response->assertSee($user->name);
    }
}
```

### Basic Unit Test

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SalaryCalculationService;

class SalaryCalculationTest extends TestCase
{
    public function test_calculate_salary_correctly()
    {
        $service = new SalaryCalculationService();
        $salary = $service->calculate(100000, 0.15);
        
        $this->assertEquals(85000, $salary);
    }
}
```

### API Test

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;

class UserApiTest extends TestCase
{
    public function test_get_users_endpoint()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->getJson('/api/users');
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'email']]]);
    }
}
```

## CI/CD Integration

### GitHub Actions Workflow

Tests run automatically on:
- **Push** to `main` or `develop` branches
- **Pull Requests** to `main` or `develop` branches

### Workflow Jobs

1. **Test Job** (30 min timeout)
   - Runs PHPUnit with coverage
   - Uploads coverage report as artifact
   - Uses SQLite in-memory database

2. **Lint Job** (15 min timeout)
   - Runs Pint (code style checker)
   - Runs PHPStan (static analysis, level 5)

3. **Deploy Job** (20 min timeout)
   - Runs only on `main` branch push
   - Requires test and lint jobs to pass
   - Deploys via SSH to production server

### Coverage Reports

Coverage reports are generated and uploaded as artifacts:
- **Location**: `src/coverage.xml`
- **Retention**: 30 days
- **Format**: Clover XML format

## Best Practices

### ✅ Do's

1. **Write descriptive test names**
   ```php
   public function test_user_can_login_with_valid_credentials()
   ```

2. **Use factories for test data**
   ```php
   $user = User::factory()->create(['email' => 'test@example.com']);
   ```

3. **Test one thing per test**
   ```php
   public function test_login_validates_email()
   public function test_login_validates_password()
   ```

4. **Use meaningful assertions**
   ```php
   $response->assertStatus(200);
   $response->assertSee('Welcome');
   ```

5. **Clean up after tests**
   ```php
   protected function tearDown(): void
   {
       parent::tearDown();
       // Cleanup code
   }
   ```

### ❌ Don'ts

1. **Don't test framework features**
   - Laravel already tests these
   - Focus on your application logic

2. **Don't use real external services**
   - Mock API calls
   - Mock email services
   - Mock payment gateways

3. **Don't create interdependent tests**
   - Each test should be independent
   - Don't rely on test execution order

4. **Don't use sleep() or delays**
   - Tests should be fast
   - Use mocking instead

5. **Don't commit test credentials**
   - Use `.env.testing.example`
   - Never commit `.env.testing`

## Troubleshooting

### Tests Fail Locally but Pass in CI

**Possible causes:**
- Different PHP version
- Missing extensions
- Database state issues
- Environment variable differences

**Solution:**
```bash
# Check PHP version
php -v

# Check extensions
php -m

# Run tests with verbose output
php artisan test --verbose

# Check .env.testing
cat .env.testing
```

### Tests Run Slowly

**Possible causes:**
- Database queries not optimized
- Missing indexes
- Too many database operations

**Solution:**
```bash
# Run with profiling
php artisan test --profile

# Check query count
php artisan test --verbose
```

### Memory Issues

**Solution:**
```bash
# Increase memory limit
php -d memory_limit=512M artisan test

# Or in phpunit.xml
<php>
    <ini name="memory_limit" value="512M"/>
</php>
```

## Coverage Goals

**Target Coverage:**
- Overall: 80%+
- Critical paths: 90%+
- Controllers: 85%+
- Services: 90%+
- Models: 75%+

**View Coverage Report:**
```bash
cd src
php artisan test --coverage
# Open coverage/index.html in browser
```

## Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Testing Best Practices](https://laravel.com/docs/testing#best-practices)

