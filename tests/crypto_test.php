<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/CryptoService.php';

$directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ruang-aman-test-' . bin2hex(random_bytes(8));
try {
    CryptoService::generateKeyPair($directory);
    $crypto = new CryptoService($directory . DIRECTORY_SEPARATOR . 'public.pem');
    $record = [
        'title' => 'Pengumuman rahasia',
        'category' => 'Internal',
        'body' => 'Isi dengan karakter Indonesia: keamanan data.',
    ];
    $encrypted = $crypto->encryptRecord($record);

    foreach ($record as $value) {
        if (str_contains($encrypted['ciphertext'], $value)) {
            throw new RuntimeException('Nilai konten tidak boleh terlihat pada ciphertext.');
        }
    }

    $locked = false;
    try {
        $crypto->decryptRecord($encrypted);
    } catch (RuntimeException $exception) {
        $locked = true;
    }
    if (!$locked) {
        throw new RuntimeException('Dekripsi seharusnya memerlukan private key.');
    }

    $crypto->unlock((string) file_get_contents($directory . DIRECTORY_SEPARATOR . 'private.pem'));
    if ($crypto->decryptRecord($encrypted) !== $record) {
        throw new RuntimeException('Hasil dekripsi tidak sama dengan konten awal.');
    }

    $wrongKeyDirectory = $directory . DIRECTORY_SEPARATOR . 'wrong-key';
    CryptoService::generateKeyPair($wrongKeyDirectory);
    $wrongKeyRejected = false;
    try {
        $crypto->unlock((string) file_get_contents($wrongKeyDirectory . DIRECTORY_SEPARATOR . 'private.pem'));
    } catch (RuntimeException $exception) {
        $wrongKeyRejected = true;
    }
    if (!$wrongKeyRejected) {
        throw new RuntimeException('Private key dari pasangan berbeda seharusnya ditolak.');
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
    foreach ([
        $directory . DIRECTORY_SEPARATOR . 'private.pem',
        $directory . DIRECTORY_SEPARATOR . 'public.pem',
        $directory . DIRECTORY_SEPARATOR . 'wrong-key' . DIRECTORY_SEPARATOR . 'private.pem',
        $directory . DIRECTORY_SEPARATOR . 'wrong-key' . DIRECTORY_SEPARATOR . 'public.pem',
    ] as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }
    $wrongKeyDirectory = $directory . DIRECTORY_SEPARATOR . 'wrong-key';
    if (is_dir($wrongKeyDirectory)) {
        rmdir($wrongKeyDirectory);
    }
    if (is_dir($directory)) {
        rmdir($directory);
    }
}
