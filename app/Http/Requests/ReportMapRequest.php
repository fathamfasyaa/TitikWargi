<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The visible map area, sent by the map on the home page.
 */
class ReportMapRequest extends FormRequest
{
    /**
     * Everyone, including guests, may view the map.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'south' => ['required', 'numeric', 'between:-90,90'],
            'north' => ['required', 'numeric', 'between:-90,90', 'gte:south'],
            'west' => ['required', 'numeric', 'between:-180,180'],
            'east' => ['required', 'numeric', 'between:-180,180', 'gte:west'],
        ];
    }
}
