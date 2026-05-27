<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WhatsAppMessageTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WhatsAppMessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@school.test')->first() ?? User::first();

        if (! $admin) {
            return; // Skip if no admin user exists
        }

        $templates = [
            // Greeting category
            [
                'name' => 'Welcome Message',
                'category' => 'greeting',
                'template_text' => 'Assalamualaikum {parent_name}! Selamat datang di sistem informasi sekolah kami. Bagaimana kabar {student_name}?',
                'response_time_seconds' => 300,
            ],
            [
                'name' => 'Good Morning',
                'category' => 'greeting',
                'template_text' => 'Pagi! Semoga {student_name} memiliki hari yang produktif di sekolah.',
                'response_time_seconds' => 600,
            ],

            // Payment Reminders
            [
                'name' => 'Payment Due Soon',
                'category' => 'payment_reminder',
                'template_text' => 'Lengkap Orang Tua {parent_name}, pembayaran untuk {student_name} akan jatuh tempo dalam 3 hari. Mohon segera lakukan pembayaran.',
                'response_time_seconds' => 1800,
            ],
            [
                'name' => 'Payment Overdue',
                'category' => 'payment_reminder',
                'template_text' => 'Pengingat: Pembayaran {student_name} sudah melewati jatuh tempo. Mohon segera hubungi sekolah untuk pembayaran.',
                'response_time_seconds' => 1800,
            ],

            // Attendance Alerts
            [
                'name' => 'Absence Alert',
                'category' => 'attendance_alert',
                'template_text' => '{student_name} tidak hadir ke sekolah hari ini. Apakah ada yang bisa kami bantu?',
                'response_time_seconds' => 900,
            ],
            [
                'name' => 'Low Attendance Warning',
                'category' => 'attendance_alert',
                'template_text' => 'Kabar tentang kehadiran {student_name}: Tingkat kehadiran di bawah 85%. Mohon perhatikan kehadiran {student_name}.',
                'response_time_seconds' => 1800,
            ],

            // Academic Information
            [
                'name' => 'Grade Posted',
                'category' => 'academic_info',
                'template_text' => 'Nilai {student_name} untuk periode ini telah diposting. Silakan cek portal untuk detail lengkapnya.',
                'response_time_seconds' => 3600,
            ],
            [
                'name' => 'Report Card Ready',
                'category' => 'academic_info',
                'template_text' => 'Rapor {student_name} sudah siap. Silakan ambil di kantor sekolah atau lihat di portal kami.',
                'response_time_seconds' => 3600,
            ],

            // General Information
            [
                'name' => 'School Event Announcement',
                'category' => 'general_info',
                'template_text' => 'Pengumuman: Acara mendatang di sekolah kami akan diadakan minggu depan. Pastikan {student_name} siap berpartisipasi!',
                'response_time_seconds' => 7200,
            ],
            [
                'name' => 'Holiday Notice',
                'category' => 'general_info',
                'template_text' => 'Libur sekolah akan dimulai pada {date}. Semoga {student_name} memiliki istirahat yang nyaman!',
                'response_time_seconds' => 7200,
            ],

            // Administrative
            [
                'name' => 'Document Required',
                'category' => 'administrative',
                'template_text' => 'Kami membutuhkan dokumen dari orang tua {student_name}. Mohon kirimkan melalui portal atau ke kantor sekolah.',
                'response_time_seconds' => 3600,
            ],
            [
                'name' => 'Schedule Update',
                'category' => 'administrative',
                'template_text' => 'Jadwal kelas {student_name} telah diperbarui. Silakan cek portal untuk informasi terbaru.',
                'response_time_seconds' => 3600,
            ],
        ];

        foreach ($templates as $template) {
            $existingTemplate = WhatsAppMessageTemplate::query()
                ->where('name', $template['name'])
                ->first();

            if ($existingTemplate) {
                $existingTemplate->update([
                    'category' => $template['category'],
                    'template_text' => $template['template_text'],
                    'response_time_seconds' => $template['response_time_seconds'] ?? null,
                    'is_active' => true,
                    'created_by' => $admin->id,
                ]);
            } else {
                WhatsAppMessageTemplate::create([
                    'id' => (string) Str::uuid(),
                    'name' => $template['name'],
                    'category' => $template['category'],
                    'template_text' => $template['template_text'],
                    'response_time_seconds' => $template['response_time_seconds'] ?? null,
                    'is_active' => true,
                    'created_by' => $admin->id,
                ]);
            }
        }
    }
}
