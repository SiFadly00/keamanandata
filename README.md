# Ruang Aman

Aplikasi sistem informasi sederhana berbasis PHP untuk mengelola konten admin dengan enkripsi hibrida. Judul, kategori, dan isi konten dienkripsi sebagai satu record sebelum masuk ke `db_keamanan`.

## Teknologi keamanan

- **AES-256-GCM** mengenkripsi konten dan mendeteksi perubahan data.
- Setiap konten memakai kunci AES acak tersendiri.
- **RSA-2048 (OAEP)** mengenkripsi kunci AES; private key hanya digunakan server saat membuka konten.
- Password admin disimpan dengan `password_hash()`, query menggunakan PDO prepared statements, dan formulir memakai token CSRF.
- Buka bagian **“Lihat bukti data terenkripsi”** pada kartu konten di dashboard untuk menunjukkan ciphertext dan kunci AES terbungkus RSA yang tersimpan di database.

## Persiapan

1. Pastikan PHP 8.1+ memiliki ekstensi `pdo_mysql` dan `openssl`, serta MySQL/MariaDB berjalan.
2. Impor `database/schema.sql` ke server database. Skema tersebut membuat database `db_keamanan` jika belum tersedia.
3. Sesuaikan koneksi database melalui environment variable `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, dan `DB_PASSWORD`. Nilai default cocok untuk Laragon standar (`127.0.0.1`, port `3306`, user `root`, tanpa password).
4. Dari folder proyek, buat pasangan kunci RSA:

   ```sh
   php scripts/generate_keys.php
   ```

5. Buat akun admin. Jalankan perintah berikut di terminal dan masukkan username serta password minimal 12 karakter saat diminta:

   ```sh
   php scripts/create_admin.php
   ```

6. Jalankan server pengembangan dengan folder `public` sebagai document root:

   ```sh
   php -S 127.0.0.1:8000 -t public
   ```

   Buka `http://127.0.0.1:8000`. Pada Apache/Laragon, arahkan document root virtual host ke folder `public`; aturan `.htaccess` juga membatasi akses langsung ke folder internal.

## Catatan kunci

Simpan `storage/keys/private.pem` sebagai rahasia dan buat cadangan di lokasi aman. Tanpa private key, konten lama tidak dapat didekripsi. Jangan menimpa atau membuat ulang pasangan kunci setelah konten disimpan.

## Pengujian

```sh
php tests/crypto_test.php
```

Kode program yang digunakan untuk laporan tugas berada di folder `app/`, `public/`, `views/`, `scripts/`, dan `database/`.

Untuk melihat nilai terenkripsi langsung di HeidiSQL/phpMyAdmin, jalankan query baca-saja berikut setelah menyimpan konten:

```sql
SELECT id, ciphertext, nonce, auth_tag, wrapped_key
FROM admin_contents;
```
