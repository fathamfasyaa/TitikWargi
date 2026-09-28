<?php

namespace App\Enums;

enum FlagReason: string
{
    case Spam = 'spam';
    case InappropriatePhoto = 'inappropriate_photo';
    case Fake = 'fake';
    case WrongLocation = 'wrong_location';
    case Other = 'other';

    /**
     * Label shown to users (Indonesian).
     */
    public function label(): string
    {
        return match ($this) {
            self::Spam => 'Spam atau iklan',
            self::InappropriatePhoto => 'Foto tidak pantas (wajah atau plat nomor terlihat)',
            self::Fake => 'Laporan palsu atau bukan jalan rusak',
            self::WrongLocation => 'Lokasi salah',
            self::Other => 'Lainnya',
        };
    }
}
