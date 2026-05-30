# ProductSchool ERP — Sistem Manajemen Sekolah

Sistem manajemen sekolah berbasis web dengan fitur manajemen siswa, pembayaran SPP online (Midtrans), komunikasi orang tua via WhatsApp (Meta API), manajemen kelas & tahun ajaran, serta RBAC (Spatie Permission).

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12 + PHP ^8.2 |
| Frontend | React 19 + Tailwind CSS 3 |
| Database | MySQL |
| Cache / Queue / Session | Redis |
| WebSocket | Laravel Reverb |
| Build Tool | Vite 6 |

## Fitur Utama

- **Dashboard & Statistik** — Grafik real-time (siswa, pembayaran, tunggakan)
- **Manajemen Siswa** — CRUD + upload foto + filter + status (active/inactive/graduated/dropped)
- **Import Siswa (Excel)** — Import batch via job queue + progress bar real-time
- **Manajemen Kelas** — CRUD + assignment siswa & guru + filter tahun ajaran
- **Tahun Ajaran** — CRUD + penanda aktif (hanya satu per waktu)
- **Pembayaran SPP** — Tagihan + status tracking (pending/settlement/expire) + mark paid manual
- **Midtrans Gateway** — Snap token, webhook, refund, cancel, status check
- **WhatsApp Chat** — Inbox dua arah via Meta API, template message, assign admin, reaksi, pencarian
- **RBAC** — User, role, permission management (Spatie)
- **Notifikasi** — In-app notification + preferensi channel (WhatsApp/Email/SMS)
- **Audit Log** — Riwayat perubahan data (siapa, apa, kapan, data lama & baru)
- **Pencarian Global** — Cari siswa/kelas dari navbar
- **REST API** — Endpoint Students/Classrooms/Payments via Sanctum
- **Scheduled Task** — Generate tagihan SPP bulanan otomatis (tgl 1)
- **Real-time Broadcasting** — Update live via Laravel Reverb (import, chat, notifikasi)

## Integrasi Eksternal

| Service | Fungsi |
|---------|--------|
| **Midtrans** | Pembayaran online (VA, QRIS, CC, E-Wallet) |
| **WhatsApp Meta API** | Komunikasi dua arah dengan orang tua |
| **Anthropic / DeepSeek** | AI untuk narasi rapor (config siap) |
| **Sentry** | Error tracking & performance monitoring |

## Persyaratan Sistem

- PHP ^8.2
- Composer
- MySQL
- Redis
- Node.js + NPM

## Instalasi

```bash
git clone <repo-url>
cd skripis_fix
composer install
npm install
cp .env.example .env
php artisan key:generate
# Konfigurasi database, redis, dan API keys di .env
php artisan migrate --seed
php artisan storage:link
npm run build
```

### Menjalankan Development

```bash
composer dev
```

Menjalankan server, queue worker, log (Pail), dan Vite secara concurrent.

## Struktur Direktori

```
app/
├── Console/Commands/    # Artisan commands (generate bills, dll)
├── Enums/               # Enum types
├── Events/              # Event classes
├── Exceptions/          # Custom exceptions
├── Exports/             # Excel exports
├── Helpers/             # Helper functions (currency, system config)
├── Http/
│   ├── Controllers/     # Web & API controllers
│   └── Requests/        # Form request validation
├── Jobs/                # Queue jobs
├── Listeners/           # Event listeners
├── Mail/                # Mailables
├── Models/              # Eloquent models (62 tables)
├── Notifications/       # Notification classes
├── Policies/            # Authorization policies
├── Providers/           # Service providers
├── Services/            # Midtrans, WhatsApp, AI services
└── Traits/              # Reusable traits
routes/
├── web.php              # Web routes (admin dashboard)
├── api.php              # REST API routes
├── auth.php             # Authentication routes
├── channels.php         # Broadcasting channels
├── console.php          # Scheduled commands
└── breadcrumbs.php      # Breadcrumb definitions
config/
├── integrations.php     # Single source of truth for external services
├── services.php         # Delegates to integrations.php
├── midtrans.php         # Delegates to integrations.php
└── ...
```

## Lisensi

Proprietary — kode internal ProductSchool.
