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
    <title>Buka admin — Ruang Aman</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <main class="login-card">
        <a class="brand" href="index.php?page=admin" aria-label="Kembali ke halaman admin">
            <span class="brand-mark" aria-hidden="true">RA</span>
        </a>
        <p class="eyebrow">VERIFIKASI PRIVATE KEY</p>
        <h1>Buka halaman admin.</h1>
        <p class="muted">Masukkan private key yang cocok. Setelah diverifikasi, tulisan dan konten admin akan ditampilkan.</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?page=unlock" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="unlock">
            <label for="private-key">Private key (PEM)</label>
            <p class="muted small">Tempel seluruh isi file, termasuk baris BEGIN dan END.</p>
            <textarea id="private-key" name="private_key" rows="10" autocomplete="off" spellcheck="false" placeholder="-----BEGIN PRIVATE KEY-----" required autofocus></textarea>
            <button type="submit" class="button button-primary button-wide">Verifikasi dan buka <span aria-hidden="true">→</span></button>
        </form>

        <p class="login-footnote"><a href="index.php?page=admin">Kembali ke halaman admin</a></p>
    </main>
</body>
</html>
