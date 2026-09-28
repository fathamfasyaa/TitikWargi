<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Unrepaired = 'unrepaired';
    case AwaitingConfirmation = 'awaiting_confirmation';
    case Repaired = 'repaired';

    /**
     * Label shown to users (Indonesian).
     */
    public function label(): string
    {
        return match ($this) {
            self::Unrepaired => 'Belum diperbaiki',
            self::AwaitingConfirmation => 'Menunggu konfirmasi',
            self::Repaired => 'Sudah diperbaiki',
        };
    }
}
