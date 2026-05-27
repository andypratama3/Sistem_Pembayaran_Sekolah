# Flow Sistem Pembayaran ProductSchool

Dokumen ini menjelaskan arsitektur, skema database, dan alur kerja sistem pembayaran yang terintegrasi dengan Midtrans.

## 1. Arsitektur Sistem

Sistem pembayaran ini menggunakan pola **Asynchronous Webhook** untuk sinkronisasi status pembayaran dengan Midtrans.

### Komponen Utama:
- **Payment Models**: Mengelola data invoice dan status pembayaran di database lokal.
- **Midtrans Service**: Jembatan komunikasi antara aplikasi dan Midtrans API.
- **Snap Integration**: Library client-side untuk menampilkan popup pembayaran.
- **Webhook Listener**: Endpoint publik untuk menerima notifikasi status dari Midtrans.

---

## 2. Skema Database

Berikut adalah tabel-tabel utama yang terlibat dalam sistem pembayaran:

### Tabel `payment_titles` (Kategori Pembayaran)
Menyimpan jenis tagihan (contoh: SPP, Uang Pangkal, Seragam).

| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | UUID | Primary Key |
| `name` | String | Nama kategori (e.g. "SPP Januari 2026") |
| `code` | String | Kode unik kategori |
| `slug` | String | Slug untuk URL |

### Tabel `payments` (Invoice/Tagihan)
Data utama tagihan yang dikirim ke siswa.

| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | UUID | Primary Key |
| `order_id` | String | ID Order internal (Format: INV-...) |
| `student_id` | UUID | Relasi ke tabel `students` |
| `classroom_id` | UUID | Relasi ke tabel `classrooms` |
| `payment_title_id`| UUID | Relasi ke tabel `payment_titles` |
| `gross_amount` | Decimal | Total tagihan |
| `status` | String | Status (`pending`, `completed`, `failed`, `cancelled`, `expired`) |
| `payment_type` | String | Metode pembayaran (e.g. `manual`, `bank_transfer`) |
| `transaction_id` | String | ID Transaksi dari Midtrans |
| `paid_at` | DateTime | Waktu pelunasan |

### Tabel `charges` (Transaksi Real-time)
Mencatat setiap percobaan transaksi melalui Midtrans Snap.

| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | UUID | Primary Key |
| `order_id` | String | ID Order Midtrans (Format: PAY-{payment_id}-{ts}) |
| `snap_token` | String | Token untuk membuka popup Snap Midtrans |
| `transaction_status`| String | Status teknis dari Midtrans |
| `gross_amount` | Decimal | Jumlah transaksi |

### Tabel `student_fees` (Kewajiban Siswa)
Digunakan untuk tracking kewajiban bayar yang mungkin periodik.

---

## 3. Alur Proses Pembayaran (End-to-End)

### A. Tahap Pembuatan Tagihan (Admin)
1. Admin membuat tagihan melalui dashboard.
2. `PaymentController@store` memanggil `PaymentService@create`.
3. Record baru dibuat di tabel `payments` dengan status `pending`.
4. Record otomatis dibuat di tabel `student_fees` untuk tracking kewajiban.

### B. Tahap Inisialisasi Pembayaran (Siswa/Orang Tua)
1. Siswa memilih tagihan dan klik tombol **"Bayar Sekarang"**.
2. Sistem memanggil `MidtransController@getSnapToken`.
3. `MidtransService` mengirim data transaksi ke Midtrans API.
4. Midtrans mengembalikan `snap_token`.
5. Sistem menyimpan data transaksi di tabel `charges` dan mengirim `snap_token` ke frontend.
6. Frontend menampilkan Popup Snap Midtrans.

### C. Tahap Eksekusi Pembayaran
1. Siswa memilih metode pembayaran (VA BCA, Mandiri, QRIS, dll).
2. Siswa melakukan transfer/pembayaran.
3. Midtrans memproses transaksi tersebut.

### D. Tahap Sinkronisasi Status (Webhook)
1. Midtrans mengirimkan **HTTP POST Notification** ke endpoint `/midtrans/notification`.
2. `MidtransController@notification` menerima data.
3. `MidtransService@processCallback` melakukan verifikasi `signature_key` untuk keamanan.
4. Sistem mencocokkan `order_id` dengan data di database.
5. **Update Status**:
   - Jika status `settlement` atau `capture`:
     - Update `payments.status` menjadi `completed`.
     - Update `charges.transaction_status` menjadi `success`.
     - Set `payments.paid_at = now()`.
     - Trigger event `PaymentCompleted`.
   - Jika status `deny`, `cancel`, atau `expire`:
     - Update `payments.status` sesuai kondisi.

---

## 4. Interaksi WhatsApp Bot (Untuk Orang Tua)

Sistem dilengkapi dengan Chatbot otomatis yang memudahkan orang tua untuk mengecek tagihan tanpa harus login ke portal.

### Alur Cek Tagihan via WhatsApp:
1. **Inisiasi**: Orang tua mengirim pesan "cek" atau memilih menu "Cek Tagihan / SPP".
2. **Input NISN**: Bot meminta orang tua memasukkan 10 digit NISN putra/putrinya.
3. **Validasi & Query**: 
   - `WhatsAppBotService` mencari data siswa berdasarkan NISN.
   - Sistem melakukan query ke tabel `payments` untuk mencari tagihan dengan status `pending` atau `expired`.
4. **Respons Otomatis**:
   - Jika ditemukan, bot mengirimkan daftar tagihan lengkap beserta total tunggakan.
   - Jika tidak ditemukan, bot memberikan pesan selamat bahwa tagihan sudah lunas.
5. **Instruksi Bayar**: Bot juga menyediakan menu "Cara Pembayaran" yang menjelaskan langkah transfer atau bayar via TU.

---

## 5. Pemetaan Status Midtrans

| Status Midtrans | Status Sistem (`payments`) | Deskripsi |
| :--- | :--- | :--- |
| `capture` | `completed` | Pembayaran kartu kredit berhasil |
| `settlement` | `completed` | Dana berhasil diterima (VA/E-wallet/QRIS) |
| `pending` | `pending` | Menunggu pembayaran dari siswa |
| `deny` | `failed` | Transaksi ditolak oleh penyedia pembayaran |
| `cancel` | `cancelled` | Transaksi dibatalkan oleh user/admin |
| `expire` | `expired` | Waktu pembayaran telah habis |

---

## 5. Fitur Tambahan

### 1. Pembayaran Manual
Admin dapat menandai tagihan sebagai lunas secara manual melalui tombol **"Mark as Paid"** jika siswa membayar tunai di sekolah. Ini akan mengupdate status tanpa melalui Midtrans.

### 2. Refund & Pembatalan
- **Cancel**: Admin bisa membatalkan transaksi yang masih pending melalui dashboard, yang akan mengirim instruksi pembatalan ke Midtrans.
- **Refund**: Untuk transaksi yang sudah lunas, admin bisa melakukan refund (pengembalian dana) melalui dashboard (membutuhkan konfigurasi khusus di akun Midtrans).

### 3. Notifikasi Real-time
Saat pembayaran lunas via Webhook, sistem memicu event `PaymentCompleted` yang dapat dihubungkan ke:
- Pengiriman kwitansi otomatis via WhatsApp/Email.
- Update otomatis status pendaftaran (untuk tagihan Admisi).
- Update status kehadiran atau akses modul pelajaran.

---
*Dokumen ini dibuat otomatis sebagai panduan sistem pembayaran ProductSchool.*
