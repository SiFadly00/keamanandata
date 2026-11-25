# Ruang Aman

Aplikasi sistem informasi sederhana berbasis PHP untuk mengelola konten admin dengan enkripsi hibrida. Judul, kategori, dan isi konten dienkripsi sebagai satu record sebelum masuk ke `db_keamanan`.

## Teknologi keamanan

- **AES-256-GCM** mengenkripsi konten dan mendeteksi perubahan data.
- Setiap konten memakai kunci AES acak tersendiri.
- **RSA-2048 (OAEP)** mengenkripsi kunci AES. Private key yang cocok diperlukan untuk membaca, mengubah, atau menghapus konten yang sudah tersimpan; penambahan record baru tetap bisa dilakukan saat admin terkunci.
- Halaman utama merupakan halaman sambutan statis dan tidak membaca atau menampilkan data database.
- Halaman admin mempertahankan tata letak yang sama saat terkunci maupun terbuka. Saat terkunci, teks antarmuka ditampilkan sebagai token acak alfanumerik dan konten berupa ciphertext. Penambahan konten baru tetap tersedia dan langsung dienkripsi; membaca, mengubah, atau menghapus konten yang sudah tersimpan memerlukan private key.
- Query database menggunakan PDO prepared statements dan formulir memakai token CSRF.

## Skema jaringan dan alur data

```text
+--------------------+       HTTP atau HTTPS       +-------------------------+
| Browser pengguna   | --------------------------> | Web server + aplikasi   |
| (localhost)        | <-------------------------- | PHP / CryptoService     |
+--------------------+                              +------------+------------+
                                                               |
                                                   koneksi MySQL lokal
                                                               |
                                                               v
                                                    +----------------------+
                                                    | MySQL: db_keamanan   |
                                                    | admin_contents       |
                                                    +----------------------+
```

Alur saat menambah konten:

1. Browser mengirim formulir ke aplikasi PHP. Dengan HTTP, isi formulir dapat dibaca pada jaringan; dengan HTTPS, isi request dilindungi TLS selama perjalanan ke web server.
2. `CryptoService` menyatukan judul, kategori, dan isi menjadi satu record JSON, lalu mengenkripsinya menggunakan AES-256-GCM.
3. Kunci AES unik untuk record tersebut dibungkus menggunakan kunci publik RSA dengan OAEP.
4. Aplikasi menyimpan `ciphertext`, `nonce`, `auth_tag`, dan `wrapped_key` ke tabel `admin_contents`. Database tidak menyimpan judul, kategori, dan isi sebagai teks biasa.

Alur saat membaca konten:

1. Admin mengirim private key melalui halaman verifikasi. Aplikasi memeriksa bahwa pasangan kuncinya cocok dengan kunci publik.
2. Aplikasi mengambil record terenkripsi, membuka kunci AES dengan private key RSA, lalu mendekripsi record dan memverifikasi authentication tag AES-GCM.
3. Konten yang sudah didekripsi ditampilkan pada halaman admin. Jika key salah, admin tetap terkunci.

Token acak pada teks antarmuka admin terkunci hanya mengaburkan tampilan. Perlindungan isi data yang sebenarnya berasal dari enkripsi AES/RSA. Enkripsi aplikasi dilakukan di server setelah request diterima; karena itu, HTTPS tetap diperlukan untuk melindungi data formulir dan private key selama transmisi.

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

   Buka `http://127.0.0.1:8000`. Server bawaan `php -S` ini menggunakan HTTP, bukan HTTPS. Pada Apache/Laragon, arahkan document root virtual host ke folder `public`; aturan `.htaccess` juga membatasi akses langsung ke folder internal. Untuk HTTPS, aktifkan SSL pada web server dan gunakan sertifikat lokal yang dipercaya browser; mengganti URL menjadi `https://` saja tidak mengaktifkan TLS.

## Bukti enkripsi database dan packet sniffing

Bagian ini adalah prosedur untuk mengambil bukti tugas. README ini tidak menyertakan hasil capture Wireshark; ambil screenshot atau file capture sendiri saat menjalankan pengujian.

### 1. Siapkan pengujian yang aman

- Gunakan konten contoh dan, bila perlu, pasangan key khusus untuk pengujian.
- Jangan mengirim private key asli melalui HTTP dan jangan memasukkan key atau data pribadi ke screenshot/capture yang akan dibagikan.
- Jangan menjalankan `scripts/generate_keys.php` pada proyek yang sudah memiliki data penting tanpa membuat backup: pembuatan pasangan baru dapat membuat konten lama tidak bisa didekripsi.

### 2. Amati HTTP lokal dengan Wireshark

Untuk demonstrasi di mesin Windows, pilih interface loopback Npcap (misalnya **Adapter for loopback traffic capture**) agar koneksi ke `127.0.0.1` tertangkap. Jalankan server pengembangan di atas, mulai capture, lalu gunakan data uji untuk mengirim formulir. Contoh display filter:

```text
tcp.port == 8000
```

Pada HTTP, Wireshark mungkin dapat menampilkan request formulir dalam bentuk terbaca, termasuk data uji yang dikirim ke PHP. Ini menunjukkan bahwa HTTP tidak melindungi data selama transmisi. Jangan gunakan private key atau data sungguhan untuk demonstrasi ini.

### 3. Buktikan data yang tersimpan di database

Setelah mengirim konten uji, jalankan query berikut pada database:

```sql
SELECT id, ciphertext, nonce, auth_tag, wrapped_key
FROM admin_contents
ORDER BY id DESC;
```

Lampirkan hasil yang menunjukkan nilai `ciphertext` berbeda dari teks contoh yang dimasukkan. Jangan menyertakan private key atau konten sensitif pada bukti.

### 4. Bandingkan dengan HTTPS

Aktifkan HTTPS pada Apache/Laragon atau web server lain menggunakan sertifikat lokal yang dipercaya browser, lalu akses situs melalui URL `https://` yang sesuai. Ulangi capture pada interface yang sama. Wireshark seharusnya menampilkan handshake dan paket TLS, bukan isi formulir sebagai teks biasa. PHP tetap menerima dan memproses request setelah TLS berakhir di web server.

Server bawaan `php -S` pada bagian persiapan tidak menyediakan HTTPS secara langsung. Jangan menganggap koneksi aman hanya karena alamat yang diketik diawali `https://`; web server harus benar-benar dikonfigurasi untuk TLS.

### Bukti yang dapat dimasukkan ke laporan

- Diagram alur browser, web server/PHP, dan MySQL dari bagian skema jaringan.
- Tampilan admin terkunci dan tampilan setelah private key uji yang cocok diverifikasi.
- Hasil query database yang memperlihatkan kolom ciphertext dan parameter enkripsinya.
- Capture HTTP dengan data uji untuk menunjukkan risiko sniffing tanpa TLS.
- Capture HTTPS yang menunjukkan komunikasi berupa paket TLS, bukan isi formulir terbaca.

## Membuka konten dan catatan kunci

Saat membuka situs, halaman utama menampilkan sambutan statis. Gunakan tautan **Halaman admin** untuk membuka panel. Dalam keadaan terkunci, susunan panel tetap sama, tetapi teks antarmukanya dikaburkan dengan token acak dan data ditampilkan sebagai ciphertext. Formulir tambah konten tetap dapat digunakan; record baru dienkripsi dengan kunci publik. Untuk membaca, mengubah, atau menghapus konten yang sudah tersimpan, gunakan tombol akses untuk membuka halaman input private key tersendiri. Tempel seluruh isi file private key pada lingkungan uji yang aman dan kirim untuk diverifikasi. Setelah berhasil, konten dapat dibaca dan dikelola di halaman admin. Private key disimpan sementara di sesi server selama admin terbuka dan dihapus dari sesi saat dikunci atau sesi berakhir.

Simpan `storage/keys/private.pem` sebagai rahasia dan buat cadangan di lokasi aman. Jangan menyalin private key ke laporan atau capture jaringan. Jangan menimpa atau membuat ulang pasangan kunci setelah konten disimpan.

## Pengujian

```sh
php tests/crypto_test.php
```

Kode program yang digunakan untuk laporan tugas berada di folder `app/`, `public/`, `views/`, `scripts/`, dan `database/`.
