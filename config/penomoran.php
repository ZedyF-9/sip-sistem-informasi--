<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Format nomor
    |--------------------------------------------------------------------------
    |
    | Placeholder:
    | {urut}  = urutan dengan padding digit
    | {tahun} = tahun dari tanggal surat
    | {bulan} = bulan angka dua digit
    | {romawi}= bulan Romawi
    |
    | Format di bawah adalah format aplikasi sementara.
    | Sesuaikan setelah mendapat format resmi dari TU.
    |
    */

    'format' => 'S-{urut}/SMKN4/{tahun}',

    'digit' => 4,

    /*
    | Nomor minimum berikutnya jika penghitung masih lebih kecil.
    |
    | Contoh: jika nomor terakhir administrasi sekolah adalah 125,
    | isi 126 sebelum mulai menggunakan penomoran aplikasi.
    |
    | Penghitung yang sudah lebih besar tidak akan diturunkan.
    */

    'awal' => 1,

    /*
    | Batas pemrosesan PDF.
    */

    'maksimal_halaman' => 50,

    /*
    | Bidang tambahan di atas halaman, satuan milimeter.
    */

    'tinggi_bidang_nomor' => 16,
];