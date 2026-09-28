<?php

namespace App\Enums;

enum ReportCategory: string
{
    case Pothole = 'pothole';
    case Crack = 'crack';
    case Sinkhole = 'sinkhole';
    case Flooding = 'flooding';

    /**
     * Label shown to users (Indonesian).
     */
    public function label(): string
    {
        return match ($this) {
            self::Pothole => 'Jalan berlubang',
            self::Crack => 'Jalan retak',
            self::Sinkhole => 'Jalan amblas',
            self::Flooding => 'Genangan air',
        };
    }
}
