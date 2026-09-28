<?php

namespace App\Enums;

enum ReportSeverity: string
{
    case Light = 'light';
    case Medium = 'medium';
    case Severe = 'severe';

    /**
     * Label shown to users (Indonesian).
     */
    public function label(): string
    {
        return match ($this) {
            self::Light => 'Ringan',
            self::Medium => 'Sedang',
            self::Severe => 'Parah',
        };
    }
}
