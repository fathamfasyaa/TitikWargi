<?php

namespace Database\Factories;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Locations are random points within about 1.5 km of central Cianjur.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => fake()->randomElement(ReportCategory::cases()),
            'severity' => fake()->randomElement(ReportSeverity::cases()),
            'description' => fake()->optional()->sentence(),
            'location' => Report::pointFromCoordinates(
                -6.817 + fake()->randomFloat(5, -0.0135, 0.0135),
                107.142 + fake()->randomFloat(5, -0.0135, 0.0135),
            ),
            'address' => null,
            'kelurahan' => null,
            'status' => ReportStatus::Unrepaired,
        ];
    }

    /**
     * Place the report at an exact location.
     */
    public function at(float $latitude, float $longitude): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => Report::pointFromCoordinates($latitude, $longitude),
        ]);
    }

    /**
     * Indicate that the report has been repaired.
     */
    public function repaired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportStatus::Repaired,
        ]);
    }

    /**
     * Indicate that the report is hidden by a moderator.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'hidden_at' => now(),
        ]);
    }
}
