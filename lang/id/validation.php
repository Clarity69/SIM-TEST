<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pesan validasi (Bahasa Indonesia)
    |--------------------------------------------------------------------------
    */

    'accepted' => 'Kolom :attribute harus disetujui.',
    'active_url' => 'Kolom :attribute harus berupa URL yang valid.',
    'after' => 'Kolom :attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => 'Kolom :attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => 'Kolom :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Kolom :attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => 'Kolom :attribute hanya boleh berisi huruf dan angka.',
    'array' => 'Kolom :attribute harus berupa array.',
    'before' => 'Kolom :attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => 'Kolom :attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => 'Kolom :attribute harus memiliki :min sampai :max item.',
        'file' => 'Ukuran :attribute harus antara :min dan :max kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai antara :min dan :max.',
        'string' => 'Kolom :attribute harus berisi :min sampai :max karakter.',
    ],
    'boolean' => 'Kolom :attribute harus bernilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Password salah.',
    'date' => 'Kolom :attribute harus berupa tanggal yang valid.',
    'date_format' => 'Kolom :attribute harus sesuai format :format.',
    'decimal' => 'Kolom :attribute harus memiliki :decimal angka desimal.',
    'different' => 'Kolom :attribute dan :other harus berbeda.',
    'digits' => 'Kolom :attribute harus terdiri dari :digits digit.',
    'digits_between' => 'Kolom :attribute harus terdiri dari :min sampai :max digit.',
    'dimensions' => 'Dimensi gambar :attribute tidak valid.',
    'distinct' => 'Kolom :attribute memiliki nilai duplikat.',
    'email' => 'Kolom :attribute harus berupa alamat email yang valid.',
    'ends_with' => 'Kolom :attribute harus diakhiri salah satu dari: :values.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'extensions' => 'Kolom :attribute harus berekstensi: :values.',
    'file' => 'Kolom :attribute harus berupa file.',
    'filled' => 'Kolom :attribute wajib memiliki nilai.',
    'gt' => [
        'numeric' => 'Kolom :attribute harus lebih besar dari :value.',
        'string' => 'Kolom :attribute harus lebih dari :value karakter.',
    ],
    'gte' => [
        'numeric' => 'Kolom :attribute harus lebih besar dari atau sama dengan :value.',
        'string' => 'Kolom :attribute minimal :value karakter.',
    ],
    'image' => 'Kolom :attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
    'lowercase' => 'Kolom :attribute harus huruf kecil.',
    'lt' => [
        'numeric' => 'Kolom :attribute harus lebih kecil dari :value.',
        'string' => 'Kolom :attribute harus kurang dari :value karakter.',
    ],
    'lte' => [
        'numeric' => 'Kolom :attribute harus lebih kecil dari atau sama dengan :value.',
        'string' => 'Kolom :attribute maksimal :value karakter.',
    ],
    'max' => [
        'array' => 'Kolom :attribute maksimal berisi :max item.',
        'file' => 'Ukuran :attribute maksimal :max kilobyte.',
        'numeric' => 'Kolom :attribute maksimal bernilai :max.',
        'string' => 'Kolom :attribute maksimal :max karakter.',
    ],
    'mimes' => 'Kolom :attribute harus berupa file bertipe: :values.',
    'mimetypes' => 'Kolom :attribute harus berupa file bertipe: :values.',
    'min' => [
        'array' => 'Kolom :attribute minimal berisi :min item.',
        'file' => 'Ukuran :attribute minimal :min kilobyte.',
        'numeric' => 'Kolom :attribute minimal bernilai :min.',
        'string' => 'Kolom :attribute minimal :min karakter.',
    ],
    'not_in' => ':attribute yang dipilih tidak valid.',
    'numeric' => 'Kolom :attribute harus berupa angka.',
    'password' => [
        'letters' => 'Kolom :attribute harus berisi minimal satu huruf.',
        'mixed' => 'Kolom :attribute harus berisi huruf besar dan huruf kecil.',
        'numbers' => 'Kolom :attribute harus berisi minimal satu angka.',
        'symbols' => 'Kolom :attribute harus berisi minimal satu simbol.',
        'uncompromised' => ':attribute ini pernah bocor di internet. Gunakan :attribute lain.',
    ],
    'present' => 'Kolom :attribute wajib ada.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => 'Kolom :attribute wajib diisi.',
    'required_if' => 'Kolom :attribute wajib diisi bila :other bernilai :value.',
    'required_with' => 'Kolom :attribute wajib diisi bila :values diisi.',
    'same' => 'Kolom :attribute dan :other harus sama.',
    'size' => [
        'array' => 'Kolom :attribute harus berisi :size item.',
        'file' => 'Ukuran :attribute harus :size kilobyte.',
        'numeric' => 'Kolom :attribute harus bernilai :size.',
        'string' => 'Kolom :attribute harus :size karakter.',
    ],
    'starts_with' => 'Kolom :attribute harus diawali salah satu dari: :values.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'timezone' => 'Kolom :attribute harus berupa zona waktu yang valid.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => ':attribute gagal diunggah.',
    'url' => 'Kolom :attribute harus berupa URL yang valid.',
    'uuid' => 'Kolom :attribute harus berupa UUID yang valid.',

    'custom' => [],

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'password',
        'current_password' => 'password saat ini',
        'password_confirmation' => 'konfirmasi password',
    ],

];
