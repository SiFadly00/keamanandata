<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selamat datang — Ruang Aman</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="index.php"><span class="brand-mark brand-mark-small" aria-hidden="true">RA</span><span>Ruang Aman</span></a>
        <div class="topbar-right">
            <a class="button button-quiet" href="index.php?page=admin">Halaman admin</a>
        </div>
    </header>

    <main class="welcome-shell">
        <section class="welcome-hero">
            <div class="welcome-copy">
                <p class="eyebrow">SELAMAT DATANG DI RUANG AMAN</p>
                <h1>Privasi informasi, dimulai dari enkripsi.</h1>
                <p class="welcome-description">Ruang Aman adalah demonstrasi pengelolaan informasi dengan enkripsi hibrida. Konten admin disimpan sebagai ciphertext dan hanya dapat dibuka dengan private key yang sesuai.</p>
                <a class="button button-primary welcome-button" href="index.php?page=admin">Buka halaman admin <span aria-hidden="true">→</span></a>
                <p class="welcome-hint">Halaman ini merupakan pengantar dan tidak menampilkan data dari database.</p>
            </div>
            <div class="welcome-visual" aria-hidden="true">
                <div class="welcome-orbit orbit-outer"></div>
                <div class="welcome-orbit orbit-inner"></div>
                <div class="welcome-lock"><span>RA</span></div>
                <div class="welcome-chip chip-one">AES-256-GCM</div>
                <div class="welcome-chip chip-two">RSA-2048</div>
            </div>
        </section>

        <section class="welcome-features" aria-label="Fitur keamanan">
            <article class="welcome-feature">
                <span class="feature-number">01</span>
                <h2>Enkripsi konten</h2>
                <p>Judul, kategori, dan isi digabung lalu dienkripsi sebelum disimpan.</p>
            </article>
            <article class="welcome-feature">
                <span class="feature-number">02</span>
                <h2>Kunci unik</h2>
                <p>Setiap record memakai kunci AES tersendiri yang dilindungi RSA.</p>
            </article>
            <article class="welcome-feature">
                <span class="feature-number">03</span>
                <h2>Akses terverifikasi</h2>
                <p>Konten admin hanya terbuka setelah private key yang cocok diverifikasi.</p>
            </article>
        </section>
    </main>
</body>
</html>
