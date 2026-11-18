<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/CryptoService.php';

$directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ruang-aman-test-' . bin2hex(random_bytes(8));
try {
    CryptoService::generateKeyPair($directory);
    $crypto = new CryptoService(
        $directory . DIRECTORY_SEPARATOR . 'public.pem',
        $directory . DIRECTORY_SEPARATOR . 'private.pem'
    );
    $record = [
        'title' => 'Pengumuman rahasia',
        'category' => 'Internal',
        'body' => 'Isi dengan karakter Indonesia: keamanan data.',
    ];
    $encrypted = $crypto->encryptRecord($record);

    if ($crypto->decryptRecord($encrypted) !== $record) {
        throw new RuntimeException('Hasil dekripsi tidak sama dengan konten awal.');
    }

    $tampered = $encrypted;
    $tampered['ciphertext'] = base64_encode('konten yang diubah');
    $rejected = false;
    try {
        $crypto->decryptRecord($tampered);
    } catch (RuntimeException $exception) {
        $rejected = true;
    }
    if (!$rejected) {
        throw new RuntimeException('Konten yang diubah seharusnya gagal diverifikasi.');
    }

    fwrite(STDOUT, "CryptoService: round-trip dan deteksi perubahan berhasil.\n");
} finally {
    foreach (['private.pem', 'public.pem'] as $file) {
        $path = $directory . DIRECTORY_SEPARATOR . $file;
        if (is_file($path)) {
            unlink($path);
        }
    }
    if (is_dir($directory)) {
        rmdir($directory);
    }
}
