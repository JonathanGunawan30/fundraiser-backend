<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'FundRaiser',
                'type' => 'string',
            ],
            [
                'key' => 'site_logo',
                'value' => '',
                'type' => 'image',
            ],
            [
                'key' => 'contact_whatsapp',
                'value' => '081234567890',
                'type' => 'string',
            ],
            [
                'key' => 'contact_email',
                'value' => 'support@fundraiser.com',
                'type' => 'string',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
