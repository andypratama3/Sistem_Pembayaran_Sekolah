<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,

            AcademicYearSeeder::class,
            SchoolSettingSeeder::class,

            PaymentTitleSeeder::class,

            WhatsAppMessageTemplateSeeder::class,
            WhatsAppConversationSeeder::class,
            WhatsAppMessageSeeder::class,
            WhatsAppWebhookSimulationSeeder::class,
            NotificationPreferenceSeeder::class,

            DemoDataSeeder::class,
            SystemConfigSeeder::class,
        ]);
    }
}
