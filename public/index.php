<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$config = require dirname(__DIR__) . '/app/config.php';
$page = $_GET['page'] ?? 'landing';
if (!is_string($page) || !in_array($page, ['landing', 'admin', 'unlock'], true)) {
    $page = 'landing';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $page = $action === 'unlock' ? 'unlock' : 'admin';
}

if ($page === 'landing') {
    require dirname(__DIR__) . '/views/landing.php';
    exit;
}

if ($page === 'unlock') {
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_token();
        $privateKey = $_POST['private_key'] ?? '';
        if (is_string($privateKey) && trim($privateKey) !== '') {
            $crypto = new CryptoService($config['public_key']);
            try {
                $crypto->unlock($privateKey);
                session_regenerate_id(true);
                $_SESSION['private_key_pem'] = $privateKey;
                set_flash('Konten berhasil dibuka dengan private key.');
                header('Location: index.php?page=admin');
                exit;
            } catch (RuntimeException) {
                unset($_SESSION['private_key_pem']);
            }
        }

        unset($_SESSION['private_key_pem']);
        session_regenerate_id(true);
        header('Location: index.php?page=admin');
        exit;
    }

    require dirname(__DIR__) . '/views/unlock.php';
    exit;
}

$createPdo = require dirname(__DIR__) . '/app/database.php';
$pdo = $createPdo($config);
$crypto = new CryptoService($config['public_key']);
$repository = new ContentRepository($pdo, $crypto);
$contentService = new ContentService($repository);

$error = '';
$notice = take_flash();
$unlocked = false;
$sessionPrivateKey = $_SESSION['private_key_pem'] ?? null;
if (is_string($sessionPrivateKey)) {
    try {
        $crypto->unlock($sessionPrivateKey);
        $unlocked = true;
    } catch (RuntimeException) {
        unset($_SESSION['private_key_pem']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'lock') {
        unset($_SESSION['private_key_pem']);
        session_regenerate_id(true);
        set_flash('Konten telah dikunci kembali.');
        header('Location: index.php?page=admin');
        exit;
    } elseif ($action === 'save' && !$unlocked && ($_POST['id'] ?? '') !== '') {
        $error = 'Buka admin untuk mengubah konten yang sudah tersimpan.';
    } elseif ($action === 'save') {
        try {
            $contentService->save($_POST);
            set_flash('Konten berhasil disimpan dan dienkripsi.');
            header('Location: index.php?page=admin');
            exit;
        } catch (InvalidArgumentException $exception) {
            $error = $exception->getMessage();
        }
    } elseif (!$unlocked) {
        $page = 'admin';
        $error = 'Buka konten dengan private key sebelum mengelolanya.';
    } elseif ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id < 1) {
            $error = 'ID konten tidak valid.';
        } elseif (!$repository->delete($id)) {
            $error = 'Informasi yang akan dihapus tidak ditemukan.';
        } else {
            set_flash('Konten berhasil dihapus.');
            header('Location: index.php?page=admin');
            exit;
        }
    } else {
        $error = 'Aksi tidak dikenali.';
    }
}

$contents = $unlocked ? $repository->allDecrypted() : $repository->allEncrypted();
require dirname(__DIR__) . '/views/admin.php';
