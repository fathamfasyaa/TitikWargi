<?php

/*
|--------------------------------------------------------------------------
| Validation messages (Indonesian)
|--------------------------------------------------------------------------
|
| Only the rules used by TitikWargi. Rules that are missing here fall back
| to English (APP_FALLBACK_LOCALE). Add a line here when a new rule is used.
|
*/

return [

    'array' => ':Attribute tidak valid.',
    'between' => [
        'numeric' => ':Attribute harus di antara :min dan :max.',
    ],
    'enum' => ':Attribute yang dipilih tidak valid.',
    'gte' => [
        'numeric' => ':Attribute harus lebih besar atau sama dengan :value.',
    ],
    'image' => ':Attribute harus berupa gambar.',
    'max' => [
        'array' => ':Attribute maksimal :max buah.',
        'file' => ':Attribute maksimal :max kilobyte.',
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'mimes' => ':Attribute harus berupa file: :values.',
    'min' => [
        'array' => ':Attribute minimal :min buah.',
    ],
    'numeric' => ':Attribute harus berupa angka.',
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'uploaded' => ':Attribute gagal diunggah. Coba lagi.',

    /*
     * Friendly names for form fields, used in place of ":attribute".
     */
    'attributes' => [
        'photos' => 'foto',
        'photos.*' => 'foto',
        'latitude' => 'lokasi',
        'longitude' => 'lokasi',
        'category' => 'jenis kerusakan',
        'severity' => 'tingkat keparahan',
        'address' => 'alamat atau patokan',
        'description' => 'keterangan',
        'reason' => 'alasan',
        'note' => 'catatan',
    ],

];
