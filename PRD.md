# Product Requirements Document
**Produk**  : ProductSchool ERP — Sistem Manajemen Sekolah
**Versi**   : 1.0
**Tanggal** : 28 Mei 2026
**Status**  : Draft

---

## 1. Ringkasan Produk

ProductSchool ERP adalah sistem manajemen sekolah berbasis web yang mencakup manajemen siswa, pembayaran SPP online via Midtrans, komunikasi orang tua via WhatsApp API Meta, manajemen kelas & tahun ajaran, serta sistem peran & izin berbasis RBAC (Spatie Permission). Produk ini menyelesaikan masalah pencatatan manual pembayaran sekolah, kurangnya transparasi tagihan bagi orang tua, dan komunikasi satu-arah antara sekolah dengan wali murid. Sistem dibangun dengan Laravel 12 + React 19 + Tailwind CSS dan menggunakan Redis untuk antrian, cache, serta sesi.

---

## 2. Tujuan & Sasaran

- Menyediakan dashboard manajemen sekolah yang menampilkan statistik real-time (total siswa, pembayaran, tunggakan)
- Mengotomatiskan pembuatan tagihan SPP bulanan dan pemrosesan pembayaran via Midtrans
- Menyediakan saluran komunikasi dua arah antara sekolah dan orang tua melalui WhatsApp
- Memberikan sistem kontrol akses berbasis peran (RBAC) yang fleksibel
- Menyediakan audit trail untuk semua perubahan data penting

### KPI
| Metrik | Target |
|--------|--------|
| Waktu pembuatan tagihan bulanan | < 5 menit (otomatis via scheduler) |
| Persentase pembayaran online | > 80% dari total transaksi |
| Waktu respons chat WhatsApp | < 5 menit (jam kerja) |
| Akurasi pencatatan audit log | 100% untuk operasi CRUD |

---

## 3. Pengguna (User Personas)

### 3.1 Admin Sekolah
- **Siapa** : Staf tata usaha / operator sekolah yang mengelola data siswa, pembayaran, dan pengaturan sistem
- **Goal** : Mengelola data siswa, memantau pembayaran, mengatur tahun ajaran & kelas
- **Pain Point saat ini** : Data siswa tersebar di banyak file Excel, pembayaran dicatat manual, sulit melacak tunggakan

### 3.2 Finance / Bendahara
- **Siapa** : Bendahara sekolah yang bertanggung jawab atas penerimaan pembayaran
- **Goal** : Memantuk status pembayaran siswa, mengekspor laporan, memproses refund jika perlu
- **Pain Point saat ini** : Rekonsiliasi pembayaran manual, tidak ada notifikasi otomatis untuk tagihan jatuh tempo

### 3.3 Orang Tua / Wali Murid
- **Siapa** : Orang tua siswa yang menerima tagihan dan melakukan pembayaran
- **Goal** : Menerima tagihan via WhatsApp, membayar SPP online, mendapatkan konfirmasi pembayaran
- **Pain Point saat ini** : Tidak tahu jadwal pembayaran, harus datang ke sekolah untuk bayar

### 3.4 Guru / Staf Pengajar
- **Siapa** : Guru yang mengelola kelas dan nilai siswa
- **Goal** : Melihat daftar siswa di kelas, mengelola kehadiran dan nilai
- **Pain Point saat ini** : Rekap nilai manual di kertas

### 3.5 Super Admin
- **Siapa** : Developer / pengelola sistem yang mengatur pengguna, peran, dan konfigurasi teknis
- **Goal** : Mengelola role & permission, melihat audit log, membersihkan cache
- **Pain Point saat ini** : Tidak ada audit trail untuk perubahan data sensitif

---

## 4. Scope Fitur

### 4.1 Fitur yang Sudah Ada (Implemented)

#### F-01: Dashboard & Statistik
- **Deskripsi** : Halaman utama admin dengan grafik statistik real-time (jumlah siswa, total pembayaran, tunggakan, grafik keuangan)
- **User Story** : Sebagai admin, saya ingin melihat ringkasan data sekolah di satu halaman, agar bisa memantau kondisi sekolah dengan cepat
- **Acceptance Criteria** :
  - [ ] AC-1: Menampilkan total siswa aktif, total kelas, total pembayaran bulan ini
  - [ ] AC-2: Menampilkan grafik tren pembayaran 6 bulan terakhir
  - [ ] AC-3: Data statistik bisa di-refresh via AJAX tanpa reload halaman
- **Route terkait** : `GET /dashboard` → `DashboardController@index`, `GET /dashboard/stats` → `DashboardController@getStats`
- **Status** : ✅ Done

#### F-02: Manajemen Siswa
- **Deskripsi** : CRUD data siswa dengan upload foto, filter, pencarian, dan DataTables AJAX. Termasuk data demografi lengkap (alamat, orang tua, dll)
- **User Story** : Sebagai admin, saya ingin menambah/mengedit data siswa, agar data siswa selalu terkini
- **Acceptance Criteria** :
  - [ ] AC-1: Form create/edit dengan validasi NISN unik
  - [ ] AC-2: Upload foto siswa (max 2MB)
  - [ ] AC-3: Status siswa (active/inactive/graduated/dropped) bisa diubah
  - [ ] AC-4: Pencarian siswa berdasarkan nama/NISN
- **Route terkait** : `GET/POST /dashboard/students` → `StudentController`, `POST /dashboard/students/{id}/status`
- **Status** : ✅ Done

#### F-03: Import Siswa dari DAPODIK (Excel)
- **Deskripsi** : Import massal data siswa dari file Excel format DAPODIK dengan progress bar real-time via job queue
- **User Story** : Sebagai admin, saya ingin import banyak siswa sekaligus dari Excel, agar tidak perlu input satu per satu
- **Acceptance Criteria** :
  - [ ] AC-1: Upload file .xlsx dengan format DAPODIK
  - [ ] AC-2: Validasi data sebelum import
  - [ ] AC-3: Progress bar real-time via broadcasting
  - [ ] AC-4: Download template Excel
  - [ ] AC-5: Cancel import jika terjadi error
- **Route terkait** : `POST /dashboard/students/import` → `StudentImportController`, `GET /dashboard/students/import/progress/{batchId}`
- **Status** : ✅ Done

#### F-04: Manajemen Kelas
- **Deskripsi** : CRUD kelas dengan penempatan siswa, assignment guru & mata pelajaran, filter tahun ajaran
- **User Story** : Sebagai admin, saya ingin mengatur kelas dan menempatkan siswa, agar struktur kelas tertata rapi per tahun ajaran
- **Acceptance Criteria** :
  - [ ] AC-1: Tambah/edit kelas dengan tipe (reguler, dll)
  - [ ] AC-2: Tambah/hapus siswa dari kelas secara bulk
  - [ ] AC-3: Lihat daftar siswa dalam satu kelas
  - [ ] AC-4: Filter kelas berdasarkan tahun ajaran aktif
- **Route terkait** : `GET/POST /dashboard/classrooms` → `ClassroomController`, `POST /dashboard/classrooms/{id}/add-student`
- **Status** : ✅ Done

#### F-05: Manajemen Tahun Ajaran
- **Deskripsi** : CRUD tahun ajaran, menandai tahun aktif, ekspor ke Excel
- **User Story** : Sebagai admin, saya ingin mengatur tahun ajaran dan menandai yang aktif, agar data akademik dikelompokkan dengan benar
- **Acceptance Criteria** :
  - [ ] AC-1: Tambah tahun ajaran dengan rentang tanggal
  - [ ] AC-2: Hanya satu tahun ajaran yang aktif dalam satu waktu
  - [ ] AC-3: Ekspor data ke Excel
- **Route terkait** : `GET/POST /dashboard/academic-years` → `AcademicYearController`
- **Status** : ✅ Done

#### F-06: Manajemen Pembayaran SPP
- **Deskripsi** : CRUD pembayaran dengan integrasi Midtrans (snap token, VA, QRIS), status tracking (pending/settlement/expire), mark paid manual
- **User Story** : Sebagai admin, saya ingin mencatat pembayaran siswa dan memproses pembayaran online, agar semua transaksi tercatat dengan baik
- **Acceptance Criteria** :
  - [ ] AC-1: Buat tagihan baru untuk siswa
  - [ ] AC-2: Generate Snap Token Midtrans untuk pembayaran online
  - [ ] AC-3: Track status pembayaran (pending/settlement/expire/deny)
  - [ ] AC-4: Mark paid manual untuk pembayaran tunai
  - [ ] AC-5: Lihat outstanding tagihan per siswa
  - [ ] AC-6: Ekspor laporan pembayaran ke Excel
- **Route terkait** : `GET/POST /dashboard/payments` → `PaymentController`, `POST /dashboard/payments/{id}/mark-paid`
- **Status** : ✅ Done

#### F-07: Judul Pembayaran (Payment Titles)
- **Deskripsi** : CRUD kategori pembayaran (SPP, DPP, Seragam, dll) yang digunakan sebagai referensi pembuatan tagihan
- **User Story** : Sebagai admin, saya ingin mengatur jenis-jenis pembayaran, agar tagihan bisa dikelompokkan per kategori
- **Acceptance Criteria** :
  - [ ] AC-1: Tambah/edit judul pembayaran dengan kode unik
  - [ ] AC-2: Judul pembayaran digunakan sebagai referensi di form payment
- **Route terkait** : `GET/POST /dashboard/payment-titles` → `PaymentTitleController`
- **Status** : ✅ Done

#### F-08: Integrasi Midtrans Payment Gateway
- **Deskripsi** : End-to-end pembayaran online via Midtrans (Snap) — generate token, handle notifikasi webhook, refund, cancel, cek status
- **User Story** : Sebagai admin, saya ingin memproses pembayaran online via Midtrans, agar siswa bisa bayar dari rumah
- **Acceptance Criteria** :
  - [ ] AC-1: Generate Snap Token untuk pembayaran
  - [ ] AC-2: Webhook notification dari Midtrans diproses otomatis
  - [ ] AC-3: Refund dan cancel transaksi
  - [ ] AC-4: Halaman success/unfinish/error setelah redirect dari Midtrans
  - [ ] AC-5: Verifikasi signature key webhook (SHA-512)
- **Route terkait** : `POST /dashboard/midtrans/snap-token/{payment}`, `POST /midtrans/notification`, `GET /dashboard/midtrans/status/{chargeId}`, `POST /dashboard/midtrans/refund/{payment}`
- **Status** : ✅ Done

#### F-09: WhatsApp Chat (Inbox)
- **Deskripsi** : Sistem komunikasi dua arah dengan orang tua via WhatsApp Meta API — percakapan, template message, assignment admin, reaksi, edit/hapus pesan, pencarian
- **User Story** : Sebagai admin, saya ingin berkomunikasi dengan orang tua siswa via WhatsApp dari dashboard, agar tidak perlu pakai HP pribadi
- **Acceptance Criteria** :
  - [ ] AC-1: Lihat daftar percakapan (active/closed)
  - [ ] AC-2: Kirim pesan teks dan template
  - [ ] AC-3: Assign percakapan ke admin tertentu
  - [ ] AC-4: Edit/hapus pesan yang sudah dikirim
  - [ ] AC-5: Reaksi pesan (emoji)
  - [ ] AC-6: Cari pesan dalam percakapan
  - [ ] AC-7: Mark as read & mark conversation as read
  - [ ] AC-8: Routing otomatis ke admin berdasarkan jam kerja
- **Route terkait** : `GET/POST /dashboard/whatsapp-chat` → `WhatsAppChatController`
- **Status** : ✅ Done

#### F-10: WhatsApp Webhook Incoming (Meta API)
- **Deskripsi** : Webhook endpoint untuk menerima pesan masuk dari WhatsApp Meta Cloud API, verifikasi token, dan routing pesan
- **User Story** : Sebagai sistem, saya ingin menerima dan merespon pesan WhatsApp dari orang tua secara otomatis
- **Acceptance Criteria** :
  - [ ] AC-1: GET endpoint untuk verifikasi webhook (hub_challenge)
  - [ ] AC-2: POST endpoint untuk menerima pesan masuk
  - [ ] AC-3: Validasi signature request
  - [ ] AC-4: Logging semua request
- **Route terkait** : `GET/POST /api/v1/webhook/whatsapp` → `WhatsAppWebhookController`
- **Status** : ✅ Done

#### F-11: Manajemen User & RBAC (Role & Permission)
- **Deskripsi** : CRUD user, role, dan permission dengan Spatie Laravel Permission. Pengaturan peran & izin per resource
- **User Story** : Sebagai super admin, saya ingin mengatur siapa yang bisa mengakses fitur apa, agar keamanan data terjaga
- **Acceptance Criteria** :
  - [ ] AC-1: Tambah/edit/hapus user dengan role assignment
  - [ ] AC-2: Aktif/nonaktifkan user
  - [ ] AC-3: CRUD role dengan permission assignment
  - [ ] AC-4: CRUD permission individual
  - [ ] AC-5: Bulk delete users/roles/permissions
  - [ ] AC-6: Filter user berdasarkan role
- **Route terkait** : `GET/POST /dashboard/settings/users`, `/dashboard/settings/roles`, `/dashboard/settings/permission`
- **Status** : ✅ Done

#### F-12: Profil Pengguna
- **Deskripsi** : Edit profil pribadi pengguna (nama, email, password, avatar, two-factor)
- **User Story** : Sebagai pengguna, saya ingin mengupdate profil saya sendiri, agar data saya tetap akurat
- **Route terkait** : `GET/PATCH /dashboard/profile` → `ProfileController`
- **Status** : ✅ Done

#### F-13: Notifikasi Sistem
- **Deskripsi** : Sistem notifikasi in-app — daftar notifikasi, mark read, unread count, delete, filter by type
- **User Story** : Sebagai pengguna, saya ingin mendapat notifikasi tentang pembayaran dan aktivitas sistem, agar tidak ketinggalan informasi penting
- **Acceptance Criteria** :
  - [ ] AC-1: Lihat daftar notifikasi dengan filter status/type
  - [ ] AC-2: Unread count badge di navbar
  - [ ] AC-3: Mark all as read
  - [ ] AC-4: Hapus notifikasi individu
- **Route terkait** : `GET /dashboard/notifications` → `NotificationController`
- **Status** : ✅ Done

#### F-14: Preferensi Notifikasi
- **Deskripsi** : Pengaturan channel notifikasi per pengguna (WhatsApp, Email, SMS, Push) dan frekuensi (immediate/daily/weekly)
- **User Story** : Sebagai pengguna, saya ingin memilih bagaimana saya ingin menerima notifikasi, agar tidak terganggu di luar jam kerja
- **Route terkait** : `GET/PUT /dashboard/settings/notification-preferences` → `NotificationPreferenceController`
- **Status** : ✅ Done

#### F-15: Audit Log
- **Deskripsi** : Catatan semua perubahan data (CRUD) — siapa, apa, kapan, data lama & baru, IP address
- **User Story** : Sebagai super admin, saya ingin melihat riwayat perubahan data, agar bisa audit jika ada masalah
- **Acceptance Criteria** :
  - [ ] AC-1: DataTables dengan filter tanggal dan aksi
  - [ ] AC-2: Lihat detail perubahan (old vs new values)
  - [ ] AC-3: Tracking model, action, user
- **Route terkait** : `GET /dashboard/audit-log` → `AuditLogController`
- **Status** : ✅ Done

#### F-16: Pencarian Global
- **Deskripsi** : Pencarian cepat siswa dan kelas dari sidebar/navbar
- **User Story** : Sebagai admin, saya ingin mencari siswa atau kelas dengan cepat, agar tidak perlu navigasi manual
- **Route terkait** : `GET /dashboard/search` → `SearchController`
- **Status** : ✅ Done

#### F-17: Autentikasi Pengguna
- **Deskripsi** : Login, register, logout, reset password via email, verifikasi email, konfirmasi password
- **User Story** : Sebagai pengguna, saya ingin login dengan aman dan bisa mereset password jika lupa
- **Route terkait** : `routes/auth.php` — semua route autentikasi Laravel Breeze
- **Status** : ✅ Done

#### F-18: API Endpoints (REST)
- **Deskripsi** : REST API untuk Students, Classrooms, Payments dengan Sanctum token auth, search, pagination, dan relation loading
- **User Story** : Sebagai developer eksternal, saya ingin mengakses data sekolah via API, agar bisa integrasi dengan sistem lain
- **Route terkait** : `GET/POST /api/students`, `/api/classrooms`, `/api/payments`, `POST /api/sanctum/token`
- **Status** : ✅ Done

#### F-19: Scheduled Task — Generate Tagihan Bulanan
- **Deskripsi** : Artisan command `school:generate-monthly-bills` yang dijadwalkan setiap tanggal 1 bulan untuk generate tagihan SPP
- **User Story** : Sebagai admin, saya ingin tagihan SPP dibuat otomatis setiap bulan, agar tidak perlu membuat manual
- **Route terkait** : `routes/console.php` → `Schedule::command('school:generate-monthly-bills')->monthlyOn(1, '00:00')`
- **Status** : ✅ Done

#### F-20: Real-time Broadcasting (WebSocket)
- **Deskripsi** : Channel broadcasting untuk pembaruan real-time (pembayaran, notifikasi, import siswa, chat WhatsApp) menggunakan Laravel Reverb
- **User Story** : Sebagai admin, saya ingin melihat pembaruan data secara real-time, tanpa perlu me-refresh halaman
- **Route terkait** : `routes/channels.php` → 15+ channel definitions
- **Status** : ✅ Done

#### F-21: Manajemen Cache
- **Deskripsi** : Tombol flush cache di dashboard untuk membersihkan cache aplikasi, config, dan view
- **User Story** : Sebagai admin, saya ingin membersihkan cache dengan mudah, agar perubahan konfigurasi langsung terlihat
- **Route terkait** : `POST /dashboard/cache/flush`
- **Status** : ✅ Done

#### F-22: Konfigurasi Sistem (System Config)
- **Deskripsi** : Pengaturan sistem yang dapat diedit via database — nama sekolah, alamat, integrasi WhatsApp/Midtrans/AI, notifikasi, tahun akademik aktif
- **User Story** : Sebagai admin, saya ingin mengubah pengaturan sistem tanpa mengedit file .env
- **Status** : ✅ Done (data seeding di SystemConfigSeeder)

### 4.2 Fitur yang Belum Ada (Out of Scope / Backlog)

#### F-23: Manajemen Nilai (Grade)
- **Deskripsi** : Input dan rekap nilai siswa per semester, komponen nilai, predikat, narasi rapor
- **Prioritas** : High
- **Alasan ditunda** : Model & migration Grade sudah ada, controller belum diimplementasi

#### F-24: Manajemen Kehadiran (Attendance)
- **Deskripsi** : Absensi siswa per kelas per tanggal dengan status hadir/sakit/izin/alpa, GPS check-in untuk pegawai
- **Prioritas** : High
- **Alasan ditunda** : Model & migration StudentAttendance sudah ada, controller belum diimplementasi

#### F-25: Manajemen Jadwal Pelajaran (Schedule)
- **Deskripsi** : Penjadwalan mata pelajaran per kelas — hari, jam, guru, ruang
- **Prioritas** : High
- **Alasan ditunda** : Model & migration Schedule/ScheduleDetails sudah ada, controller & view belum diimplementasi

#### F-26: Rapor Digital (Report Card)
- **Deskripsi** : Generate rapor PDF dari nilai siswa per semester, dengan template dan predikat
- **Prioritas** : High
- **Alasan ditunda** : Migration report_cards & student_report_cards sudah ada, controller belum diimplementasi

#### F-27: Manajemen Pegawai & Guru
- **Deskripsi** : CRUD data pegawai dan guru, penugasan guru ke kelas & mata pelajaran
- **Prioritas** : Medium
- **Alasan ditunda** : Model Employee, Teacher sudah ada, controller belum diimplementasi

#### F-28: Penggajian (Payroll)
- **Deskripsi** : Perhitungan gaji pegawai, tunjangan, potongan pajak PPh 21
- **Prioritas** : Low
- **Alasan ditunda** : Model EmployeeSalaryConfiguration sudah ada, fitur belum dimulai

#### F-29: Manajemen Tugas & Proyek (Tasks)
- **Deskripsi** : Task management untuk tim sekolah — subtask, dependencies, comments, progress tracking
- **Prioritas** : Low
- **Alasan ditunda** : Migration tasks, task_dependencies, task_comments sudah ada, UI belum dibangun

#### F-30: Manajemen Ekstrakurikuler
- **Deskripsi** : Pendaftaran dan penilaian kegiatan ekstrakurikuler siswa
- **Prioritas** : Low
- **Alasan ditunda** : Migration student_extracurriculars & extracurriculars sudah ada

#### F-31: Kalender Akademik
- **Deskripsi** : Kalender dengan event akademik — ujian, libur, penerimaan rapor
- **Prioritas** : Low
- **Alasan ditunda** : Migration academic_calendars sudah ada

---

## 5. Alur Pengguna (User Flows)

### 5.1 Flow Pembayaran SPP via Midtrans
```
Admin membuat tagihan → Pilih siswa & judul bayar → 
System generate Order ID → Admin klik "Bayar Online" →
System call Midtrans Snap API → Dapat Snap Token → 
Redirect ke halaman Midtrans → Orang tua bayar (VA/QRIS/CC) →
Midtrans kirim webhook notifikasi → System update status payment →
Kirim konfirmasi via WhatsApp otomatis →
Redirect ke halaman success/fail
```

### 5.2 Flow WhatsApp Chat
```
Orang tua kirim WA ke nomor sekolah → 
Meta Webhook → System terima pesan → 
Cari/create conversation → Cari student by phone →
Route ke admin yang available (assign) →
Admin buka dashboard → Lihat percakapan baru →
Balas pesan → System kirim via Meta API →
Orang tua terima balasan
```

### 5.3 Flow Import Siswa Excel
```
Admin download template → Isi data sesuai format DAPODIK →
Upload file .xlsx → System validasi kolom →
Job queue (batch) mulai import per baris →
Progress bar update real-time via WebSocket →
Selesai → Notifikasi jumlah berhasil/gagal
```

### 5.4 Flow Autentikasi & RBAC
```
User buka URL login → Input email & password →
Validasi kredensial → Cek is_active →
Cek email_verified → Redirect ke dashboard →
Middleware periksa permission per route →
Akses diberikan/ditolak sesuai role
```

### 5.5 Flow Generate Tagihan Bulanan (Scheduled)
```
Cron: tgl 1 setiap bulan jam 00:00 →
Artisan command school:generate-monthly-bills →
Ambil semua siswa aktif → Buat payment per student →
Kirim notifikasi WhatsApp ke orang tua →
Log hasil eksekusi
```

---

## 6. Persyaratan Fungsional

| ID | Fitur | Deskripsi Requirement | Prioritas |
|---|---|---|---|
| FR-01 | Dashboard | Menampilkan statistik real-time (jumlah siswa, pembayaran, tunggakan, grafik) | Must Have |
| FR-02 | Manajemen Siswa | CRUD siswa + upload foto + filter + search + status | Must Have |
| FR-03 | Import Siswa | Import Excel batch dengan progress bar real-time | Must Have |
| FR-04 | Manajemen Kelas | CRUD kelas + assignment siswa & guru | Must Have |
| FR-05 | Tahun Ajaran | CRUD tahun ajaran + penanda aktif | Must Have |
| FR-06 | Pembayaran SPP | CRUD payment + status tracking + mark paid + outstanding check | Must Have |
| FR-07 | Payment Titles | CRUD kategori pembayaran | Must Have |
| FR-08 | Midtrans Gateway | Snap token, webhook, refund, cancel, status check | Must Have |
| FR-09 | WhatsApp Chat | Inbox dua arah, template, assign, reactions, search | Must Have |
| FR-10 | WhatsApp Webhook | Verifikasi & handle incoming messages dari Meta | Must Have |
| FR-11 | RBAC Users | CRUD user + role + permission (Spatie) | Must Have |
| FR-12 | Profil | Edit profil sendiri + password | Must Have |
| FR-13 | Notifikasi | Notifikasi in-app + unread count + mark read | Must Have |
| FR-14 | Preferensi Notifikasi | Channel & frekuensi notifikasi per user | Should Have |
| FR-15 | Audit Log | Riwayat perubahan data + filter + detail | Must Have |
| FR-16 | Pencarian Global | Cari siswa/kelas dari navbar | Should Have |
| FR-17 | Autentikasi | Login/register/reset password/verifikasi email | Must Have |
| FR-18 | REST API | Endpoint CRUD Students/Classrooms/Payments dengan Sanctum | Should Have |
| FR-19 | Scheduler | Generate tagihan bulanan otomatis | Should Have |
| FR-20 | Broadcasting | Real-time update via WebSocket (Reverb) | Should Have |
| FR-21 | Cache Flush | Tombol bersihkan cache dari dashboard | Should Have |
| FR-22 | System Config | Konfigurasi sistem via database (editable dari UI) | Should Have |

---

## 7. Persyaratan Non-Fungsional

| ID | Kategori | Requirement | Target |
|---|---|---|---|
| NFR-01 | Performance | Response time halaman dashboard dengan DataTables | < 2 detik |
| NFR-02 | Performance | Import Excel 1000 siswa | < 30 detik (via queue) |
| NFR-03 | Security | Verifikasi signature webhook Midtrans (SHA-512) | Wajib |
| NFR-04 | Security | Verifikasi webhook WhatsApp Meta (hub_challenge) | Wajib |
| NFR-05 | Security | Semua endpoint admin dilindungi middleware auth & permission | Wajib |
| NFR-06 | Security | Password di-hash dengan bcrypt | Wajib |
| NFR-07 | Security | Data sensitif (chat content, notes) di-enkripsi di database | Wajib |
| NFR-08 | Reliability | Antrian job Redis untuk tasks berat (import, broadcast) | Wajib |
| NFR-09 | Reliability | Rate limiting untuk webhook (Midtrans 30/min, WhatsApp 60/min) | Wajib |
| NFR-10 | Availability | Session & cache menggunakan Redis (bukan file) | Wajib |
| NFR-11 | Scalability | Queue worker dapat di-scale horizontal | Harus mendukung |
| NFR-12 | Maintainability | Audit log otomatis untuk semua model penting | Wajib |

---

## 8. Arsitektur & Integrasi

### 8.1 Tech Stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend Framework | Laravel | 12.x |
| PHP | PHP | ^8.2 |
| Frontend | React + Tailwind CSS | React 19, Tailwind 3 |
| Build Tool | Vite | ^6.0 |
| Database | MySQL | (via Docker) |
| Cache / Queue / Session | Redis | (via Docker) |
| WebSocket | Laravel Reverb | ^1.0 |
| Real-time Client | Laravel Echo + Pusher JS | ^2.3 / ^8.5 |
| State Management (FE) | Zustand | ^5.0 |
| Canvas (future) | Konva + React-Konva | ^10.3 / ^19.2 |
| CSS Framework | Tailwind CSS + Alpine.js | 3.x / 3.4 |

### 8.2 Integrasi Eksternal

#### Midtrans Payment Gateway
- **Tujuan** : Pemrosesan pembayaran online (Virtual Account, QRIS, Credit Card, E-Wallet)
- **Endpoint** : `POST /midtrans/notification` (webhook), `POST https://app.sandbox.midtrans.com/snap/v1/transactions` (Snap API)
- **Trigger** : Admin klik "Bayar Online" → Generate Snap Token → Redirect ke Midtrans
- **Error Handling** : Webhook diverifikasi via signature key SHA-512; retry dari Midtrans otomatis; charge_not_found dicatat ke tabel terpisah

#### WhatsApp Meta Cloud API
- **Tujuan** : Komunikasi dua arah dengan orang tua siswa via WhatsApp
- **Endpoint** : `GET/POST /api/v1/webhook/whatsapp` (webhook), `https://graph.facebook.com/v24.0/{phone-id}/messages` (send API)
- **Trigger** : Pesan masuk dari orang tua → Webhook → Routing ke admin; Admin kirim pesan → Send API
- **Error Handling** : Rate limiting 60 req/min; retry dengan exponential backoff; logging semua request ke whatsapp_request_logs

#### Anthropic Claude / DeepSeek AI
- **Tujuan** : Generasi narasi rapor otomatis (future)
- **Status** : Config sudah ada, provider belum diimplementasi penuh (saat ini memakai StubAiTemplateProvider)

#### Sentry
- **Tujuan** : Error tracking & performance monitoring
- **Konfigurasi** : `SENTRY_LARAVEL_DSN` + breadcrumbs untuk queries, queue, redis, cache, logs

### 8.3 Diagram Arsitektur (Teks)

```
┌────────────────────────────────────────────────────────────┐
│                    Browser (React SPA)                      │
│  Dashboard · Students · Payments · WhatsApp Chat · Settings │
└──────────────────────┬─────────────────────────────────────┘
                       │ HTTP / WebSocket
          ┌────────────┼────────────────┐
          ▼            ▼                 ▼
┌─────────────────┐ ┌──────────┐ ┌──────────────┐
│  Laravel Web     │ │ REST API │ │  Laravel      │
│  (web.php)       │ │(api.php) │ │  Reverb WS    │
│  Auth + RBAC     │ │Sanctum   │ │  Broadcasting │
│  Middleware       │ │Auth      │ │              │
└────────┬─────────┘ └────┬─────┘ └──────────────┘
         │                │
         ▼                ▼
┌──────────────────────────────────────────────────┐
│               Laravel Backend                     │
│  Controllers · Services · Jobs · Events           │
│  ┌──────────┐ ┌────────┐ ┌───────────────────┐  │
│  │ Midtrans │ │WhatsApp│ │ AI / Other        │  │
│  │Service   │ │Service │ │ Services          │  │
│  └──────────┘ └────────┘ └───────────────────┘  │
└──────────────────────┬───────────────────────────┘
                       │
          ┌────────────┼────────────────┐
          ▼            ▼                 ▼
    ┌──────────┐ ┌──────────┐ ┌──────────────┐
    │  MySQL   │ │  Redis   │ │  Filesystem  │
    │Database  │ │Cache/Queue│ │  (S3/Local)  │
    └──────────┘ └──────────┘ └──────────────┘
                       │
                       ▼
          ┌────────────────────────┐
          │    External Services    │
          │  Midtrans · Meta WA    │
          │  Sentry · AI APIs      │
          └────────────────────────┘
```

---

## 9. Struktur Database

### `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid | Primary Key |
| name | string(255) | |
| email | string(255) | Unique |
| password | string(255) | Hashed |
| is_active | boolean | Default true |
| slug | string(255) | Unique |

Relasi: `users` → HasOne `students`, HasOne `employees`, HasMany `notifications`, HasMany `whatsapp_conversations`, Spatie roles & permissions

### `students`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid | Primary Key |
| user_id | uuid | FK → users, nullable |
| name | string(255) | |
| nisn | string(20) | Unique |
| gender | enum(Laki-laki/Perempuan) | |
| birth_place | string(255) | |
| birth_date | date | |
| phone | string(20) | |
| status | string(255) | Default 'active' |
| slug | string(255) | Unique |

Relasi: `students` → BelongsToMany `classrooms` (pivot: `student_classrooms`), HasMany `payments`, HasMany `student_fees`

### `classrooms`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid | Primary Key |
| name | string(255) | |
| slug | string(255) | Unique |
| academic_year_id | uuid | FK → academic_years |
| classroom_type | string(100) | |

Relasi: `classrooms` → BelongsToMany `students`, BelongsToMany `subjects`, BelongsToMany `teachers`, BelongsTo `academic_years`

### `payments`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid | Primary Key |
| order_id | string(255) | |
| student_id | uuid | FK → students |
| payment_title_id | uuid | FK → payment_titles |
| gross_amount | decimal(15,2) | |
| status | string(50) | Default 'pending' |
| paid_at | timestamp | Nullable |

Relasi: `payments` → BelongsTo `student`, BelongsTo `payment_title`, HasMany `charges`

### `charges`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid | Primary Key |
| order_id | string(255) | Unique |
| student_id | uuid | FK → students |
| gross_amount | decimal(15,2) | |
| transaction_status | string(50) | |
| snap_token | string(255) | Nullable |

Relasi: `charges` → BelongsTo `student`, BelongsTo `payment_title`

### `whatsapp_conversations`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid | Primary Key |
| phone_number | string(20) | Unique |
| profile_name | string(255) | Nullable |
| student_id | uuid | FK → students, nullable |
| assigned_admin_id | uuid | FK → users, nullable |
| status | enum(active/closed/archived) | Default 'active' |

### `whatsapp_messages`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid | Primary Key |
| conversation_id | uuid | FK → whatsapp_conversations |
| sender_type | enum(parent/admin) | |
| content | longText | Encrypted |
| message_type | enum(text/image/document/template) | |
| status | enum(sent/delivered/read/failed) | |
| is_deleted | boolean | Default false |

### `system_configs`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid | Primary Key |
| grup | string(255) | Kelompok konfigurasi |
| key | string(255) | Unique |
| label | string(255) | Label untuk UI |
| nilai | text | Nilai konfigurasi |
| tipe | string(255) | text/boolean/number/select |
| is_editable | boolean | Default true |

### `audit_logs`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid | Primary Key |
| user_id | uuid | FK → users, nullable |
| action | string(50) | created/updated/deleted |
| model_type | string(255) | |
| model_id | string(255) | |
| old_values | json | Nullable |
| new_values | json | Nullable |

(Total 62 tabel — lihat migration files untuk detail lengkap)

### Relasi Utama
```
users ──→ students (1:1)
users ──→ employees (1:1)
users ──→ roles & permissions (M:N via Spatie)
students ──→ classrooms (M:N via student_classrooms)
students ──→ payments (1:M)
students ──→ student_fees (1:M)
payments ──→ charges (1:M via order_id)
payments ──→ payment_titles (M:1)
classrooms ──→ subjects (M:N via classroom_subjects)
classrooms ──→ teachers (M:N via teacher_classrooms)
whatsapp_conversations ──→ whatsapp_messages (1:M)
classrooms ──→ academic_years (M:1)
```

---

## 10. Antarmuka Pengguna (UI Requirements)

- **Design system** : Tailwind CSS utility classes + custom components
- **Komponen UI utama** :
  - DataTables dengan server-side processing (Yajra) — sorting, searching, pagination
  - Form validation dengan error messages inline
  - Modal/Alert dengan SweetAlert2
  - Sidebar navigasi dengan menu collapsible
  - Breadcrumbs (Diglactic)
  - Real-time notification dropdown di navbar
- **Behavior responsif** : Admin dashboard desktop-first, dengan mobile-friendly untuk chat WhatsApp
- **Notifikasi & feedback** :
  - Toast/sweetalert untuk success/error setelah action
  - Badge unread count untuk notifikasi
  - Real-time update via Laravel Echo untuk import progress dan chat

---

## 11. Keamanan & Compliance

- **Autentikasi** : Laravel Breeze (session-based) + Laravel Sanctum (API token)
- **Otorisasi / Role** : Spatie Laravel Permission — permission-based middleware di `ResourceController` (view-{resource}, create-{resource}, edit-{resource}, delete-{resource})
- **Validasi input** : Form Request classes untuk setiap resource (StudentRequest, PaymentRequest, dll)
- **Data sensitif yang dilindungi** :
  - Password di-hash (bcrypt via Laravel)
  - Content WhatsApp & notes di-enkripsi (Laravel encrypted casting)
  - API keys & tokens di .env, tidak di-commit
- **Rate limiting** : 6 rate limiter berbeda untuk login, webhook, API
- **CSRF Protection** : Aktif untuk semua route web (kecuali webhook Midtrans)
- **Middleware** : `CheckUserStatus` (cegah user non-aktif login), `auth`, `verified`, `role_or_permission`, `can`

---

## 12. Penanganan Error & Edge Cases

| Skenario | Behavior yang Diharapkan |
|----------|--------------------------|
| Midtrans timeout / gagal | Retry dari Midtrans; charge tercatat untuk investigasi manual; fallback mark paid manual oleh admin |
| WA API rate limit (429) | Queue job dengan retry delay; log ke whatsapp_request_logs |
| Tagihan duplikat | Validasi di level controller; cek existing payment sebelum create |
| Queue worker mati | Job tetap di Redis; worker bisa direstart tanpa kehilangan data |
| Webhook duplikat | Idempotent processing via order_id unique constraint |
| User non-aktif login | Middleware CheckUserStatus redirect ke login dengan pesan error |
| NISN duplikat saat import | Dicatat sebagai failure row; import tetap lanjut untuk baris lain |
| Percakapan WhatsApp dari nomor tak dikenal | Auto-create conversation baru; flag manual assignment |
| Midtrans webhook signature invalid | Log warning; reject dengan 403 |
| Session expired | Redirect ke login; form data tidak hilang via session flash |
| Backup database (future) | Migrasi bisa di-re-run dengan fresh untuk development |

---

## 13. Glossary

| Istilah | Definisi |
|---------|----------|
| SPP | Sumbangan Pembinaan Pendidikan — biaya rutin sekolah per bulan |
| DPP | Dana Pengembangan Pendidikan — biaya pendaftaran/pengembangan |
| NISN | Nomor Induk Siswa Nasional — ID unik siswa dari Kemendikbud |
| DAPODIK | Data Pokok Pendidikan — sistem data pokok pendidikan Kemendikbud |
| VA | Virtual Account — nomor rekening virtual untuk transfer bank |
| QRIS | Quick Response Code Indonesian Standard — standar QR code pembayaran Indonesia |
| Snap | Produk Midtrans untuk pembayaran satu halaman (embedded) |
| RBAC | Role-Based Access Control — kontrol akses berbasis peran |
| Reverb | Laravel Reverb — WebSocket server first-party Laravel |
| Rapor | Laporan hasil belajar siswa per semester |

---

## 14. Riwayat Dokumen

| Versi | Tanggal | Perubahan | Author |
|-------|---------|-----------|--------|
| 1.0 | 28 Mei 2026 | Initial — berdasarkan kode aktual ProductSchool ERP | Claude |
