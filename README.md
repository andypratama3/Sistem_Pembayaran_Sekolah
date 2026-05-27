# 🎓 ProductSchool — Comprehensive School Management System

[![PHP](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-9.6+-lightblue.svg)](https://www.mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen.svg)](VERIFICATION_STATUS.md)

A complete **Laravel 12 education management system** for schools, featuring student lifecycle management, academic tracking, HR/payroll, payment processing, WhatsApp integration, and real-time communication.

---

## 📋 Table of Contents

- [Features](#-features)
- [System Architecture](#-system-architecture)
- [Tech Stack](#-tech-stack)
- [Quick Start](#-quick-start)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Modules Overview](#-modules-overview)
- [Security Features](#-security-features)
- [API Documentation](#-api-documentation)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [Support](#-support)

---

## 🌟 Features

### 🔐 Authentication & Authorization
- **Spatie Laravel Permission** RBAC system with 5+ roles
- Multi-role support: Superadmin, Admin, Academic Admin, HR, Finance, Teachers, Students
- OAuth2 ready, session management
- Two-factor authentication support

### 👨‍🎓 Student Management
- Complete student lifecycle: registration → admission → enrollment → graduation
- Student status tracking (new, active, graduated, transferred, retained, dropped)
- **Early Warning System (EWS)** for at-risk students
- Risk assessment: grade×0.4 + attendance×0.3 + behavior×0.2 + engagement×0.1
- Student analytics and performance snapshots
- Bulk promotion, transfer, graduation, retention workflows

### 📚 Academic & Curriculum
- **Digital Report Cards (Rapor)** generation with PDF export
- **P5 Character Assessment** (Pancasila Student Profile)
- Grade weighting system with validated distribution (must total 100%)
- Multi-subject curriculum support
- Automated narration generation for report cards
- Grade posting events and audit trails

### 👔 HR & Payroll
- Complete **6-component salary calculation system**:
  - Base salary (gaji pokok)
  - Seniority allowance (tunjangan masa kerja)
  - Education allowance (tunjangan pendidikan)
  - Structural allowance (tunjangan struktural)
  - Functional allowance (tunjangan fungsional)
  - Family allowance (tunjangan keluarga)
- **GPS-based attendance** with geofence validation
- Employee directory and performance tracking
- Bulk payroll processing
- Payment status workflow: pending → processed → paid

### 💳 Payment & Billing
- **Midtrans Snap** integration for secure payments
- Webhook notification system with HMAC signature verification
- Multiple payment status tracking (pending, paid, failed, refunded, cancelled)
- Invoice generation and audit trails
- Automated payment reminders
- Transaction reconciliation

### 📱 WhatsApp Integration
- **Meta WhatsApp Business API** integration
- **10-item smart menu bot** with context-aware routing
- Automatic work hours detection (school hours → teacher routed, off-hours → admin queued)
- HMAC signature verification for webhook security
- Real-time message delivery with Pusher
- Student/parent notification system
- Admin broadcast capability

### ✅ Task Management
- Hierarchical task system with subtasks
- Task dependency management
- Multiple status tracking (pending, in_progress, completed, blocked, archived)
- Auto-calculated progress from subtask completion
- Task assignment and team collaboration
- Timeline and deadline tracking

### 📰 Content Management (CMS)
- Article/post publishing system
- Category organization
- Media gallery management
- SEO-friendly content structure
- Draft → published workflow

### 🏫 Admission System
- End-to-end admission workflow
- Application status tracking: application → review → approved → enrolled
- Order ID generation (ADM-YYYYMMDD-XXXXX)
- Document management
- Automated confirmation emails/WhatsApp messages

### 🔄 Real-Time Features
- **Laravel Reverb** for WebSocket support
- **Pusher** integration for real-time notifications
- Live dashboard updates
- Real-time message delivery (WhatsApp, SMS)
- Live student status updates

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    BROWSER (End User)                        │
│  ┌──────────────────────────────────────────────────────┐   │
│  │            Laravel Blade + Alpine.js                 │   │
│  │  • Dashboard  • Forms  • Reports  • Real-time UI    │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↕ HTTP/HTTPS
                            ↕ API REST
┌─────────────────────────────────────────────────────────────┐
│              LARAVEL 12 APPLICATION LAYER                    │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │              REQUEST PIPELINE                        │    │
│  │  • Authentication (Spatie RBAC)                      │    │
│  │  • CSRF Protection  • Rate Limiting                  │    │
│  │  • Validation  • Authorization Checks                │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │         CONTROLLERS & ACTION HANDLERS                │    │
│  │  • StudentController  • PaymentController            │    │
│  │  • GradeController  • EmployeeController             │    │
│  │  • AdmissionController  • TaskController             │    │
│  │  • WhatsAppWebhookController  • APIControllers       │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │           BUSINESS LOGIC (SERVICES)                  │    │
│  │  • StudentService  • GradeService                    │    │
│  │  • SalaryCalculationService                          │    │
│  │  • MidtransService  • WhatsappMetaService            │    │
│  │  • EarlyWarningService  • RaporDistributionService   │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │         DATA MODELS (Eloquent ORM)                   │    │
│  │  • User  • Student  • Employee  • Payment            │    │
│  │  • Grade  • Task  • Article  • Admission             │    │
│  │  • Template  • Notification  • AuditLog              │    │
│  └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│           EXTERNAL INTEGRATIONS & SERVICES                   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Database: MySQL 9.6+  │  Cache: Redis/Memcached     │   │
│  │ Storage: S3/Local     │  Queue: Redis/Database      │   │
│  │ Payment: Midtrans     │  SMS/WA: Meta API           │   │
│  │ Real-time: Reverb/Pusher  │  Logging: Sentry        │   │
│  │ PDF Generation: DomPDF    │  Email: SMTP/SendGrid    │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## 🛠️ Tech Stack

### Backend
- **PHP 8.3+** — Modern PHP with type hints and attributes
- **Laravel 12.x** — Full-featured web framework
- **MySQL 9.6+** — Relational database
- **Redis** — Caching and queue driver
- **Laravel Reverb** — WebSocket server for real-time features

### Frontend
- **Blade Templating** — Server-side template engine
- **Alpine.js** — Lightweight JavaScript framework
- **Tailwind CSS** — Utility-first CSS framework
- **Chart.js** — Data visualization
- **DataTables** — Advanced table functionality

### Third-Party Services
- **Midtrans** — Payment gateway (Snap)
- **Meta WhatsApp Business API** — WhatsApp integration
- **Pusher** — Real-time messaging
- **Sentry** — Error tracking and monitoring
- **DomPDF** — PDF generation

### Development Tools
- **PHPUnit** — Unit testing framework (551+ tests)
- **Laravel Dusk** — End-to-end testing
- **Pest** — Elegant testing framework
- **Laravel Pint** — Code style fixer
- **PHPStan** — Static analysis

---

## 🚀 Quick Start

### Prerequisites
```bash
PHP 8.3+
Composer
MySQL 9.6+
Redis (optional but recommended)
Node.js 18+ (for frontend assets)
```

### Clone & Setup (5 minutes)
```bash
# 1. Clone repository
git clone https://github.com/andypratama3/ProductsSchool.git
cd ProductsSchool

# 2. Install dependencies
cd src
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Database setup
php artisan migrate --seed

# 5. Build assets
npm run build

# 6. Start development server
php artisan serve
# Access: http://localhost:8000
```

---

## 📦 Installation

### Full Installation Guide

#### Step 1: Clone Repository
```bash
git clone https://github.com/andypratama3/ProductsSchool.git
cd ProductsSchool/src
```

#### Step 2: Install PHP Dependencies
```bash
composer install
composer require spatie/laravel-permission
composer require barryvdh/laravel-dompdf
composer require midtrans/midtrans-php
composer require yajra/laravel-datatables-oracle
```

#### Step 3: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your configuration:
```env
APP_NAME="ProductSchool"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourschool.edu

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=productschool
DB_USERNAME=root
DB_PASSWORD=your_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database

MIDTRANS_SERVER_KEY=your_midtrans_key
MIDTRANS_CLIENT_KEY=your_midtrans_client_key
MIDTRANS_IS_PRODUCTION=false

WHATSAPP_BUSINESS_ACCOUNT_ID=your_account_id
WHATSAPP_PHONE_NUMBER_ID=your_phone_id
WHATSAPP_ACCESS_TOKEN=your_access_token
WHATSAPP_VERIFY_TOKEN=your_verify_token

PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret

SENTRY_DSN=your_sentry_dsn
```

#### Step 4: Database Migration
```bash
php artisan migrate
php artisan seed:seeders
php artisan storage:link
```

#### Step 5: Frontend Assets
```bash
npm install
npm run build
# Or for development with watch:
npm run dev
```

#### Step 6: Verify Installation
```bash
php artisan tinker
# > User::count()  // Should return users from seeder
# > exit
```

---

## ⚙️ Configuration

### Email Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourschool.edu
MAIL_FROM_NAME="${APP_NAME}"
```

### Midtrans Setup
```php
// config/midtrans.php
'server_key' => env('MIDTRANS_SERVER_KEY'),
'client_key' => env('MIDTRANS_CLIENT_KEY'),
'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
```

### WhatsApp Webhook
Configure in Meta Business Manager:
```
Webhook URL: https://yourschool.edu/api/whatsapp/webhook
Verify Token: ${WHATSAPP_VERIFY_TOKEN}
Subscribe to: messages, message_status
```

### Real-Time Features
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_HOST=api-mt1.pusher.com
PUSHER_PORT=443
PUSHER_SCHEME=https
```

---

## 📚 Modules Overview

### 1. Student Management Module
**Controllers:**
- `StudentController` — CRUD operations, status management
- `PromotionController` — Student promotion to next level
- `TransferController` — Student transfer between schools
- `GraduationController` — Graduation workflow
- `RetentionController` — Student retention (repeat level)

**Services:**
- `StudentService` — Business logic for student operations
- `EarlyWarningService` — At-risk student detection

**Key Endpoints:**
```
GET    /api/students                  List all students
POST   /api/students                  Create student
GET    /api/students/{id}             Get student details
PUT    /api/students/{id}             Update student
DELETE /api/students/{id}             Delete student
POST   /api/students/{id}/promote     Promote student
POST   /api/students/{id}/transfer    Transfer student
POST   /api/students/{id}/graduate    Graduate student
```

### 2. Academic Module
**Controllers:**
- `GradeController` — Grade entry and management
- `RaporController` — Report card generation
- `P5AssessmentController` — Character assessment

**Services:**
- `GradeService` — Grade calculation and validation
- `RaporDistributionService` — Report card distribution
- `NarasiGeneratorService` — Automated narration generation

**Key Endpoints:**
```
POST   /api/grades                    Record grade
GET    /api/rapor/{student_id}        Generate report card
GET    /api/rapor/{student_id}/pdf    Download as PDF
POST   /api/p5-assessment            Record P5 assessment
```

### 3. HR & Payroll Module
**Controllers:**
- `EmployeeController` — Employee management
- `EmployeeAttendanceController` — Attendance with GPS
- `PayrollController` — Payroll processing

**Services:**
- `SalaryCalculationService` — 6-component salary calculation
- `AttendanceService` — GPS validation and tracking

**Key Endpoints:**
```
POST   /api/attendance                Record attendance (with GPS)
POST   /api/payroll/calculate        Calculate salaries
POST   /api/payroll/process          Process payroll run
GET    /api/payroll/slip/{employee_id} Get salary slip
```

### 4. Payment Module
**Controllers:**
- `PaymentController` — Payment management
- `MidtransWebhookController` — Payment notifications

**Services:**
- `MidtransService` — Payment gateway integration
  - `createTransaction()` — Create payment request
  - `processCallback()` — Handle payment notification
  - `verifySignature()` — HMAC verification

**Key Endpoints:**
```
POST   /api/payments                  Create payment
GET    /api/payments/{id}             Get payment status
POST   /api/midtrans/notification     Webhook (internal)
```

### 5. WhatsApp Module
**Controllers:**
- `WhatsAppWebhookController` — Webhook receiver

**Services:**
- `WhatsappMetaService` — Meta API integration
- `WhatsAppBotService` — Bot logic with 10-item menu
- `WhatsAppAdminRouterService` — Message routing

**Bot Menu (10 Items):**
1. Cek Tagihan (Check Billing)
2. Cara Pembayaran (Payment Methods)
3. Info NISN (Student ID Info)
4. Tagihan Belum (Pending Charges)
5. Jadwal (Schedule)
6. Pendaftaran (Registration)
7. Izin (Permission Requests)
8. Ekstrakurikuler (Extra-curricular)
9. Kontak (Contact)
10. Lainnya (Other)

**Webhook Endpoint:**
```
POST   /api/whatsapp/webhook          Message receiver
```

### 6. Task Management Module
**Controllers:**
- `TaskController` — Task CRUD and management

**Features:**
- Hierarchical subtasks
- Dependency management
- Status tracking
- Auto-calculated progress

**Key Endpoints:**
```
POST   /api/tasks                     Create task
GET    /api/tasks                     List tasks
POST   /api/tasks/{id}/subtasks       Add subtask
POST   /api/tasks/{id}/complete       Mark complete
```

### 7. Admission Module
**Controllers:**
- `AdmissionController` — Admission workflow

**Workflow:**
```
Application → Review → Approved → Enrolled
```

**Key Endpoints:**
```
POST   /api/admissions                Submit application
GET    /api/admissions/{id}           Get status
POST   /api/admissions/{id}/review    Review application
POST   /api/admissions/{id}/approve   Approve
POST   /api/admissions/{id}/enroll    Enroll student
```

---

## 🔒 Security Features

### Authentication & Authorization
- ✅ Spatie RBAC with middleware protection
- ✅ Password hashing with bcrypt
- ✅ Session management
- ✅ CSRF protection on all forms
- ✅ Rate limiting on API endpoints

### Data Protection
- ✅ Input validation on all requests
- ✅ SQL parameterization (no raw queries)
- ✅ Mass assignment protection ($fillable/$guarded)
- ✅ Sensitive data redaction in logs
- ✅ Encryption for sensitive fields

### External Integration Security
- ✅ Midtrans: HMAC signature verification
- ✅ WhatsApp: X-Hub-Signature validation
- ✅ API: Bearer token authentication
- ✅ Webhook: IP whitelisting
- ✅ Rate limiting: Throttle middleware

### Monitoring & Logging
- ✅ Audit trails for sensitive operations
- ✅ Sentry error tracking
- ✅ Query logging for optimization
- ✅ Sensitive field redaction
- ✅ Activity logging for compliance

### Verified Security Checklist ✅
```
[✓] 96/96 models have mass assignment protection
[✓] No hardcoded credentials found
[✓] .env protected in .gitignore
[✓] 0 CRITICAL security findings
[✓] 0 HIGH severity findings
[✓] All external webhooks verified with signatures
[✓] CSRF protection on all forms
[✓] Authorization checks on protected endpoints
```

---

## 📖 API Documentation

### Authentication
All API endpoints (except `/api/login`, `/api/register`) require authentication:

```bash
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
     https://api.yourschool.edu/api/students
```

### Response Format
```json
{
  "success": true,
  "data": { /* resource data */ },
  "message": "Operation completed successfully"
}
```

### Error Handling
```json
{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "email": ["Email must be unique"]
  }
}
```

### Rate Limiting
- **Default:** 60 requests per minute per IP
- **API:** 120 requests per minute per token
- **Webhook:** 1000 requests per minute (Midtrans, WhatsApp)

### Pagination
```bash
# Get page 2 with 50 items per page
GET /api/students?page=2&per_page=50

# Response includes:
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": "...",
    "next": "..."
  },
  "meta": {
    "current_page": 2,
    "from": 51,
    "last_page": 10,
    "per_page": 50,
    "total": 500
  }
}
```

---

## 🧪 Testing

### Running Tests
```bash
# All tests
php artisan test

# Specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# With coverage
php artisan test --coverage --min=60

# Specific test file
php artisan test tests/Feature/StudentTest.php

# Watch mode (requires Laravel Dusk or Pest)
php artisan test --watch
```

### Test Coverage
```
✅ Total Tests: 551
✅ Passed: 544 (98.7%)
✅ Skipped: 7
✅ Coverage: 50%+ (auto-retry enabled)
```

### Key Test Suites
- `tests/Feature/StudentTest.php` — Student management workflows
- `tests/Feature/PaymentTest.php` — Payment processing
- `tests/Feature/WhatsAppWebhookTest.php` — WhatsApp integration
- `tests/Unit/SalaryCalculationTest.php` — Payroll calculations
- `tests/Unit/MidtransServiceTest.php` — Payment gateway

---

## 🌐 Deployment

### Production Checklist
```
[ ] APP_DEBUG=false in .env
[ ] APP_ENV=production in .env
[ ] Database backed up
[ ] .env variables configured for production
[ ] APP_KEY generated: php artisan key:generate
[ ] Composer dependencies installed: composer install --no-dev
[ ] Database migrated: php artisan migrate --force
[ ] Frontend assets built: npm run build
[ ] Cache cleared: php artisan config:cache
[ ] Storage symlink created: php artisan storage:link
[ ] Queue worker running: php artisan queue:work (or supervisor)
[ ] Reverb server running: php artisan reverb:start (WebSocket)
[ ] SSL certificate installed
[ ] Backups configured
[ ] Monitoring activated (Sentry DSN set)
```

### Docker Deployment
```dockerfile
FROM php:8.3-fpm
RUN apt-get update && apt-get install -y \
    libpq-dev mysql-client redis-tools
RUN docker-php-ext-install pdo pdo_mysql bcmath
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader
RUN php artisan optimize
EXPOSE 9000
CMD ["php-fpm"]
```

### Environment Variables (Production)
```env
# App
APP_NAME=ProductSchool
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:xxxx
APP_URL=https://productschool.edu

# Database
DB_HOST=prod-db.xxx.rds.amazonaws.com
DB_DATABASE=productschool_prod
DB_USERNAME=dbadmin
DB_PASSWORD=strong_password

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=prod-redis.xxx.elasticache.amazonaws.com

# Third-party
MIDTRANS_IS_PRODUCTION=true
SENTRY_DSN=https://xxx@sentry.io/xxx
```

---

## 🤝 Contributing

### Development Workflow
1. **Create feature branch:**
   ```bash
   git checkout -b feature/new-feature
   git checkout -b fix/bug-fix
   ```

2. **Make changes and test:**
   ```bash
   php artisan test
   ./vendor/bin/pint  # Format code
   ```

3. **Commit with conventional messages:**
   ```bash
   git commit -m "feat: add new feature"
   git commit -m "fix: resolve bug"
   git commit -m "docs: update readme"
   ```

4. **Push and create PR:**
   ```bash
   git push origin feature/new-feature
   ```

### Code Standards
- **PSR-12:** PHP coding standard
- **Laravel conventions:** Follow framework best practices
- **Type hints:** Use PHP 8.3+ type declarations
- **Comments:** Only for complex logic, not obvious code
- **Tests:** Write tests for all new features

### Pull Request Template
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added
- [ ] Feature tests added
- [ ] Manual testing completed

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Tests pass: `php artisan test`
- [ ] No breaking changes
```

---

## 📞 Support

### Documentation
- **Full API Docs:** `/api-docs`
- **Setup Guide:** `INSTALLATION.md`
- **Verification Report:** `VERIFICATION_STATUS.md`
- **Detailed Audit:** `docs/verification/report-*.md`

### Community & Issues
- **Bug Reports:** https://github.com/andypratama3/ProductsSchool/issues
- **Discussions:** https://github.com/andypratama3/ProductsSchool/discussions
- **Email:** support@productschool.edu

### Emergency Support
- **Database Issue:** Check MySQL connection in `.env`
- **Payment Failure:** Verify Midtrans keys in `.env`
- **WhatsApp Down:** Check Meta API token expiration
- **Performance:** Check Redis connection and queue status

---

## 📄 License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

---

## 🙏 Credits

- **Framework:** [Laravel](https://laravel.com)
- **Authentication:** [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- **Payment:** [Midtrans](https://midtrans.com)
- **Communication:** [Meta WhatsApp API](https://www.whatsapp.com/business/api)
- **Real-time:** [Pusher](https://pusher.com) & [Laravel Reverb](https://laravel.com/docs/reverb)

---

## 📊 Project Status

**Current Version:** 1.0.0  
**Status:** ✅ **PRODUCTION READY**

### Latest Verification (2026-05-12)
- ✅ Environment: PHP 8.3.31, MySQL 9.6, all extensions
- ✅ System: 9/9 modules verified
- ✅ Security: 0 CRITICAL, 3 MEDIUM findings
- ✅ Tests: 544/551 passed (98.7%)
- ✅ Deployment: Ready for production

See [VERIFICATION_STATUS.md](VERIFICATION_STATUS.md) for detailed verification report.

---

**Last Updated:** May 12, 2026  
**Maintained By:** ProductSchool Development Team  
**Repository:** https://github.com/andypratama3/ProductsSchool
