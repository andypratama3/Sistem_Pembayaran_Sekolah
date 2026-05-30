<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SystemConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            // GROUP: SCHOOL
            [
                'grup' => 'sekolah',
                'key' => 'school_name',
                'label' => 'Nama Sekolah',
                'nilai' => 'SMA Negeri 1 Jakarta',
                'tipe' => 'text',
                'deskripsi' => 'Nama resmi institusi pendidikan.',
                'urutan' => 1,
            ],
            [
                'grup' => 'sekolah',
                'key' => 'school_address',
                'label' => 'Alamat Sekolah',
                'nilai' => 'Jl. Pendidikan No. 123, Jakarta',
                'tipe' => 'text',
                'deskripsi' => 'Alamat lengkap sekolah.',
                'urutan' => 2,
            ],
            [
                'grup' => 'sekolah',
                'key' => 'school_email',
                'label' => 'Email Sekolah',
                'nilai' => 'info@sma1jakarta.sch.id',
                'tipe' => 'text',
                'deskripsi' => 'Email resmi untuk korespondensi.',
                'urutan' => 3,
            ],
            [
                'grup' => 'sekolah',
                'key' => 'headmaster',
                'label' => 'Kepala Sekolah',
                'nilai' => '-',
                'tipe' => 'select',
                'deskripsi' => 'Pejabat yang berwenang menandatangani rapor dan dokumen resmi.',
                'urutan' => 4,
            ],

            // GROUP: SYSTEM
            [
                'grup' => 'system',
                'key' => 'app_name',
                'label' => 'Nama Aplikasi',
                'nilai' => 'ProductSchool ERP',
                'tipe' => 'text',
                'deskripsi' => 'Nama sistem yang muncul di judul browser dan header.',
                'urutan' => 1,
            ],
            [
                'grup' => 'system',
                'key' => 'maintenance_mode',
                'label' => 'Mode Pemeliharaan',
                'nilai' => '0',
                'tipe' => 'boolean',
                'deskripsi' => 'Jika diaktifkan, hanya administrator yang dapat mengakses sistem.',
                'urutan' => 2,
            ],

            // GROUP: NOTIFICATION
            [
                'grup' => 'notifikasi',
                'key' => 'whatsapp_notification_enabled',
                'label' => 'Aktifkan Notifikasi WhatsApp',
                'nilai' => '1',
                'tipe' => 'boolean',
                'deskripsi' => 'Kirim pemberitahuan sistem melalui WhatsApp.',
                'urutan' => 1,
            ],
            [
                'grup' => 'notifikasi',
                'key' => 'email_notification_enabled',
                'label' => 'Aktifkan Notifikasi Email',
                'nilai' => '1',
                'tipe' => 'boolean',
                'deskripsi' => 'Kirim pemberitahuan sistem melalui Email.',
                'urutan' => 2,
            ],

            // GROUP: AKADEMIK
            [
                'grup' => 'akademik',
                'key' => 'academic_year',
                'label' => 'Tahun Akademik Aktif',
                'nilai' => '2024/2025',
                'tipe' => 'select',
                'deskripsi' => 'Tahun akademik yang sedang berjalan.',
                'urutan' => 1,
            ],

            // GROUP: INTEGRASI
            [
                'grup' => 'integrasi',
                'key' => 'midtrans_server_key',
                'label' => 'Midtrans Server Key',
                'nilai' => env('MIDTRANS_SERVER_KEY', ''),
                'tipe' => 'text',
                'deskripsi' => 'Kunci server autentikasi dari dashboard Midtrans.',
                'urutan' => 1,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'midtrans_client_key',
                'label' => 'Midtrans Client Key',
                'nilai' => env('MIDTRANS_CLIENT_KEY', ''),
                'tipe' => 'text',
                'deskripsi' => 'Kunci client untuk Snap JS dari dashboard Midtrans.',
                'urutan' => 2,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'midtrans_is_production',
                'label' => 'Midtrans Mode Produksi',
                'nilai' => env('MIDTRANS_IS_PRODUCTION', '0'),
                'tipe' => 'boolean',
                'deskripsi' => 'Aktifkan mode production Midtrans (non-sandbox).',
                'urutan' => 3,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'midtrans_webhook_url',
                'label' => 'Midtrans Webhook URL',
                'nilai' => env('MIDTRANS_WEBHOOK_URL', ''),
                'tipe' => 'text',
                'deskripsi' => 'URL tujuan notifikasi dari Midtrans. Gunakan: '.url('/api/midtrans/notification'),
                'urutan' => 4,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'whatsapp_api_url',
                'label' => 'WhatsApp API URL',
                'nilai' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v24.0'),
                'tipe' => 'text',
                'deskripsi' => 'URL endpoint API WhatsApp Meta (Cloud API).',
                'urutan' => 5,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'whatsapp_api_version',
                'label' => 'WhatsApp API Version',
                'nilai' => env('WHATSAPP_API_VERSION', 'v24.0'),
                'tipe' => 'text',
                'deskripsi' => 'Versi API WhatsApp Meta Cloud API yang digunakan.',
                'urutan' => 6,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'whatsapp_phone_number_id',
                'label' => 'WhatsApp Phone ID',
                'nilai' => env('WHATSAPP_PHONE_NUMBER_ID', ''),
                'tipe' => 'text',
                'deskripsi' => 'ID nomor telepon WhatsApp dari Dashboard Meta.',
                'urutan' => 7,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'whatsapp_access_token',
                'label' => 'WhatsApp Access Token',
                'nilai' => env('WHATSAPP_ACCESS_TOKEN', ''),
                'tipe' => 'text',
                'deskripsi' => 'Permanent Access Token dari Dashboard Meta.',
                'urutan' => 8,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'whatsapp_business_id',
                'label' => 'WhatsApp Business ID',
                'nilai' => env('WHATSAPP_BUSINESS_ID', ''),
                'tipe' => 'text',
                'deskripsi' => 'Business Account ID dari Dashboard Meta.',
                'urutan' => 9,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'whatsapp_verify_token',
                'label' => 'WhatsApp Verify Token',
                'nilai' => env('WHATSAPP_VERIFY_TOKEN', 'product_school_webhook_token_2024'),
                'tipe' => 'text',
                'deskripsi' => 'Token verifikasi untuk Webhook Meta. Gunakan URL: '.url('/api/v1/webhook/whatsapp'),
                'urutan' => 10,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'whatsapp_webhook_secret',
                'label' => 'WhatsApp Webhook Secret',
                'nilai' => env('WHATSAPP_WEBHOOK_SECRET', ''),
                'tipe' => 'text',
                'deskripsi' => 'Secret key untuk memverifikasi signature payload dari Meta.',
                'urutan' => 11,
            ],
            [
                'grup' => 'integrasi',
                'key' => 'ai_driver',
                'label' => 'AI Driver',
                'nilai' => env('AI_DRIVER', 'stub'),
                'tipe' => 'select',
                'deskripsi' => 'Provider AI yang digunakan (stub/anthropic/deepseek).',
                'urutan' => 12,
            ],

            // GROUP: INTEGRASI AI
            [
                'grup' => 'integrasi_ai',
                'key' => 'deepseek_api_key',
                'label' => 'DeepSeek API Key',
                'nilai' => env('DEEPSEEK_API_KEY', ''),
                'tipe' => 'text',
                'deskripsi' => 'API Key untuk layanan DeepSeek (digunakan untuk generasi narasi rapor).',
                'urutan' => 1,
            ],
            [
                'grup' => 'integrasi_ai',
                'key' => 'deepseek_model',
                'label' => 'DeepSeek Model',
                'nilai' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
                'tipe' => 'text',
                'deskripsi' => 'Model DeepSeek yang digunakan (misal: deepseek-chat, deepseek-reasoner).',
                'urutan' => 2,
            ],
            [
                'grup' => 'integrasi_ai',
                'key' => 'anthropic_api_key',
                'label' => 'Anthropic API Key (Claude)',
                'nilai' => env('ANTHROPIC_API_KEY', ''),
                'tipe' => 'text',
                'deskripsi' => 'API Key untuk layanan Anthropic Claude.',
                'urutan' => 3,
            ],
            [
                'grup' => 'integrasi_ai',
                'key' => 'anthropic_model',
                'label' => 'Anthropic Model',
                'nilai' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
                'tipe' => 'text',
                'deskripsi' => 'Model Anthropic yang digunakan (misal: claude-3-5-sonnet-20241022, claude-opus-4-20250514).',
                'urutan' => 4,
            ],

            // GROUP: INTEGRASI SMS
            [
                'grup' => 'integrasi_sms',
                'key' => 'sms_gateway_enabled',
                'label' => 'Aktifkan Gateway SMS',
                'nilai' => env('SMS_GATEWAY_ENABLED', '0'),
                'tipe' => 'boolean',
                'deskripsi' => 'Aktifkan pengiriman notifikasi via SMS konvensional.',
                'urutan' => 1,
            ],
            [
                'grup' => 'integrasi_sms',
                'key' => 'sms_gateway_provider',
                'label' => 'Provider SMS',
                'nilai' => env('SMS_GATEWAY', 'fonnte'),
                'tipe' => 'select',
                'deskripsi' => 'Pilih provider SMS yang akan digunakan.',
                'urutan' => 2,
            ],
            [
                'grup' => 'integrasi_sms',
                'key' => 'sms_fonnte_token',
                'label' => 'Fonnte Token',
                'nilai' => env('SMS_FONNTE_TOKEN', ''),
                'tipe' => 'text',
                'deskripsi' => 'API Token dari dashboard Fonnte.',
                'urutan' => 3,
            ],
            [
                'grup' => 'integrasi_sms',
                'key' => 'sms_twilio_account_sid',
                'label' => 'Twilio Account SID',
                'nilai' => env('SMS_TWILIO_ACCOUNT_SID', ''),
                'tipe' => 'text',
                'deskripsi' => 'Account SID dari dashboard Twilio.',
                'urutan' => 4,
            ],
            [
                'grup' => 'integrasi_sms',
                'key' => 'sms_twilio_auth_token',
                'label' => 'Twilio Auth Token',
                'nilai' => env('SMS_TWILIO_AUTH_TOKEN', ''),
                'tipe' => 'text',
                'deskripsi' => 'Auth Token dari dashboard Twilio.',
                'urutan' => 5,
            ],
            [
                'grup' => 'integrasi_sms',
                'key' => 'sms_twilio_from_number',
                'label' => 'Twilio From Number',
                'nilai' => env('SMS_TWILIO_FROM_NUMBER', ''),
                'tipe' => 'text',
                'deskripsi' => 'Nomor pengirim yang terdaftar di Twilio.',
                'urutan' => 6,
            ],
            [
                'grup' => 'integrasi_sms',
                'key' => 'sms_zenziva_userkey',
                'label' => 'Zenziva User Key',
                'nilai' => env('SMS_ZENZIVA_USERKEY', ''),
                'tipe' => 'text',
                'deskripsi' => 'User Key dari dashboard Zenziva.',
                'urutan' => 7,
            ],
            [
                'grup' => 'integrasi_sms',
                'key' => 'sms_zenziva_passkey',
                'label' => 'Zenziva Pass Key',
                'nilai' => env('SMS_ZENZIVA_PASSKEY', ''),
                'tipe' => 'text',
                'deskripsi' => 'Pass Key dari dashboard Zenziva.',
                'urutan' => 8,
            ],

            // GROUP: EMAIL
            [
                'grup' => 'email',
                'key' => 'mail_from_address',
                'label' => 'Email Pengirim',
                'nilai' => env('MAIL_FROM_ADDRESS', 'noreply@school.example.com'),
                'tipe' => 'text',
                'deskripsi' => 'Alamat email yang muncul sebagai pengirim.',
                'urutan' => 1,
            ],
            [
                'grup' => 'email',
                'key' => 'mail_from_name',
                'label' => 'Nama Pengirim',
                'nilai' => env('MAIL_FROM_NAME', 'ProductSchool ERP'),
                'tipe' => 'text',
                'deskripsi' => 'Nama instansi yang muncul sebagai pengirim email.',
                'urutan' => 2,
            ],

            // GROUP: ERROR TRACKING
            [
                'grup' => 'error_tracking',
                'key' => 'sentry_dsn',
                'label' => 'Sentry DSN',
                'nilai' => env('SENTRY_DSN', ''),
                'tipe' => 'text',
                'deskripsi' => 'Data Source Name untuk error tracking Sentry.',
                'urutan' => 1,
            ],
        ];

        foreach ($configs as $config) {
            DB::table('system_configs')->updateOrInsert(
                ['key' => $config['key']],
                array_merge($config, [
                    'id' => (string) Str::uuid(),
                    'is_editable' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
