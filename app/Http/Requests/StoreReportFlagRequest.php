<?php

namespace App\Http\Requests;

use App\Enums\FlagReason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportFlagRequest extends FormRequest
{
    /**
     * Uses ReportPolicy::flag (not the reporter, not flagged before).
     */
    public function authorize(): bool
    {
        return $this->user()->can('flag', $this->route('report'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::enum(FlagReason::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
