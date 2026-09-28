<?php

namespace App\Enums;

enum ModerationAction: string
{
    case HideReport = 'hide_report';
    case UnhideReport = 'unhide_report';
    case ResolveFlags = 'resolve_flags';
    case WarnUser = 'warn_user';
    case BanUser = 'ban_user';
    case UnbanUser = 'unban_user';

    /**
     * Label shown to admins (Indonesian).
     */
    public function label(): string
    {
        return match ($this) {
            self::HideReport => 'Menyembunyikan laporan',
            self::UnhideReport => 'Menampilkan laporan',
            self::ResolveFlags => 'Menandai flag selesai',
            self::WarnUser => 'Memberi peringatan',
            self::BanUser => 'Memblokir pengguna',
            self::UnbanUser => 'Membuka blokir',
        };
    }
}
