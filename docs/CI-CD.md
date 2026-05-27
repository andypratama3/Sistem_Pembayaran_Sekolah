# CI/CD Pipeline Documentation - ProductSchool

## Overview

ProductSchool uses **GitHub Actions** for continuous integration and deployment. The pipeline automatically tests, lints, and deploys code changes.

## Pipeline Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    GitHub Event                             │
│  (Push to main/develop or Pull Request)                     │
└────────────────────┬────────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
        ▼                         ▼
   ┌─────────┐              ┌─────────┐
   │  TEST   │              │  LINT   │
   │  JOB    │              │  JOB    │
   │ (30min) │              │ (15min) │
   └────┬────┘              └────┬────┘
        │                        │
        └────────────┬───────────┘
                     │
                     ▼
            ✅ All checks pass?
                     │
        ┌────────────┴────────────┐
        │ YES                     │ NO
        ▼                         ▼
   ┌─────────┐              ❌ FAIL
   │ DEPLOY  │
   │  JOB    │
   │ (20min) │
   │(main    │
   │ only)   │
   └────┬────┘
        │
        ▼
   🚀 Production
```

## Workflow Triggers

### Automatic Triggers

1. **Push to main branch**
   - Runs all jobs (test, lint, deploy)
   - Deploy job runs after test and lint pass

2. **Push to develop branch**
   - Runs test and lint jobs only
   - No deployment

3. **Pull Request to main/develop**
   - Runs test and lint jobs only
   - No deployment

### Manual Triggers

Currently, no manual triggers are configured. To add manual trigger:

```yaml
on:
  workflow_dispatch:
    inputs:
      environment:
        description: 'Deployment environment'
        required: true
        default: 'staging'
```

## Jobs

### 1. TEST JOB

**Purpose**: Run PHPUnit tests with coverage reporting

**Timeout**: 30 minutes

**Environment**:
- OS: Ubuntu Latest
- PHP: 8.3
- Node: 22
- Database: SQLite in-memory

**Steps**:

1. **Checkout code**
   - Fetches the repository code

2. **Setup PHP**
   - Installs PHP 8.3
   - Installs required extensions:
     - sqlite3, pdo_sqlite (database)
     - redis (caching)
     - gd (image processing)
     - zip, bcmath, mbstring, exif, pcntl, sockets, opcache

3. **Setup Node.js**
   - Installs Node 22
   - Caches npm dependencies

4. **Cache Composer dependencies**
   - Speeds up dependency installation

5. **Cache NPM dependencies**
   - Speeds up npm install

6. **Install Composer dependencies**
   - Runs `composer install`

7. **Generate app key**
   - Generates encryption key for testing

8. **Seed database**
   - Runs database seeders if needed

9. **Install NPM dependencies**
   - Runs `npm ci`

10. **Build frontend assets**
    - Runs `npm run build`

11. **Create SQLite directory**
    - Ensures database directory exists

12. **Run tests**
    - Runs `php artisan test --coverage`
    - Generates coverage report

13. **Upload coverage report**
    - Uploads `coverage.xml` as artifact
    - Retention: 30 days

**Success Criteria**:
- All tests pass
- No PHP errors
- Coverage report generated

### 2. LINT JOB

**Purpose**: Check code style and static analysis

**Timeout**: 15 minutes

**Environment**:
- OS: Ubuntu Latest
- PHP: 8.3

**Steps**:

1. **Checkout code**
   - Fetches the repository code

2. **Setup PHP**
   - Installs PHP 8.3
   - Installs required extensions:
     - pdo_mysql, mbstring, bcmath

3. **Cache Composer dependencies**
   - Speeds up dependency installation

4. **Install Composer dependencies**
   - Runs `composer install`

5. **Run Pint (Code Style)**
   - Checks code formatting
   - Fails if code doesn't match style guide

6. **Run PHPStan (Static Analysis)**
   - Analyzes code for potential bugs
   - Level 5 (strict)
   - Memory limit: 512MB
   - Outputs errors in GitHub format

7. **Run Larastan (Laravel Static Analysis)**
   - Laravel-specific static analysis
   - Checks for common Laravel issues

**Success Criteria**:
- Code passes Pint style check
- No PHPStan errors
- No Larastan errors

### 3. DEPLOY JOB

**Purpose**: Deploy to production server

**Timeout**: 20 minutes

**Conditions**:
- Only runs on `main` branch
- Only on push events (not pull requests)
- Only after test and lint jobs pass

**Steps**:

1. **Checkout code**
   - Fetches the repository code

2. **Deploy via SSH**
   - Connects to production server
   - Runs deployment script

**Deployment Script**:

```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-progress --prefer-dist --optimize-autoloader

# Build frontend
npm ci && npm run build

# Run migrations
php artisan migrate --force

# Optimize application
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart services
php artisan queue:restart
php artisan reverb:restart
```

3. **Health check**
   - Waits 10 seconds for services to restart
   - Checks `/up` endpoint
   - Fails if endpoint not available

**Success Criteria**:
- SSH connection successful
- All commands execute without error
- Health check passes

## Environment Variables

### Shared (All Jobs)

```yaml
BROADCAST_DRIVER: reverb
REVERB_APP_ID: '12345'
REVERB_APP_KEY: test-key-for-ci
REVERB_APP_SECRET: test-secret-for-ci
REVERB_HOST: localhost
REVERB_PORT: '8080'
PUSHER_APP_ID: '12345'
PUSHER_APP_KEY: test-key-for-ci
PUSHER_APP_SECRET: test-secret-for-ci
PUSHER_HOST: localhost
PUSHER_PORT: '6001'
PUSHER_SCHEME: http
APP_ENV: testing
DB_CONNECTION: sqlite
DB_DATABASE: ':memory:'
CACHE_STORE: array
SESSION_DRIVER: array
QUEUE_CONNECTION: sync
MAIL_MAILER: array
```

### Secrets (GitHub Secrets)

Required secrets for deployment:

```
DEPLOY_HOST      - Production server hostname/IP
DEPLOY_USER      - SSH username
DEPLOY_KEY       - SSH private key
```

**To add secrets**:
1. Go to GitHub repository
2. Settings → Secrets and variables → Actions
3. Click "New repository secret"
4. Add each secret

## Concurrency

The workflow uses concurrency to prevent duplicate runs:

```yaml
concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true
```

**Behavior**:
- If a new push happens while workflow is running
- Previous workflow is cancelled
- New workflow starts
- Prevents resource waste

## Caching Strategy

### Composer Cache

```yaml
key: ${{ runner.os }}-composer-${{ hashFiles('src/composer.lock') }}
```

- Cache key includes `composer.lock` hash
- Cache invalidated when dependencies change
- Fallback to base key if exact match not found

### NPM Cache

```yaml
key: ${{ runner.os }}-npm-${{ hashFiles('src/package-lock.json') }}
```

- Cache key includes `package-lock.json` hash
- Cache invalidated when dependencies change
- Fallback to base key if exact match not found

## Monitoring & Debugging

### View Workflow Runs

1. Go to GitHub repository
2. Click "Actions" tab
3. Select workflow run
4. View job logs

### Common Issues

#### Test Job Fails

**Check**:
- PHP version compatibility
- Missing extensions
- Database connection
- Environment variables

**Debug**:
```bash
# View full logs
# Click on failed job → View logs

# Run locally
cd src
php artisan test --verbose
```

#### Lint Job Fails

**Check**:
- Code style violations
- PHPStan errors
- Larastan errors

**Debug**:
```bash
# Check code style
cd src
vendor/bin/pint --test

# Fix code style
vendor/bin/pint

# Run static analysis
vendor/bin/phpstan analyse app --level=5
```

#### Deploy Job Fails

**Check**:
- SSH credentials
- Server connectivity
- Deployment script errors
- Disk space on server

**Debug**:
```bash
# Test SSH connection
ssh -i deploy_key user@host

# Check server logs
ssh user@host tail -f /var/www/productschool/storage/logs/laravel.log
```

## Performance Optimization

### Current Performance

- **Test Job**: ~5-10 minutes
- **Lint Job**: ~2-3 minutes
- **Deploy Job**: ~5 minutes
- **Total**: ~12-18 minutes

### Optimization Tips

1. **Parallel Jobs**
   - Test and Lint run in parallel
   - Saves ~5 minutes per run

2. **Caching**
   - Composer cache saves ~2 minutes
   - NPM cache saves ~1 minute

3. **Database**
   - SQLite in-memory is fast
   - No network latency

4. **Selective Testing**
   - Could add job to run only affected tests
   - Requires test dependency mapping

## Best Practices

### ✅ Do's

1. **Write meaningful commit messages**
   ```
   ✅ Good: "Fix user authentication bug in login controller"
   ❌ Bad: "Fix bug"
   ```

2. **Keep commits small and focused**
   - One feature per commit
   - Easier to debug if tests fail

3. **Run tests locally before pushing**
   ```bash
   cd src
   php artisan test
   vendor/bin/pint --test
   vendor/bin/phpstan analyse app --level=5
   ```

4. **Use feature branches**
   ```bash
   git checkout -b feature/user-authentication
   ```

5. **Create pull requests for code review**
   - Allows team review before merge
   - Catches issues early

### ❌ Don'ts

1. **Don't push directly to main**
   - Always use pull requests
   - Allows CI/CD to validate

2. **Don't ignore failing tests**
   - Fix tests before merging
   - Don't skip tests in CI/CD

3. **Don't commit secrets**
   - Use environment variables
   - Use GitHub Secrets for sensitive data

4. **Don't modify workflow without testing**
   - Test workflow changes in branch
   - Use `workflow_dispatch` for manual testing

5. **Don't leave long-running jobs**
   - Optimize slow tests
   - Consider splitting into multiple jobs

## Troubleshooting

### Workflow Not Triggering

**Check**:
- Branch name matches trigger (main/develop)
- Event type matches (push/pull_request)
- Workflow file syntax is valid

**Solution**:
```bash
# Validate workflow syntax
# Use GitHub's workflow validator
# Or run locally with act: https://github.com/nektos/act
```

### Deployment Fails

**Check**:
- SSH credentials are correct
- Server is accessible
- Disk space available
- Permissions correct

**Solution**:
```bash
# Test SSH connection
ssh -i ~/.ssh/deploy_key user@host

# Check server status
ssh user@host systemctl status php-fpm
ssh user@host systemctl status nginx
```

### Tests Timeout

**Check**:
- Tests running too slowly
- Database queries not optimized
- External API calls not mocked

**Solution**:
```bash
# Increase timeout
# Edit .github/workflows/ci-cd.yml
timeout-minutes: 45

# Or optimize tests
php artisan test --profile
```

## Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [PHPUnit Documentation](https://phpunit.de/)
- [Pint Documentation](https://laravel.com/docs/pint)
- [PHPStan Documentation](https://phpstan.org/)

