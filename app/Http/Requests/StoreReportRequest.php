<?php

namespace App\Http\Requests;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Models\Report;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    /**
     * Uses ReportPolicy::create (not banned, daily limit not reached).
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Report::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $area = config('titikwargi.report_area');

        return [
            'photos' => ['required', 'array', 'min:1', 'max:3'],
            // Photos are compressed in the browser, but we still accept up to 10 MB
            // in case JavaScript did not run.
            'photos.*' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:10240'],
            'latitude' => ['required', 'numeric', "between:{$area['south']},{$area['north']}"],
            'longitude' => ['required', 'numeric', "between:{$area['west']},{$area['east']}"],
            'category' => ['required', Rule::enum(ReportCategory::class)],
            'severity' => ['required', Rule::enum(ReportSeverity::class)],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Friendlier messages for the location, instead of showing the number range.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'Lokasi belum ditentukan. Izinkan GPS atau geser pin di peta.',
            'longitude.required' => 'Lokasi belum ditentukan. Izinkan GPS atau geser pin di peta.',
            'latitude.between' => 'Lokasi harus berada di wilayah Cianjur.',
            'longitude.between' => 'Lokasi harus berada di wilayah Cianjur.',
        ];
    }
}
