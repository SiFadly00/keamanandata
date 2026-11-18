<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Ruang Aman</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <main class="login-card">
        <div class="brand-mark" aria-hidden="true">RA</div>
        <p class="eyebrow">SISTEM INFORMASI TERLINDUNGI</p>
        <h1>Selamat datang.</h1>
        <p class="muted">Masuk untuk mengelola informasi terenkripsi.</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" class="stack-form">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="login">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" autocomplete="username" required autofocus>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button type="submit" class="button button-primary button-wide">Masuk ke panel <span aria-hidden="true">→</span></button>
        </form>

        <p class="login-footnote"><span class="lock-icon" aria-hidden="true">●</span> Isi informasi dilindungi enkripsi AES-256-GCM + RSA.</p>
    </main>
</body>
</html>
