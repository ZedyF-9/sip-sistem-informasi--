# Laporan dashboard ringkasan per role

Tanggal: 17 September 2026

## 1. Role dashboard yang diubah

Role aktif ditentukan dari sesi aplikasi terbaru secara read-only:

- Nama akun: Koordinator TU
- Role: `koordinator_tu`
- Route dashboard: `tu.dashboard`
- View: `resources/views/dashboard.blade.php`
- Layout: `resources/views/layouts/internal.blade.php`

Ketiga role menggunakan view dashboard yang sama. Karena itu, tampilan baru dipasang melalui kondisi `$account->role === 'koordinator_tu'` dan partial khusus. Cabang tampilan role lain tetap menggunakan markup sebelumnya.

## 2. File yang diubah

- `resources/views/dashboard.blade.php`
  - Menambahkan kondisi role Koordinator TU.
  - Memanggil partial ringkasan hanya untuk `koordinator_tu`.
  - Seluruh markup dashboard lama tetap digunakan oleh role lain.
- `resources/css/dashboard.css`
  - Menambahkan class berawalan `role-summary-` yang hanya digunakan partial Koordinator TU.
  - Menambahkan aturan desktop, tablet, dan mobile khusus dashboard ringkasan.
- `public/build/manifest.json`
  - Diperbarui otomatis oleh Vite.
- `public/build/assets/app-BCN66wTo.css`
  - Hasil build CSS terbaru.
- `public/build/assets/app-C-6hCRd7.js`
  - Hasil build JavaScript terbaru.

Vite membersihkan file build ber-hash lama secara otomatis. Tidak ada file sumber aplikasi yang dihapus.

## 3. File baru yang dibuat

- `resources/views/partials/dashboard-koordinator-ringkasan.blade.php`
- `dashboard-role-ringkasan-report.md`

Script diagnostik untuk membaca role aktif dan fixture browser dibuat hanya di folder temporary Windows, bukan di project.

## 4. Konfirmasi penghapusan banner “Selamat datang”

Banner sambutan besar, panel ilustrasi sambutan, teks “Selamat datang”, dan tombol “Buka layanan” tidak dirender pada dashboard Koordinator TU.

Banner tersebut tetap dirender pada dashboard Kepala Sekolah dan Penerima Disposisi.

Hasil pemeriksaan render:

- Koordinator TU memiliki “Ringkasan Data Sekolah”: ya.
- Koordinator TU memiliki “Selamat datang”: tidak.
- Kepala Sekolah memiliki “Selamat datang”: ya.
- Penerima Disposisi memiliki “Selamat datang”: ya.

## 5. Konfirmasi scope hanya dashboard role ini

Perubahan markup hanya terjadi pada dashboard Koordinator TU melalui partial khusus. Tidak ada perubahan pada layout global dalam pekerjaan ini. Class CSS baru memiliki prefix `role-summary-` dan tidak digunakan halaman atau role lain.

Render bagian `<main>` dashboard Kepala Sekolah dan Penerima Disposisi dibandingkan dengan snapshot sebelum pekerjaan. Setelah normalisasi whitespace, keduanya identik.

Tidak ada perubahan pada halaman surat masuk, surat keluar, pemeriksaan, disposisi, laporan, profil, pengaturan, halaman publik, atau halaman administrasi lainnya.

## 6. Komponen ringkasan yang ditambahkan

- Header ringkas:
  - “TELEMETRI UTAMA”
  - “Ringkasan Data Sekolah”
  - keterangan sumber angka operasional.
- Empat kartu statistik:
  - Surat Masuk
  - Surat Keluar
  - Menunggu ACC
  - Mitra Industri
- Panel “Surat masuk terbaru” dengan:
  - tombol “Lihat semua” menuju route `tu.surat-masuk.index`;
  - kolom Nomor surat, Asal, Tanggal, dan Status;
  - empty state yang aman.
- Panel “Peta operasional” dengan:
  - antrean ACC;
  - persentase menunggu persetujuan;
  - komposisi surat masuk dan surat keluar;
  - tautan ke route laporan yang sudah ada.

## 7. Data yang digunakan

Route dashboard memakai `Route::view` dan tidak mengirim count, koleksi surat, data mitra, atau statistik operasional ke view. Sesuai batasan, tidak dibuat query, controller, model, atau sumber data baru.

Karena data belum tersedia di view:

- Surat Masuk: `0`
- Surat Keluar: `0`
- Menunggu ACC: `0`
- Mitra Industri: `0`
- Antrean ACC dan komposisi dokumen: `0`
- Tabel surat terbaru: empty state

Tidak ada nomor surat, instansi, tanggal, status, atau angka dummy. Bila data operasional ingin ditampilkan kelak, data tersebut perlu dikirim oleh backend; perubahan tersebut berada di luar scope pekerjaan ini.

## 8. Hasil build dan test

Perintah yang dijalankan:

```text
php artisan optimize:clear
php artisan view:cache
php artisan route:list --except-vendor
npm.cmd run build
node --check resources/js/dashboard-navigation.js
php artisan test
```

Hasil:

- Optimasi/cache Laravel berhasil dibersihkan.
- Seluruh Blade berhasil dikompilasi melalui `php artisan view:cache`.
- Route aplikasi tetap 42 route.
- Vite build berhasil: 59 module ditransformasi.
- CSS: 82,17 kB, gzip 17,08 kB.
- JavaScript: 52,76 kB, gzip 19,98 kB.
- Pemeriksaan sintaks JavaScript berhasil.
- Test: 2 test lulus, 2 assertion.
- 25 fixture halaman berhasil dirender tanpa akses database.
- 87 pemeriksaan viewport/browser berhasil.
- Tidak ada overflow halaman pada ukuran yang diperiksa.
- Navigasi mobile, keyboard, dan backdrop tetap bekerja.
- Tidak ada error JavaScript atau aset pada audit browser terakhir.
- Kontrak 24 form lama tetap sama pada audit regresi.
- Dashboard Koordinator TU diperiksa pada 1440, 768, 390, dan 320 px.

Pada mobile, kartu statistik menggunakan dua kolom, panel menjadi satu kolom, dan tabel dapat digeser horizontal. Empty state diletakkan di sisi awal tabel agar langsung terbaca.

## 9. Konfirmasi route dan logic tidak diubah

Tidak ada perubahan pada:

- route;
- controller;
- model;
- migration;
- database;
- query aplikasi;
- API;
- authentication dan authorization;
- middleware;
- role dan permission;
- validasi;
- proses bisnis;
- JavaScript aplikasi.

Hash file di `app/`, `routes/`, `database/`, `config/`, dan `tests/` tetap sama dengan snapshot sebelumnya. `.env`, `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `vite.config.js`, dan `bootstrap/app.php` juga tidak berubah.

## 10. Masalah yang ditemukan

File yang diminta, `public/reference/dashboard-ringkasan.png`, belum tersedia di workspace. Folder `public/reference/` hanya berisi:

- `image.png`
- `image copy.png`
- `image copy 2.png`
- `image copy 3.png`

Implementasi mengikuti spesifikasi tertulis dan bagian “Ringkasan data sekolah” pada `public/reference/image.png`, yang menampilkan struktur statistik, tabel surat terbaru, dan peta operasional yang sama.

Tidak tersedianya data statistik pada route dashboard adalah batasan utama. Nilai nol dan empty state dipilih agar dashboard jujur serta tidak menambah query atau logic backend.
