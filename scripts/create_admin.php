<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$config = require $root . '/app/config.php';
$createPdo = require $root . '/app/database.php';
$pdo = $createPdo($config);

fwrite(STDOUT, "Username admin: ");
$username = trim((string) fgets(STDIN));
if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
    fwrite(STDERR, "Username harus 3-50 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.\n");
    exit(1);
}

fwrite(STDOUT, "Password admin (minimal 12 karakter): ");
$password = trim((string) fgets(STDIN));
if (strlen($password) < 12) {
    fwrite(STDERR, "Password harus memiliki minimal 12 karakter.\n");
    exit(1);
}

fwrite(STDOUT, "Ulangi password: ");
$confirmation = trim((string) fgets(STDIN));
if (!hash_equals($password, $confirmation)) {
    fwrite(STDERR, "Konfirmasi password tidak cocok.\n");
    exit(1);
}

$statement = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)');
$statement->execute([
    'username' => $username,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
]);

fwrite(STDOUT, "Admin berhasil dibuat. Silakan masuk melalui halaman aplikasi.\n");
