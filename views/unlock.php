<?php
/**
 * @var string $error
 */
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buka konten — Ruang Aman</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <main class="login-card">
        <a class="brand" href="index.php" aria-label="Kembali ke dashboard">
            <span class="brand-mark" aria-hidden="true">RA</span>
        </a>
        <p class="eyebrow">BUKA KUNCI KONTEN</p>
        <h1>Masukkan private key.</h1>
        <p class="muted">Private key yang cocok akan membuka judul, kategori, dan isi konten.</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="unlock">
            <label for="private-key">Private key (PEM)</label>
            <p class="muted small">Tempel seluruh isi file, termasuk baris BEGIN dan END.</p>
            <textarea id="private-key" name="private_key" rows="10" autocomplete="off" spellcheck="false" placeholder="-----BEGIN PRIVATE KEY-----" required autofocus></textarea>
            <button type="submit" class="button button-primary button-wide">Buka konten <span aria-hidden="true">→</span></button>
        </form>

        <p class="login-footnote"><a href="index.php">Kembali ke halaman utama</a></p>
    </main>
</body>
</html>
