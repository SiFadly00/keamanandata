<?php
/**
 * @var list<array{id: int, title: string, category: string, body: string, ciphertext: string, nonce: string, auth_tag: string, wrapped_key: string, created_at: string, updated_at: string}> $contents
 */
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ruang Aman</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="index.php"><span class="brand-mark brand-mark-small" aria-hidden="true">RA</span><span>Ruang Aman</span></a>
        <div class="topbar-right">
            <a class="button button-quiet" href="index.php?page=admin">Admin</a>
        </div>
    </header>

    <main class="page-shell">
        <section class="page-heading">
            <div>
                <p class="eyebrow">INFORMASI</p>
                <h1>Informasi terbaru</h1>
                <p class="muted">Informasi yang tersedia saat ini.</p>
            </div>
        </section>

        <section class="content-list" aria-label="Informasi">
            <?php if ($contents === []): ?>
                <div class="empty-state">
                    <div class="empty-icon" aria-hidden="true">▤</div>
                    <h2>Belum ada informasi</h2>
                    <p class="muted">Informasi akan ditampilkan di sini setelah tersedia.</p>
                </div>
            <?php else: ?>
                <?php foreach ($contents as $content): ?>
                    <article class="content-card">
                        <div class="content-card-top">
                            <span class="category-tag"><?= h($content['category']) ?></span>
                            <span class="content-date"><?= h(date('d M Y', strtotime($content['updated_at']))) ?></span>
                        </div>
                        <h2><?= h($content['title']) ?></h2>
                        <p class="content-body"><?= nl2br(h($content['body'])) ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
