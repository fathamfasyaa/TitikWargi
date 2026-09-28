<?php

namespace Database\Seeders;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with sample data for local development.
     */
    public function run(): void
    {
        // Sample admin. There is no password: admins sign in with Google like everyone else.
        User::factory()->admin()->create([
            'name' => 'Admin TitikWargi',
            'email' => 'admin@titikwargi.test',
            'password' => null,
            'google_id' => null,
        ]);

        $residents = User::factory(3)->create();

        // Sample reports around central Cianjur (-6.817, 107.142).
        // Coordinates are approximate and only meant for testing the map.
        $samples = [
            [-6.8168, 107.1411, 'Jl. Siliwangi', 'Pamoyanan', ReportCategory::Pothole, ReportSeverity::Severe, ReportStatus::Unrepaired],
            [-6.8201, 107.1395, 'Jl. Dr. Muwardi', 'Bojongherang', ReportCategory::Crack, ReportSeverity::Light, ReportStatus::Unrepaired],
            [-6.8142, 107.1448, 'Jl. HOS Cokroaminoto', 'Solokpandan', ReportCategory::Pothole, ReportSeverity::Medium, ReportStatus::Unrepaired],
            [-6.8225, 107.1432, 'Jl. Aria Cikondang', 'Sayang', ReportCategory::Flooding, ReportSeverity::Medium, ReportStatus::Unrepaired],
            [-6.8119, 107.1387, 'Jl. Pasirhayam', 'Sawahgede', ReportCategory::Sinkhole, ReportSeverity::Severe, ReportStatus::Unrepaired],
            [-6.8187, 107.1466, 'Jl. Mangunsarkoro', 'Muka', ReportCategory::Pothole, ReportSeverity::Light, ReportStatus::Repaired],
            [-6.8156, 107.1372, 'Jl. Suroso', 'Pamoyanan', ReportCategory::Crack, ReportSeverity::Medium, ReportStatus::Unrepaired],
            [-6.8239, 107.1403, 'Jl. Ir. H. Juanda', 'Bojongherang', ReportCategory::Pothole, ReportSeverity::Severe, ReportStatus::AwaitingConfirmation],
            [-6.8131, 107.1419, 'Jl. Otista', 'Sayang', ReportCategory::Flooding, ReportSeverity::Light, ReportStatus::Unrepaired],
            [-6.8209, 107.1457, 'Jl. Pramuka', 'Limbangansari', ReportCategory::Crack, ReportSeverity::Light, ReportStatus::Repaired],
        ];

        foreach ($samples as $index => [$latitude, $longitude, $address, $kelurahan, $category, $severity, $status]) {
            $owner = $residents[$index % $residents->count()];

            $report = Report::factory()
                ->at($latitude, $longitude)
                ->for($owner)
                ->create([
                    'category' => $category,
                    'severity' => $severity,
                    'status' => $status,
                    'address' => $address,
                    'kelurahan' => $kelurahan,
                    'created_at' => now()->subDays(10 - $index),
                ]);

            // Other residents (not the owner) support some of the reports.
            $others = $residents->reject(fn (User $resident) => $resident->is($owner));
            $report->supporters()->attach($others->random(rand(0, $others->count())));
        }
    }
}
