<?php

namespace Database\Seeders;

use App\Models\MaintenanceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaintenanceServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['Social Media Campaigns', 'Marketing'],
            ['Website Management', 'Web'],
            ['Website Content Updates', 'Web'],
            ['Website Hosting', 'Web'],
            ['Technical Support', 'Support'],
            ['SEO Maintenance', 'Marketing'],
            ['Analytics Reporting', 'Reporting'],
        ])->each(function (array $service): void {
            MaintenanceService::updateOrCreate(
                ['slug' => Str::slug($service[0])],
                [
                    'name' => $service[0],
                    'category' => $service[1],
                    'is_active' => true,
                ],
            );
        });
    }
}
