# Ruang Aman

Aplikasi sistem informasi sederhana berbasis PHP untuk mengelola konten admin dengan enkripsi hibrida. Judul, kategori, dan isi konten dienkripsi sebagai satu record sebelum masuk ke `db_keamanan`.

## Teknologi keamanan

- **AES-256-GCM** mengenkripsi konten dan mendeteksi perubahan data.
- Setiap konten memakai kunci AES acak tersendiri.
- **RSA-2048 (OAEP)** mengenkripsi kunci AES. Halaman admin memerlukan private key yang cocok untuk membuka tampilan dan mengelola konten.
- Halaman utama menampilkan semua konten dalam bentuk terbaca tanpa meminta key. Karena itu, isi database menjadi informasi publik bagi siapa pun yang mengakses situs.
- Query database menggunakan PDO prepared statements dan formulir memakai token CSRF.

## Persiapan

1. Pastikan PHP 8.1+ memiliki ekstensi `pdo_mysql` dan `openssl`, serta MySQL/MariaDB berjalan.
2. Impor `database/schema.sql` ke server database. Skema tersebut membuat database `db_keamanan` jika belum tersedia.
3. Sesuaikan koneksi database melalui environment variable `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, dan `DB_PASSWORD`. Nilai default cocok untuk Laragon standar (`127.0.0.1`, port `3306`, user `root`, tanpa password).
4. Dari folder proyek, buat pasangan kunci RSA:

   ```sh
   php scripts/generate_keys.php
   ```

5. Jalankan server pengembangan dengan folder `public` sebagai document root:

   ```sh
   php -S 127.0.0.1:8000 -t public
   ```

   Buka `http://127.0.0.1:8000`. Pada Apache/Laragon, arahkan document root virtual host ke folder `public`; aturan `.htaccess` juga membatasi akses langsung ke folder internal.

## Membuka konten dan catatan kunci

Saat membuka situs, halaman utama menampilkan konten dari database dalam bentuk terbaca. Gunakan tautan **Admin** untuk menuju halaman admin yang terkunci, lalu tombol kunci untuk membuka halaman input private key. Tempel seluruh isi `storage/keys/private.pem` di sana. Private key disimpan sementara di sesi server selama admin terbuka dan dihapus dari sesi saat dikunci atau sesi berakhir.

Halaman utama mendekripsi konten otomatis di server agar dapat ditampilkan tanpa input key. Dengan demikian, konten yang sama dapat dibaca secara publik; jangan gunakan untuk data rahasia. Simpan `storage/keys/private.pem` sebagai rahasia dan buat cadangan di lokasi aman. Jangan menimpa atau membuat ulang pasangan kunci setelah konten disimpan.

## Pengujian

```sh
php tests/crypto_test.php
```

Kode program yang digunakan untuk laporan tugas berada di folder `app/`, `public/`, `views/`, `scripts/`, dan `database/`.
