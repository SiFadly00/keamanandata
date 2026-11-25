<?php
/**
 * @var list<array<string, int|string>> $contents
 * @var string|null $notice
 * @var string $error
 * @var bool $unlocked
 */
$obscure = static fn (int $length = 10): string => strtoupper(
    substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length)
);
$display = static fn (string $text): string => h(
    $unlocked ? $text : $obscure(max(4, strlen($text)))
);
$ciphertextHex = static function (string $ciphertext): string {
    $decoded = base64_decode($ciphertext, true);
    if ($decoded === false) {
        throw new RuntimeException('Data terenkripsi rusak atau formatnya tidak valid.');
    }

    return strtoupper(bin2hex($decoded));
};
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $unlocked ? 'Admin — Ruang Aman' : h($obscure(12)) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="index.php" aria-label="<?= $display('Ruang Aman') ?>">
            <?php if ($unlocked): ?>
                <span class="brand-mark brand-mark-small" aria-hidden="true">RA</span>
            <?php endif; ?>
            <span><?= $display('Ruang Aman') ?></span>
        </a>
        <div class="topbar-right">
            <?php if ($unlocked): ?>
                <span class="user-label">Data terbuka</span>
                <form method="post" action="index.php?page=admin">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="lock">
                    <button class="button button-quiet" type="submit">Kunci data</button>
                </form>
            <?php else: ?>
                <a class="button button-quiet" href="index.php?page=unlock"><?= $display('Buka akses') ?></a>
                <span class="user-label"><?= $display('Data terenkripsi') ?></span>
            <?php endif; ?>
        </div>
    </header>

    <main class="page-shell">
        <section class="page-heading">
            <div>
                <p class="eyebrow"><?= $display('PANEL ADMIN') ?></p>
                <h1><?= $display('Informasi Anda, tetap rahasia.') ?></h1>
                <p class="muted"><?= $display('Buat dan kelola konten yang tersimpan dalam bentuk terenkripsi.') ?></p>
            </div>
            <div class="crypto-badge"><?= $display('AES-256-GCM + RSA-2048') ?></div>
        </section>

        <?php if ($notice !== null): ?>
            <div class="alert alert-success" role="status"><?= $display($notice) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= $display($error) ?></div>
        <?php endif; ?>

        <section class="content-grid">
            <aside class="panel editor-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow"><?= $display('EDITOR') ?></p>
                        <h2><?= $display('Tambah informasi') ?></h2>
                    </div>
                    <?php if ($unlocked): ?>
                        <span class="heading-icon" aria-hidden="true">＋</span>
                    <?php else: ?>
                        <span class="heading-icon"><?= h($obscure()) ?></span>
                    <?php endif; ?>
                </div>
                <p class="muted small"><?= $display('Judul, kategori, dan isi akan dienkripsi sebelum disimpan ke database.') ?></p>
                <form method="post" action="index.php?page=admin" class="stack-form">
                    <fieldset class="editor-fields">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="">
                        <label for="title"><?= $display('Judul informasi') ?></label>
                        <input id="title" name="title" maxlength="160" placeholder="<?= $display('Contoh: Jadwal pemeliharaan') ?>" required>
                        <label for="category"><?= $display('Kategori') ?></label>
                        <input id="category" name="category" maxlength="60" placeholder="<?= $display('Contoh: Pengumuman') ?>" required>
                        <label for="body"><?= $display('Isi informasi') ?></label>
                        <textarea id="body" name="body" rows="7" maxlength="20000" placeholder="<?= $display('Tuliskan informasi di sini...') ?>" required></textarea>
                        <button class="button button-primary button-wide" type="submit"><?= $display('Simpan dengan aman') ?></button>
                    </fieldset>
                </form>
            </aside>

            <section class="content-list" aria-labelledby="content-heading">
                <div class="list-heading">
                    <div>
                        <p class="eyebrow"><?= $display('PENYIMPANAN') ?></p>
                        <h2 id="content-heading"><?= $display('Konten tersimpan') ?> <span class="count"><?= count($contents) ?></span></h2>
                    </div>
                    <span class="encrypted-label"><?= $display('Terenkripsi') ?></span>
                </div>

                <?php if ($contents === []): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><?= $unlocked ? '▤' : h($obscure()) ?></div>
                        <h3><?= $display('Belum ada informasi') ?></h3>
                        <p class="muted"><?= $display('Informasi pertama yang Anda simpan akan tampil di sini.') ?></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($contents as $content): ?>
                        <?php $encryptedHex = $unlocked ? '' : $ciphertextHex((string) $content['ciphertext']); ?>
                        <article class="content-card">
                            <div class="content-card-top">
                                <span class="category-tag"><?= $unlocked ? h((string) $content['category']) : h($obscure()) ?></span>
                                <span class="content-date"><?= $unlocked ? h(date('d M Y', strtotime((string) $content['updated_at']))) : h($obscure(8)) ?></span>
                            </div>
                            <h3><?= $unlocked ? h((string) $content['title']) : h($obscure()) ?></h3>
                            <p class="content-body"><?= $unlocked ? nl2br(h((string) $content['body'])) : h($encryptedHex) ?></p>
                            <details class="edit-details">
                                <summary><?= $display('Edit informasi') ?></summary>
                                <form method="post" action="index.php?page=admin" class="stack-form edit-form">
                                    <fieldset class="editor-fields" <?= $unlocked ? '' : 'disabled' ?>>
                                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="save">
                                        <input type="hidden" name="id" value="<?= (int) $content['id'] ?>">
                                        <label for="title-<?= (int) $content['id'] ?>"><?= $display('Judul') ?></label>
                                        <input id="title-<?= (int) $content['id'] ?>" name="title" maxlength="160" value="<?= $unlocked ? h((string) $content['title']) : h($obscure()) ?>" required>
                                        <label for="category-<?= (int) $content['id'] ?>"><?= $display('Kategori') ?></label>
                                        <input id="category-<?= (int) $content['id'] ?>" name="category" maxlength="60" value="<?= $unlocked ? h((string) $content['category']) : h($obscure()) ?>" required>
                                        <label for="body-<?= (int) $content['id'] ?>"><?= $display('Isi') ?></label>
                                        <textarea id="body-<?= (int) $content['id'] ?>" name="body" rows="5" maxlength="20000" required><?= $unlocked ? h((string) $content['body']) : h($obscure(20)) ?></textarea>
                                        <button class="button button-primary" type="submit"><?= $display('Perbarui konten') ?></button>
                                    </fieldset>
                                </form>
                            </details>
                            <form method="post" action="index.php?page=admin" class="delete-form"<?= $unlocked ? ' onsubmit="return confirm(\'Yakin ingin menghapus informasi ini?\')"' : '' ?>>
                                <fieldset class="editor-fields" <?= $unlocked ? '' : 'disabled' ?>>
                                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $content['id'] ?>">
                                    <button class="button button-danger" type="submit"><?= $display('Hapus') ?></button>
                                </fieldset>
                            </form>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </section>

        <footer class="security-note">
            <span class="note-icon"><?= $unlocked ? '✓' : h($obscure()) ?></span>
            <p><strong><?= $display('Enkripsi hibrida aktif.') ?></strong> <?= $display('Judul, kategori, dan isi disimpan bersama dalam ciphertext AES-256-GCM. Kunci AES unik dilindungi RSA.') ?></p>
        </footer>
    </main>
</body>
</html>
