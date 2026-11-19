<?php
/**
 * @var list<array{id: int, ciphertext: string, nonce: string, auth_tag: string, wrapped_key: string, created_at: string, updated_at: string}> $contents
 */
$sampleCiphertext = $contents[0]['ciphertext'] ?? base64_encode(random_bytes(24));
$ciphertextFragment = static function (string $ciphertext, int $length, int $offset = 0): string {
    $ciphertextLength = strlen($ciphertext);
    if ($ciphertextLength === 0) {
        throw new RuntimeException('Data terenkripsi kosong.');
    }

    $offset %= $ciphertextLength;
    $fragment = substr($ciphertext, $offset, $length);

    if (strlen($fragment) < $length) {
        $fragment .= substr($ciphertext, 0, $length - strlen($fragment));
    }

    return $fragment;
};
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($ciphertextFragment($sampleCiphertext, 12)) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="index.php?page=admin" aria-label="<?= h($ciphertextFragment($sampleCiphertext, 12, 12)) ?>">
            <span class="brand-mark brand-mark-small" aria-hidden="true">🔒</span>
            <span><?= h($ciphertextFragment($sampleCiphertext, 16, 24)) ?></span>
        </a>
        <div class="topbar-right">
            <span class="user-label" aria-label="<?= h($ciphertextFragment($sampleCiphertext, 12, 40)) ?>">🔒</span>
            <a class="button button-quiet" href="index.php?page=unlock" aria-label="<?= h($ciphertextFragment($sampleCiphertext, 12, 52)) ?>">🔑</a>
        </div>
    </header>

    <main class="page-shell">
        <section class="page-heading" aria-label="<?= h($ciphertextFragment($sampleCiphertext, 12, 64)) ?>">
            <div>
                <p class="eyebrow"><?= h($ciphertextFragment($sampleCiphertext, 12, 76)) ?></p>
                <h1><?= h($ciphertextFragment($sampleCiphertext, 24, 88)) ?></h1>
                <p class="muted"><?= h($ciphertextFragment($sampleCiphertext, 36, 112)) ?></p>
            </div>
            <div class="crypto-badge" aria-label="<?= h($ciphertextFragment($sampleCiphertext, 12, 148)) ?>">🔒 <?= h($ciphertextFragment($sampleCiphertext, 16, 160)) ?></div>
        </section>

        <section class="content-list" aria-label="<?= h($ciphertextFragment($sampleCiphertext, 12, 176)) ?>">
            <?php if ($contents === []): ?>
                <div class="empty-state" aria-label="<?= h($ciphertextFragment($sampleCiphertext, 12, 188)) ?>">
                    <div class="empty-icon" aria-hidden="true">🔒</div>
                    <h2><?= h($ciphertextFragment($sampleCiphertext, 20, 200)) ?></h2>
                </div>
            <?php else: ?>
                <?php foreach ($contents as $content): ?>
                    <article class="content-card">
                        <div class="content-card-top">
                            <span class="category-tag"><?= h($ciphertextFragment($content['ciphertext'], 12, 12)) ?></span>
                            <span class="content-date"><?= h($ciphertextFragment($content['ciphertext'], 16, 24)) ?></span>
                        </div>
                        <h2><?= h($ciphertextFragment($content['ciphertext'], 24, 40)) ?></h2>
                        <div class="cipher-field">
                            <pre><?= h($content['ciphertext']) ?></pre>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
