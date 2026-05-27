# ProductSchool — Complete System Flow Documentation

> **Single source of truth** for the entire school management system.
> Generated from live codebase audit — every section verified against actual files.

---

## 1. SYSTEM OVERVIEW

### 1.1 Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| **Framework** | Laravel | ^12.56 |
| **PHP** | PHP | ^8.2 |
| **Database** | MySQL | 8.0+ |
| **Cache / Session / Queue (prod)** | Redis | - |
| **Cache / Session / Queue (dev)** | Database (MySQL) | - |
| **Broadcasting** | Laravel Reverb | ^1.0 |
| **Frontend Build** | Vite + Tailwind CSS | ^6.0 / ^3.1 |
| **Frontend JS** | Alpine.js, jQuery, Bootstrap 5, Select2, DataTables, ApexCharts | - |
| **Auth** | Laravel Sanctum + Spatie Permissions | ^4.0 / ^6.24 |
| **PDF** | DomPDF (barryvdh/laravel-dompdf) | ^3.1 |
| **Spreadsheet** | PhpSpreadsheet | ^2.4 |
| **Payment Gateway** | Midtrans | ^2.6 |
| **WhatsApp API** | Meta Graph API v24.0 | - |
| **AI / Narasi** | Anthropic Claude + DeepSeek API | - |
| **Error Tracking** | Sentry | ^4.25 |
| **E2E Testing** | Playwright | ^1.50 |
| **Static Analysis** | PHPStan | ^1.11 |

### 1.2 Directory Architecture

```
ProductSchool/
├── src/                          # Main Laravel application
│   ├── app/
│   │   ├── Console/Commands/     # 9 artisan commands
│   │   ├── Events/               # 26 broadcasting events
│   │   ├── Exceptions/           # Handler + Sentry integration
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/          # 14 API controllers (Sanctum)
│   │   │   │   ├── Auth/         # 8 auth controllers
│   │   │   │   └── Dashboard/    # 59 dashboard controllers
│   │   │   ├── Middleware/       # 3 custom: CheckUserStatus, SetLocale, LogSensitiveRequests
│   │   │   └── Requests/         # Form request validation classes per domain
│   │   ├── Jobs/                 # 17 queue jobs
│   │   ├── Listeners/            # 12 event listeners
│   │   ├── Mail/                 # 10 mailables
│   │   ├── Models/               # 96 Eloquent models
│   │   ├── Providers/            # 5 providers (App, Auth, Broadcast, Event, Sentry)
│   │   ├── Services/             # 45 domain services
│   │   └── Traits/               # 12 reusable traits
│   ├── bootstrap/app.php         # Laravel 12 middleware config
│   ├── config/                   # 23 config files
│   ├── database/
│   │   ├── migrations/           # 97 migration files
│   │   └── seeders/              # 33 seeders (demo data + permissions)
│   ├── resources/views/          # 200+ blade files across 48 subdirectories
│   ├── routes/
│   │   ├── web.php               # 460 lines, ~50 route groups
│   │   ├── api.php               # 182 lines, Sanctum-protected
│   │   ├── auth.php              # Laravel Breeze auth routes
│   │   ├── channels.php          # 15 broadcast channels
│   │   └── console.php           # Scheduler (monthly billing)
│   └── public/assets/            # Vendors, JS, CSS
├── Dockerfile                    # PHP 8.2 + Node multi-stage
├── docker-compose.yml            # MySQL, Redis, Reverb, Nginx
├── kubernetes/                   # K8s deployment configs
├── nginx/                        # Nginx site config
├── scripts/                      # Setup/deployment scripts
└── .github/                      # GitHub Actions CI/CD
```

### 1.3 Architectural Pattern

```
HTTP Request
    │
    ▼
Middleware Stack (auth, verified, CheckUserStatus, SetLocale, role/permission)
    │
    ▼
Controller (thin — auth + validation + response)
    │
    ▼
Service (fat — all business logic)
    │
    ├──► Model (relationships, scopes, accessors)
    ├──► Event (broadcast to Reverb channels)
    ├──► Job (queue heavy processing)
    └──► Response (JSON / View / Redirect)
```

Key principles:
- **Controllers** handle only HTTP concerns (auth, validation, response format)
- **Services** contain all business logic, no HTTP coupling
- **Traits** provide cross-cutting concerns (Cacheable, Filterable, Searchable, RealtimeSync, Auditable)
- **Events** broadcast real-time updates via Laravel Reverb
- **Jobs** handle heavy/async processing (imports, payroll, bulk generation)

---

## 2. USER FLOWS

### 2.1 Student Lifecycle

```
PUBLIC                        ADMISSION                    ACTIVE STUDENT              GRADUATION
  │                              │                              │                           │
  ▼                              ▼                              ▼                           ▼
Register ──► Submit       Review ──► Approve              Assign to Classroom       Evaluate Grades
Application                 Manage Documents              Track Attendance          Finalize Report Card
   │                              │    │                         │
Pay ──►   (Midtrans)        Reject ─┘  Enroll ──► Create    Transfer / Promote      Generate Rapor
   │                                            User Account      │                      │
Submit                                                                              Distribute via
Documents                                                                           WhatsApp / PDF
```

**Key flows:**
1. **Admission Flow**: `Public registration → Midtrans payment → Admin review → Approve/Reject → Enroll → Create user account`
2. **Student Flow**: `Enroll → Assign classroom → Daily attendance → Grades input → Promotions → Graduation`
3. **Transfer/Retention**: `Transfer school → Update classroom assignment → Track left_at date`
4. **Early Warning**: `AI risk assessment → Generate alerts → Notify teachers → Intervention tracking`

### 2.2 Payment & Finance Flow

```
PAYMENT TITLE SETUP             CHARGE CREATION              PAYMENT PROCESSING
      │                              │                              │
      ▼                              ▼                              ▼
Admin creates title          Admin creates charge          Student makes payment
(code + name)                (amount + student + title)          │
                                                          Midtrans Snap Token
      │                              │                         │
      ▼                              ▼                    User completes
Payment Title List             Charge List                 bank transfer
                                                              │
                                                         Midtrans Callback
                                                              │
                                                     ┌────────┴────────┐
                                                     ▼                 ▼
                                                 Success            Failed
                                                  │                   │
                                             Update status      Retry / Refund
                                             Create payment     Notify student
                                             Send receipt
```

**Midtrans callback flow:**
```
Midtrans POST /midtrans/notification
    │
    ├── Verify signature key (HMAC SHA512)
    ├── Find charge by order_id
    ├── Match transaction_status:
    │   ├── settlement / capture    → Payment succeeded
    │   ├── pending                 → Awaiting payment
    │   ├── deny / expire / cancel  → Payment failed
    │   └── refund / partial_refund → Refund processed
    └── Broadcast payment-updates channel
```

### 2.3 Document Template Flow

```
TEMPLATE EDITOR (Canvas)          INSTANCE GENERATION           OUTPUT
      │                                │                          │
      ▼                                ▼                          ▼
Open designer (Fabric.js)      Select template              Preview PDF
   │                                │                          │
Add elements:                   Choose student(s)            Download PDF
  - Text (with variables)       Fill variable values         Bulk ZIP export
  - Tables                        │                          │
  - Images                     Generate Instance         Distribute via
  - Shapes                       (DomPDF render)          WhatsApp / Email
   │                                │
Save canvas_json ──►          Store template_instances
Store to templates table       Per-student PDF file
```

**Variable resolution flow:**
```
Template contains {{student_name}} {{classroom}} {{academic_year}}
    │
    ▼
VariableResolver Service:
  1. Parse all {{variables}} from template
  2. Map to TemplateField definitions
  3. Resolve from student data + classroom + academic year
  4. Substitute into rendered HTML
  5. Pass to DomPDF for PDF generation
```

### 2.4 Payroll Flow

```
SALARY SETUP                      PAYROLL RUN                  PAYMENT
    │                                 │                          │
    ▼                                 ▼                          ▼
Salary Components              Create PayrollRun              Mark as paid
  - Base Salary                (academic_year + month)            │
  - Allowances                       │                     Generate slip PDF
  - Deductions                 Process Run (queue)          Export ZIP
       │                             │
Employee Config                Calculate each employee:
  - Salary grade                 Gross Salary
  - Effective date               + Allowances
  - Academic year                - Deductions
                                 = Net Salary
                                      │
                                 Generate payroll details
```

### 2.5 Report Card + Narasi AI Flow

```
GRADES INPUT                   RAPOR GENERATION              NARASI AI
    │                                │                          │
    ▼                                ▼                          ▼
Teacher inputs grades       Select classroom + period     Generate with Claude
  (per subject)                    │                      (per student)
       │                   Generate all report cards          │
       ▼                          │                     Teacher reviews / edits
Grade stored in             Bulk finalize                       │
grades table                     │                     Approve narasi_text
                          Generate PDF files                   │
                          (DomPDF template)              Store in student_report_cards
                               │                               │
                          Distribute via                   Finalize rapor
                          WhatsApp / Email
```

**Narasi AI API call:**
```
NarasiGeneratorService:
  1. Load student grades + P5 assessment scores
  2. Build prompt from NarasiPromptTemplate
  3. Call Anthropic Claude API (or DeepSeek fallback)
  4. Parse AI response for narrative text
  5. Store in student_report_cards.narasi_text
  6. Broadcast NarasiGenerated event
```

### 2.6 Rapor Distribution Flow

```
                        ┌──────────────────┐
                        │  Report Card      │
                        │  Finalized        │
                        └────────┬─────────┘
                                 │
                                 ▼
                        ┌──────────────────┐
                        │  Queue Job:       │
                        │  SendRaporJob     │
                        └────────┬─────────┘
                                 │
                    ┌────────────┴────────────┐
                    ▼                         ▼
            ┌──────────────┐         ┌──────────────┐
            │  WhatsApp     │         │  Email        │
            │  (primary)    │         │  (fallback)   │
            └──────┬───────┘         └──────┬───────┘
                   │                        │
                   ▼                        ▼
       ┌──────────────────┐      ┌──────────────────┐
       │  Meta WhatsApp    │      │  SMTP Mail        │
       │  Graph API        │      │  (Mailtrap/       │
       │                   │      │   SendGrid)       │
       └──────────────────┘      └──────────────────┘
```

### 2.7 WhatsApp Chat Flow

```
PARENT                         WHATSAPP API                  ADMIN
  │                                │                          │
  │── Send message ──►     Meta Webhook POST              ──►│
  │                       /v1/webhook/whatsapp               │  Reply in dashboard
  │                            │                              │
  │                       Verify signature                    │  Assign conversation
  │                       Parse message content               │  Use templates
  │                       Route to conversation               │
  │                            │                              │
  │◄── Auto-reply or ────  Store in DB ────────────◄── Admin │
  │    bot response       whatsapp_messages        response   │
  │                       Broadcast via                         │
  │                       whatsapp-conversation.{id} channel   │
```

### 2.8 Real-time Dashboard Updates Flow

```
┌───────────┐     ┌────────────┐     ┌────────────┐     ┌───────────┐
│  Laravel   │────►│  Reverb    │────►│  Laravel    │────►│  Browser   │
│  Event     │     │  Server    │     │  Echo JS    │     │  Dashboard │
└───────────┘     └────────────┘     └────────────┘     └───────────┘
      │                 │                  │                  │
      │  Broadcast      │  WebSocket       │  Subscribe       │  Update UI
      │  to channel     │  (port 8080)     │  to channel      │  (ApexCharts,
      │                 │                  │                  │   DataTables)
Events broadcasted:
  - AttendanceMarked          → channel: attendance-updates
  - GradePosted               → channel: grade-updates
  - PaymentCompleted          → channel: payment-updates
  - ScheduleUpdated           → channel: schedule-updates
  - LeaveApproved             → channel: leave-updates
  - DataUpdated               → channel: data-updates
  - NarasiGenerated           → channel: student-updates
  - WhatsAppMessageSent       → channel: whatsapp-conversation.{id}
  - StudentRiskAssessed       → channel: admin-notifications
```

---

## 3. DATABASE SCHEMA (86 Core Tables)

### 3.1 Core / Auth (6 tables)

| Table | Key Columns | Relationships |
|-------|------------|---------------|
| `users` | id(uuid), name, email, password, slug, is_active, 2FA columns | HasMany notifications, HasMany audit_logs |
| `sessions` | id, user_id(FK), ip_address, payload, last_activity | BelongsTo User |
| `permissions` | id(uuid), name, guard_name | Many-to-many roles/users via pivot |
| `roles` | id(uuid), name, guard_name | Many-to-many permissions/users |
| `model_has_permissions` | permission_id, model_type, model_id | Pivot |
| `model_has_roles` | role_id, model_type, model_id | Pivot |
| `role_has_permissions` | permission_id, role_id | Pivot |
| `personal_access_tokens` | id, tokenable_type/ID, token, abilities, expires_at | Sanctum tokens |

### 3.2 Academic (12 tables)

| Table | Key Columns | Relationships |
|-------|------------|---------------|
| `academic_years` | id(uuid), name(unique), start_date, end_date, is_active | HasMany classrooms, HasMany grades |
| `classrooms` | id(uuid), name, code, academic_year_id(FK), classroom_type, slug | BelongsTo AcademicYear, BelongsToMany Students/Subjects/Teachers |
| `subjects` | id(uuid), name, slug | BelongsToMany Classrooms/Teachers |
| `classroom_subjects` | classroom_id, subject_id | Pivot |
| `teacher_subjects` | teacher_id, subject_id | Pivot |
| `teacher_classrooms` | teacher_id, classroom_id | Pivot |
| `grades` | id(uuid), student_id(FK), classroom_id(FK), subject_id(FK), academic_year_id(FK), semester, score | Unique: (student, classroom, subject, semester) |
| `grade_components` | id(uuid), name, classroom_id(FK), subject_id(FK) | HasMany GradeComponentWeight |
| `grade_component_weights` | id(uuid), grade_component_id(FK), classroom_id(FK), weight | - |
| `schedules` | id(uuid), academic_year, classroom_id(FK), classroom_type, slug, status | HasMany ScheduleDetails |
| `schedule_details` | id(uuid), schedule_id(FK), day, time_start, time_end, subject_id(FK), teacher_id(FK nullable), color | BelongsTo Schedule/Subject/Teacher |
| `report_templates` | id(uuid), name, slug, category, language, curriculum_type, version, is_active, template_content, placeholders, config, blade_template | BelongsTo User (created_by) |

### 3.3 Student Lifecycle (13 tables)

| Table | Key Columns |
|-------|-------------|
| `students` | 50+ columns: name, nisn(unique), gender, birth, religion, address, parents, guardian, status, spp, dpp, va_number, photo, slug |
| `student_classrooms` | student_id(FK), classroom_id(FK), classroom_type, academic_year_id(FK), status(enum), enrolled_at, left_at — Unique: (student_id, classroom_id) |
| `admissions` | Full applicant data: name, birth, gender, religion, address, parent data, documents (diploma, birth cert, family card, photo), queue_number, payment_status, order_id, status |
| `admission_decisions` | admission_id(FK), decided_by(FK), status(enum), reason, notes |
| `admission_documents` | admission_id(FK), document_type, file_path, mime_type, file_size, status, verification_notes |
| `student_promotions` | Tracks class/level promotions |
| `student_transfers` | Tracks school transfers |
| `student_graduations` | Tracks graduation records |
| `student_retentions` | Tracks class retention |
| `student_risk_assessments` | student_id(FK), risk_level, risk_score, assessment_date, assessed_by(FK) |
| `student_risk_alerts` | risk_assessment_id(FK), alert_type, severity, description, is_dismissed |
| `student_p5_assessments` | student_id(FK), classroom_id(FK), academic_year, assessment_period, assessor_user_id(FK), assessed_at, notes |
| `student_p5_scores` | student_p5_assessment_id(FK), p5_dimension_id(FK), score(1-4), evidence |

### 3.4 HR & Payroll (17 tables)

| Table | Key Columns |
|-------|-------------|
| `employees` | name, nip, nik, sex, phone, staff_position_id(FK), user_id(FK), base_salary, work_shift_id(FK), salary_grade_id(FK), education_level, dependent_count, status |
| `teachers` | name, employee_id(FK), staff_position_id(FK), photo, slug, status |
| `staff_positions` | name, slug, parent_position_id(FK self-ref) |
| `work_shifts` | shift_name, employee_type(enum), check_in/out times, day, is_default |
| `attendance_locations` | location_name, latitude, longitude, radius, address, status |
| `employee_attendances` | employee_id(FK), attendance_location_id(FK), work_shift_id(FK), date, status(enum), check_in/out times + lat/lng + distance + status, photo_in/out, device_id — Unique: (employee_id, date) |
| `attendance_devices` | employee_id(FK), device_fingerprint(unique), device_name, ip_address, is_active |
| `leave_requests` | employee_id(FK), type(enum), start_date, end_date, total_days, reason, attachment, status(enum), approved_by(FK) |
| `payroll_runs` | academic_year, month, start_date, end_date, total_amount, status(enum), processed_by(FK) — Unique: (academic_year, month) |
| `employee_payrolls` | payroll_run_id(FK), employee_id(FK), gross_salary, allowances, deductions, net_salary, payment_method, paid_at — Unique: (payroll_run_id, employee_id) |
| `salary_components` | name, slug, component_type(enum: base/allowance/deduction), amount, calculation_type(enum: fixed/percentage) |
| `salary_grades` | Grade levels for salary calculation |
| `payroll_salary_rates` | Rate definitions per grade/position |
| `employee_salary_configurations` | employee_id(FK), academic_year, salary_components(json), effective_date, is_active |
| `education_allowances` | Education benefit configurations |
| `functional_allowances` | Functional position allowances |
| `structural_allowances` | Structural position allowances |

### 3.5 Finance (7 tables)

| Table | Key Columns |
|-------|-------------|
| `payment_titles` | name, code(unique), slug |
| `payments` | order_id, student_id(FK), classroom_id(FK), email, gross_amount, payment_type, session_id, payment_url, transaction_id, va_number, payment_title_id(FK), status, paid_at |
| `charges` | order_id(unique), student_id(FK), gross_amount, payment_type, bank, va_number, transaction_id, payment_title_id(FK), transaction_status, snap_token, payment_id(FK) |
| `charges_archive` | Same as charges (archived records) |
| `student_fees` | student_id(FK), payment_title_id(FK), amount, due_date, status(enum), academic_year |
| `subscriptions` | school_slug(unique), school_name, plan(enum), status(enum), max_students, max_users |
| `school_settings` | subscription_id(FK nullable), key(unique per subscription), value |

### 3.6 Document Templates (6 tables)

| Table | Key Columns |
|-------|-------------|
| `templates` | name, category_id(FK), canvas_json(json), variables(json), config(json), thumbnail |
| `template_fields` | template_id(FK), name, label, type, options, required, default_value |
| `template_categories` | name, slug |
| `template_instances` | template_id(FK), student_id(FK nullable), user_id(FK), data_json(json), status, file_path, verification_code(unique) |
| `documents` | template_id(FK), student_id(FK), created_by(FK), data_json(json), status, file_path, bulk_batch_id, verification_code(unique) |
| `document_templates` | category_id(FK), classroom_id(FK nullable), canvas_json(json), html_template, generate_mode(enum) |

### 3.7 Content / Website (12 tables)

| Table | Purpose |
|-------|---------|
| `articles` | Blog/news with status (draft/published), author, categories (MTM) |
| `categories` | Article categories |
| `achievements` | School achievements |
| `galleries` | Photo galleries |
| `heroes` | Hero section slides |
| `cooperations` | Partnership/collaboration records |
| `comments` | Article comments with guest support |
| `education_staff` | School staff profiles |
| `facilities` | School facility descriptions |
| `visitors` | Website visitor tracking |
| `ratings` | User ratings |
| `feedback` | User feedback/messages |

### 3.8 WhatsApp (6 tables)

| Table | Key Columns |
|-------|-------------|
| `whatsapp_conversations` | phone_number(unique), profile_name, student_id(FK), assigned_admin_id(FK), status(enum), message_count, last_message_at |
| `whatsapp_messages` | conversation_id(FK), reply_to_message_id(FK self), sender_id(FK), sender_type(enum), message_type(enum), content, status(enum), whatsapp_message_id(unique) |
| `whatsapp_message_templates` | name(unique), category, template_text, is_active |
| `whatsapp_incoming_messages` | message_id(unique), phone, type, content(json), status |
| `whatsapp_message_statuses` | message_id, status, recipient, errors(json) |
| `whatsapp_sessions` | phone(unique), state, nisn, student_id(FK), expires_at |

### 3.9 System (9 tables)

| Table | Purpose |
|-------|---------|
| `audit_logs` | All model changes with old/new values, user, IP |
| `notifications` | Database notifications with read_at tracking |
| `notification_preferences` | Per-user notification settings (email, SMS, push, WA, frequency) |
| `tasks` | Project management: title, status, priority, category, assigned_to, progress, dependencies |
| `task_comments` | Task comment threads |
| `task_dependencies` | Task dependency graph |
| `task_timesheets` | Time tracking for tasks |
| `error_log` | Application error logging |
| `feedback` | User feedback/contact messages |

---

## 4. COMPLETE ROUTE MAP

### 4.1 Web Routes (`/dashboard` prefix)

All under middleware: `auth, verified, CheckUserStatus`

| Group | Prefix | Routes | Key Features |
|-------|--------|--------|-------------|
| Dashboard | `/` | index, stats, search | KPIs, charts, real-time stats |
| Settings | `/settings` | users, roles, permissions, konfigurasi, notification-preferences, staff-positions | Full RBAC management |
| Posts | `/posts` | articles, categories, achievements, galleries, heroes, cooperations | CRUD with status/photo management |
| Students | `/students` | CRUD + import + assign classroom + status | Batch import via Excel |
| Admissions | `/admissions` | CRUD + review/approve/reject/enroll + Midtrans payment | Full PPDB workflow |
| Attendance | `/attendances` | CRUD + daily + monthly report | Per-classroom filtering |
| Grades | `/grades` | CRUD + bulk import + components + weights + transcript | Component-based grading |
| Analytics | `/analytics` | progress, class-comparison, export | ApexCharts visualizations |
| Templates | `/templates` | CRUD + canvas editor + preview + export-pdf | Fabric.js editor |
| Template Categories | `/template-categories` | CRUD | - |
| Template Instances | `/template-instances` | CRUD + preview + PDF + submit/approve/reject + bulk generate | Bulk document generation |
| Payments | `/payments` | CRUD + outstanding + mark-paid + Midtrans integration | Payment gateway |
| Payment Titles | `/payment-titles` | CRUD | - |
| Leave Requests | `/leave-requests` | CRUD + approve/reject | Employee leave |
| Schedules | `/schedules` | CRUD + timetable + detail CRUD | Class schedules |
| Notifications | `/notifications` | list, read, read-all, destroy | Real-time notifications |
| Audit Log | `/audit-log` | index, datatable, show | Full system audit trail |
| Teachers | `/teachers` | CRUD + assign subjects/classrooms + status toggle | - |
| Employees | `/employees` | CRUD + import + salary config + status | Full HR management |
| Subjects | `/subjects` | CRUD | - |
| Promotions | `/promotions` | index, create, store, show, undo | Student class promotions |
| Classrooms | `/classrooms` | CRUD + manage students (add/remove) | - |
| Academic Years | `/academic-years` | CRUD | - |
| Academic Calendar | `/academic-calendar` | events CRUD + publish + export ICS | Calendar management |
| Employee Attendance | `/employee-attendances` | check-in/out + daily/monthly + map + export | GPS-based attendance |
| KML | `/kml` | upload, template download | Attendance boundary files |
| Payroll | `/payrolls` | runs CRUD + process + reports + slips + ZIP export | Complete payroll system |
| Payroll Salary Rates | `/payroll-salary-rates` | CRUD | - |
| Salary Grades | `/salary-grades` | CRUD | - |
| Allowances | 3 groups | education, structural, functional allowances CRUD | - |
| Midtrans | `/midtrans` | snap token, status, refund, cancel, payment methods | Payment gateway |
| Report Cards | `/report-cards` | CRUD + generate + bulk-finalize + download + preview + export | - |
| P5 Assessment | `/p5-assessment` | CRUD + bulk create + report | Pancasila profile assessment |
| Early Warning | `/early-warning` | assess + bulk-assess + show + dismiss | AI risk detection |
| Rapor Distribution | `/rapor-distribution` | CRUD + bulk distribute + track + resend + download PDF | - |
| Bulk Operations | `/bulk-operations` | delete, import, export, template download | - |
| Tasks | `/tasks` | CRUD + my-tasks + overdue + export + comments + progress + stats | Project management |
| Timesheets | `/timesheets` | start, stop, active, recent, index | Time tracking |
| WhatsApp Chat | `/whatsapp-chat` | conversations, messages, templates, reactions, read status | - |
| Narasi AI | `/narasi` | generate, bulk-generate, approve, regenerate | Claude/DeepSeek AI |
| Cache | `/cache` | flush (superadmin/admin) | - |
| Profile | `/profile` | edit, update, destroy | User profile |

### 4.2 API Routes (`/api/` prefix)

All under `auth:sanctum` middleware (except webhooks).

| Method | Endpoint | Purpose |
|--------|----------|---------|
| Public | `GET v1/webhook/whatsapp` | Meta webhook verification (hub.challenge) |
| Public | `POST v1/webhook/whatsapp` | Meta webhook message handler |
| Public | `POST sanctum/token` | Create API token (email + password) |
| Auth | `GET /user` | Current authenticated user |
| Auth | `GET/DELETE /tokens` | List/revoke API tokens |
| Auth | `GET/POST/PUT/DELETE /classrooms` | Classroom CRUD |
| Auth | `GET/POST/PUT/DELETE /teachers` | Teacher CRUD |
| Auth | `GET/POST/PUT/DELETE /employees` | Employee CRUD |
| Auth | `GET/POST/PUT/DELETE /students` | Student CRUD (admin/teacher) |
| Auth | `GET/POST/PUT/DELETE /grades` | Grade CRUD + transcript (admin/teacher) |
| Auth | `GET /student-attendances` | Student attendance list |
| Auth | `GET /employee-attendances` | Employee attendance list (admin/hr) |
| Auth | `GET/POST/PUT/DELETE /payments` | Payment CRUD (admin/finance) |
| Auth | `GET /leave-requests` | Leave request list (admin/hr) |
| Auth | `GET /schedules` | Schedule list + details |
| Auth | `GET/POST/PUT/DELETE /tasks` | Task CRUD + comments + progress |
| Auth | `GET /analytics/*` | Dashboard summary, charts, subject performance, attendance trend, peer benchmark, class comparison |
| Auth | `POST /cache/*` | Clear cache, get stats, flush module |

### 4.3 Auth Routes (Breeze)

Standard Laravel Breeze authentication routes with email verification and password confirmation.

### 4.4 Console Commands

| Command | Schedule | Purpose |
|---------|----------|---------|
| `school:generate-monthly-bills` | Monthly (1st 00:00) | Auto-generate monthly fees |
| `school:health-check` | - | System health verification |
| `import:students` | - | CSV/Excel student import |
| `sentry:test` | - | Test Sentry integration |
| `monitor:queue` | - | Queue health monitoring |
| `whatsapp:setup` | - | WhatsApp bot setup |
| `kml:manage` | - | KML attendance file management |
| `list:routes` | - | List all registered routes |

---

## 5. SERVICE LAYER (45 Services)

| Service | Domain | Key Methods |
|---------|--------|-------------|
| `StudentService` | Students | create, update, assignClassroom, transfer, promote, graduate |
| `AdmissionService` | Admissions | submit, review, approve, reject, enroll, processPayment |
| `GradeService` | Grades | calculateFinal, getTranscript, bulkImport, getAverageBySubject |
| `AttendanceService` | Student Attendance | markAttendance, getDailyReport, getMonthlyReport |
| `EmployeeAttendanceService` | Employee Attendance | checkIn, checkOut, getReport, calculateOvertime |
| `ScheduleService` | Schedules | create, addDetail, getWeeklyTimetable, getTeacherScheduleToday |
| `PayrollService` | Payroll | createRun, processRun, calculateSalary, markPaid, generateSlip |
| `PaymentService` | Payments | createCharge, processMidtransCallback, generateSnapToken, refund |
| `MidtransService` | Midtrans | getSnapToken, checkStatus, refund, cancel, getPaymentMethods |
| `P5AssessmentService` | P5 Assessment | createAssessment, scoreStudent, calculateP5Profile |
| `NarasiGeneratorService` | AI Narasi | generate, bulkGenerate, regenerate, buildPrompt, callAI |
| `TemplateService` | Templates | create, updateCanvas, renderPreview, exportPdf |
| `TemplateGeneratorService` | Bulk Generation | generateBulk, generateForStudent, getProgress |
| `RaporDistributionService` | Rapor | distribute, trackStatus, resend, getStats |
| `WhatsAppChatService` | WhatsApp | sendMessage, processIncoming, assignAdmin, getConversations |
| `WhatsAppBotService` | WhatsApp Bot | processMessage, routeToIntent, handleCommand |
| `WhatsappMetaService` | Meta API | sendTemplate, sendMessage, verifyWebhook, handleCallback |
| `SalaryCalculationService` | Payroll | calculateGross, calculateAllowances, calculateDeductions, calculateNet |
| `EarlyWarningService` | Student Risk | assessStudent, bulkAssess, getActiveAlerts, dismissAlert |
| `NotificationService` | Notifications | send, broadcast, markAsRead, getUnreadCount |
| `CacheService` | Cache | flushAll, getModuleStats, flushModule, getHitRate |
| `ExportService` | Export | exportToExcel, exportToCsv, getExportProgress |
| `PdfService` | PDF | generate, merge, getDownloadUrl |
| `StudentAnalyticsService` | Analytics | getDashboardSummary, getSubjectPerformance, getAttendanceTrend, peerBenchmark |
| `PromotionService` | Promotions | promoteStudents, undoPromotion, getPromotionHistory |
| `TaskService` | Tasks | create, updateProgress, addComment, getDependencies |
| `LeaveRequestService` | Leave | create, approve, reject, checkQuota, getBalance |
| `FileUploadService` | Files | upload, validate, getStoragePath |
| `VariableResolver` | Templates | resolve, parseVariables, getAvailableVariables |

---

## 6. BROADCASTING & REAL-TIME

### 6.1 Events (26 total)

| Event | Channel | Triggered By |
|-------|---------|-------------|
| `AttendanceMarked` | attendance-updates | Student attendance created |
| `GradePosted` | grade-updates | Grade created/updated |
| `PaymentCompleted` | payment-updates | Payment confirmed  
| `ScheduleUpdated` | schedule-updates | Schedule CRUD |
| `LeaveApproved` | leave-updates | Leave approved/rejected |
| `DataUpdated` | data-updates | Generic model changes |
| `NarasiGenerated` | student-updates | AI narasi generation complete |
| `StudentCreated` | student-updates | New student registered |
| `StudentImportProgress` | import.students.{userId} | Batch import progress |
| `WhatsAppMessageSent` | whatsapp-conversation.{id} | New chat message |
| `StudentRiskAssessed` | admin-notifications | Risk assessment complete |
| `PayrollProcessed` | admin-notifications | Payroll run processed |
| `RaporDistributionSent/Failed` | admin-notifications | Rapor distribution status |

### 6.2 Reverb Configuration

- **Server**: Port 8080, host 127.0.0.1
- **App**: ID 167378, auto-generated credentials
- **Scaling**: Redis backend for multi-server
- **Client**: Laravel Echo + Pusher JS protocol
- **Dev command**: `php artisan reverb:start`

---

## 7. QUEUE ARCHITECTURE

### 7.1 Jobs (17 total)

| Job | Queue | Purpose |
|-----|-------|---------|
| `ProcessBulkImportJob` | default | Batch student/excel import |
| `ProcessBulkTemplateGenerationJob` | default | Bulk document generation |
| `BulkAssessClassroomJob` | default | Batch risk assessment |
| `AssessStudentRiskJob` | default | Single student risk score |
| `GenerateAnalyticsSnapshotJob` | default | Analytics cache refresh |
| `ProcessPayrollRunJob` | default | Full payroll calculation |
| `GenerateBulkDocumentsJob` | default | Batch PDF generation |
| `SendRaporDistributionJob` | default | Rapor WhatsApp delivery |
| `SendWhatsAppNotificationJob` | default | WhatsApp message send |
| `SendNotificationEmailJob` | default | Email notification send |
| `SendSmsNotificationJob` | default | SMS notification send |
| `GenerateDailyReportsJob` | default | Daily report auto-generation |
| `ProcessWhatsAppMessage` | default | Incoming WA processing |
| `GenerateNarasiJob` | default | AI narrative text generation |
| `GenerateExportJob` | default | Data export generation |
| `SyncAttendanceJob` | default | Attendance device sync |
| `TrackDistributionStatusJob` | default | Distribution tracking |

### 7.2 Queue Configuration

- **Dev driver**: `database` (jobs table)
- **Prod driver**: `redis` (recommended)
- **Failed jobs**: `failed_jobs` table (UUID tracking)
- **Job batching**: `job_batches` table
- **Worker**: `php artisan queue:work --tries=3`
- **Dev command**: `php artisan queue:listen --tries=1`

---

## 8. SECURITY MODEL

### 8.1 Authentication Layers

```
Request
    │
    ├── Web:    session-based (auth middleware)
    ├── API:    token-based (Sanctum middleware)
    └── Public: guest routes (register, login, webhooks)
```

### 8.2 Authorization (RBAC)

- **Package**: Spatie Laravel Permission v6
- **Gates**: Defined in `AuthServiceProvider` per model (view, create, update, delete)
- **Permission resource names**: `schedules`, `students`, `teachers`, `users`, `settings`, `payments`, `grades`, `attendance`, `employees`, `p5_assessments`, `report_cards`, `templates`, `bulk_operations`, `notifications`, `promotions`, `early_warning`
- **Controller enforcement**: `$this->authorize('view', Model::class)` / `$this->authorize('create', ...)`
- **View enforcement**: `@can('update', $model)` in Blade templates
- **Middleware**: `role:admin`, `permission:view-students`, `role_or_permission:manage settings`

### 8.3 Middleware Stack

| Middleware | Scope | Purpose |
|-----------|-------|---------|
| `auth` | Web | Authenticated sessions |
| `verified` | Web | Email verified users only |
| `CheckUserStatus` | All dashboard | Block inactive/suspended users |
| `SetLocale` | Web | Language switching (id/en) |
| `role` | Various | Spatie role check |
| `permission` | Various | Spatie permission check |
| `role_or_permission` | Settings | Role OR permission |
| `auth:sanctum` | API | API token authentication |
| `throttle:api-general` | API | Rate limiting |
| `sentry` | All | Error tracking context |

---

## 9. DEVELOPMENT GUIDE

### 9.1 Setup (Development)

```bash
git clone <repo>
cd src
cp .env.example .env   # Configure DB credentials
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run dev             # Runs: serve + queue + logs + vite
```

### 9.2 Code Conventions

- **PSR-12** via Laravel Pint (`composer pint`)
- **Static analysis**: PHPStan (`vendor/bin/phpstan analyse`)
- **Tests**: PHPUnit (`php artisan test`) + Playwright E2E (`npm run test:e2e`)
- **Controllers**: singular, thin — auth + validation + response only
- **Services**: all business logic, injected via constructor
- **Models**: relationships, scopes, accessors — NO business logic
- **Traits**: reusable cross-cutting concerns (Cacheable, Filterable, Searchable, RealtimeSync)
- **Migrations**: timestamps prefixed, OneTable-per-file
- **Blade**: `x-component` syntax, `@section('page-header')` + `@section('content')` + `@section('modal')`

### 9.3 Blade Component Library

| Component | Usage |
|-----------|-------|
| `x-page-header` | Page title + breadcrumbs + action buttons |
| `x-card` | Card wrapper with header/body/footer slots |
| `x-data-table` | Server-side DataTable with filters, bulk actions, realtime sync |
| `x-form.input` | Label + input + error |
| `x-form.select` | Label + select + error |
| `x-form.file` | File upload with preview |
| `x-form.textarea` | Label + textarea + error |
| `x-form-actions` | Cancel + Submit buttons |
| `x-button` | Styled button/link |
| `x-modal` | Bootstrap modal wrapper |
| `x-filter` | Filter form with select2 inputs |
| `x-bulk-actions` | Bulk action toolbar |
| `x-swal-flash` | SweetAlert2 flash messages |

### 9.4 Testing

```bash
# PHPUnit (unit + feature)
php artisan test
vendor/bin/phpunit --coverage-html coverage/

# Playwright E2E
npm run test:e2e           # Headless
npm run test:e2e:ui        # With UI
npm run test:e2e:headed    # Visible browser

# Code quality
composer pint              # Auto-fix PSR-12
vendor/bin/phpstan analyse # Static analysis
```

---

## 10. DEPLOYMENT

### 10.1 Production Build

```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 10.2 Queue Worker (Supervisor)

```ini
[program:school-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/src/artisan queue:work --tries=3 --sleep=3
directory=/var/www/src
autostart=true
autorestart=true
numprocs=2
```

### 10.3 Reverb (WebSocket)

```bash
# Dev
php artisan reverb:start

# Production (Supervisor)
[program:school-reverb]
command=php /var/www/src/artisan reverb:start
user=www-data
autostart=true
autorestart=true
```

### 10.4 Cron (Scheduler)

```cron
* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1
```

### 10.5 Docker Deployment

```bash
docker-compose up -d
# Services: app (PHP-FPM), nginx, mysql, redis, reverb, queue, scheduler
```

### 10.6 Kubernetes

Configs in `kubernetes/` directory include deployments, services, configmaps, and ingress for production-grade orchestration.

### 10.7 Monitoring

- **Sentry**: Error tracking and performance monitoring
- **Health check**: `php artisan school:health-check`
- **Queue monitor**: `php artisan monitor:queue`
- **Logs**: Laravel Pail (`php artisan pail`)

---

## 11. FILE INDEX

### PHP Files
- **Models**: 96 — `app/Models/`
- **Controllers**: 83 — `app/Http/Controllers/{Auth,Dashboard,Api}/`
- **Services**: 45 — `app/Services/`
- **Traits**: 12 — `app/Traits/`
- **Jobs**: 17 — `app/Jobs/`
- **Events**: 26 — `app/Events/`
- **Listeners**: 12 — `app/Listeners/`
- **Mailables**: 10 — `app/Mail/`
- **Middleware**: 3 — `app/Http/Middleware/`
- **Console Commands**: 9 — `app/Console/Commands/`
- **Migrations**: 97 — `database/migrations/`
- **Seeders**: 33 — `database/seeders/`
- **Config**: 23 — `config/`

### Front-End Files
- **Blade views**: 200+ — `resources/views/`
- **Blade components**: 31 — `resources/views/components/`
- **Layouts**: 4 — `resources/views/layouts/`
- **Email templates**: 7 — `resources/views/emails/`

### Routes
- **Web**: `routes/web.php` (460 lines, ~50 groups)
- **API**: `routes/api.php` (182 lines)
- **Auth**: `routes/auth.php` (59 lines)
- **Channels**: `routes/channels.php` (135 lines, 15 channels)
- **Console**: `routes/console.php`

### Infrastructure
- `Dockerfile` — Multi-stage PHP 8.2 build
- `docker-compose.yml` — Full stack with 11 services
- `kubernetes/` — K8s production configs
- `.github/` — GitHub Actions CI/CD
- `nginx/` — Nginx site configuration
