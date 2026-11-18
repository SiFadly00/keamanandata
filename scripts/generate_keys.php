<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/CryptoService.php';

$keyDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'keys';
$privatePath = $keyDirectory . DIRECTORY_SEPARATOR . 'private.pem';
$publicPath = $keyDirectory . DIRECTORY_SEPARATOR . 'public.pem';

if (is_file($privatePath) || is_file($publicPath)) {
    fwrite(STDERR, "Kunci sudah ada. Pindahkan kunci lama secara aman sebelum membuat pengganti.\n");
    exit(1);
}

CryptoService::generateKeyPair($keyDirectory);
fwrite(STDOUT, "Pasangan kunci RSA berhasil dibuat di storage/keys/.\n");
fwrite(STDOUT, "Jangan hapus atau bagikan private.pem; tanpa file itu konten tidak dapat didekripsi.\n");
