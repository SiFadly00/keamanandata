<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel Admin — Ruang Aman</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="index.php"><span class="brand-mark brand-mark-small" aria-hidden="true">RA</span><span>Ruang Aman</span></a>
        <div class="topbar-right">
            <span class="user-label"><span class="status-dot" aria-hidden="true"></span><?= h((string) $adminUsername) ?></span>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="logout">
                <button class="button button-quiet" type="submit">Keluar</button>
            </form>
        </div>
    </header>

    <main class="page-shell">
        <section class="page-heading">
            <div>
                <p class="eyebrow">PANEL ADMIN</p>
                <h1>Informasi Anda, tetap rahasia.</h1>
                <p class="muted">Buat dan kelola konten yang tersimpan dalam bentuk terenkripsi.</p>
            </div>
            <div class="crypto-badge"><span aria-hidden="true">⌑</span> AES-256-GCM <i>+</i> RSA-2048</div>
        </section>

        <?php if ($notice !== null): ?>
            <div class="alert alert-success" role="status"><?= h($notice) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error" role="alert"><?= h($error) ?></div>
        <?php endif; ?>

        <section class="content-grid">
            <aside class="panel editor-panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">EDITOR</p>
                        <h2>Tambah informasi</h2>
                    </div>
                    <span class="heading-icon" aria-hidden="true">＋</span>
                </div>
                <p class="muted small">Judul, kategori, dan isi akan dienkripsi sebelum disimpan ke database.</p>
                <form method="post" class="stack-form">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="">
                    <label for="title">Judul informasi</label>
                    <input id="title" name="title" maxlength="160" placeholder="Contoh: Jadwal pemeliharaan" required>
                    <label for="category">Kategori</label>
                    <input id="category" name="category" maxlength="60" placeholder="Contoh: Pengumuman" required>
                    <label for="body">Isi informasi</label>
                    <textarea id="body" name="body" rows="7" maxlength="20000" placeholder="Tuliskan informasi di sini..." required></textarea>
                    <button class="button button-primary button-wide" type="submit">Simpan dengan aman <span aria-hidden="true">→</span></button>
                </form>
            </aside>

            <section class="content-list" aria-labelledby="content-heading">
                <div class="list-heading">
                    <div>
                        <p class="eyebrow">PENYIMPANAN</p>
                        <h2 id="content-heading">Konten tersimpan <span class="count"><?= count($contents) ?></span></h2>
                    </div>
                    <span class="encrypted-label"><span aria-hidden="true">●</span> Terenkripsi</span>
                </div>

                <?php if ($contents === []): ?>
                    <div class="empty-state">
                        <div class="empty-icon" aria-hidden="true">▤</div>
                        <h3>Belum ada informasi</h3>
                        <p class="muted">Informasi pertama yang Anda simpan akan tampil di sini.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($contents as $content): ?>
                        <article class="content-card">
                            <div class="content-card-top">
                                <span class="category-tag"><?= h($content['category']) ?></span>
                                <span class="content-date"><?= h(date('d M Y', strtotime($content['updated_at']))) ?></span>
                            </div>
                            <h3><?= h($content['title']) ?></h3>
                            <p class="content-body"><?= nl2br(h($content['body'])) ?></p>
                            <details class="edit-details">
                                <summary>Edit informasi</summary>
                                <form method="post" class="stack-form edit-form">
                                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="save">
                                    <input type="hidden" name="id" value="<?= (int) $content['id'] ?>">
                                    <label for="title-<?= (int) $content['id'] ?>">Judul</label>
                                    <input id="title-<?= (int) $content['id'] ?>" name="title" maxlength="160" value="<?= h($content['title']) ?>" required>
                                    <label for="category-<?= (int) $content['id'] ?>">Kategori</label>
                                    <input id="category-<?= (int) $content['id'] ?>" name="category" maxlength="60" value="<?= h($content['category']) ?>" required>
                                    <label for="body-<?= (int) $content['id'] ?>">Isi</label>
                                    <textarea id="body-<?= (int) $content['id'] ?>" name="body" rows="5" maxlength="20000" required><?= h($content['body']) ?></textarea>
                                    <button class="button button-primary" type="submit">Perbarui konten</button>
                                </form>
                            </details>
                            <details class="cipher-details">
                                <summary>Lihat bukti data terenkripsi</summary>
                                <p class="cipher-caption">Nilai berikut dibaca langsung dari kolom terenkripsi di database.</p>
                                <div class="cipher-field">
                                    <span>Ciphertext konten (AES-256-GCM, Base64)</span>
                                    <pre><?= h($content['ciphertext']) ?></pre>
                                </div>
                                <div class="cipher-field">
                                    <span>Kunci AES terbungkus (RSA-OAEP, Base64)</span>
                                    <pre><?= h($content['wrapped_key']) ?></pre>
                                </div>
                                <div class="cipher-meta">
                                    <div class="cipher-field">
                                        <span>Nonce</span>
                                        <pre><?= h($content['nonce']) ?></pre>
                                    </div>
                                    <div class="cipher-field">
                                        <span>Authentication tag</span>
                                        <pre><?= h($content['auth_tag']) ?></pre>
                                    </div>
                                </div>
                            </details>
                            <form method="post" class="delete-form" onsubmit="return confirm('Yakin ingin menghapus informasi ini?')">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $content['id'] ?>">
                                <button class="button button-danger" type="submit">Hapus</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </section>

        <footer class="security-note">
            <span class="note-icon" aria-hidden="true">✓</span>
            <p><strong>Enkripsi hibrida aktif.</strong> Isi konten dienkripsi AES-256-GCM. Kunci AES unik untuk setiap konten dienkripsi dengan kunci publik RSA; hanya server dengan kunci privat yang dapat membukanya.</p>
        </footer>
    </main>
</body>
</html>
