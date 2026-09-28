<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class BanUserRequest extends ModerationRequest
{
    /**
     * How long the ban lasts: 7 days, 30 days or permanent.
     */
    public const DURATIONS = ['7', '30', 'permanent'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'duration' => ['required', 'in:'.implode(',', self::DURATIONS)],
        ];
    }
}
