<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$config = require dirname(__DIR__) . '/app/config.php';
$createPdo = require dirname(__DIR__) . '/app/database.php';
$pdo = $createPdo($config);
$auth = new Auth($pdo);
$crypto = new CryptoService($config['public_key'], $config['private_key']);
$repository = new ContentRepository($pdo, $crypto);
$contentService = new ContentService($repository);

$error = '';
$notice = take_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        if (is_string($username) && is_string($password) && $auth->attempt($username, $password)) {
            header('Location: index.php');
            exit;
        }
        $error = 'Username atau password tidak sesuai.';
    } elseif (!$auth->isAuthenticated()) {
        header('Location: index.php');
        exit;
    } elseif ($action === 'logout') {
        $auth->logout();
        header('Location: index.php');
        exit;
    } elseif ($action === 'save') {
        try {
            $contentService->save($_POST);
            set_flash('Konten berhasil disimpan dan dienkripsi.');
            header('Location: index.php');
            exit;
        } catch (InvalidArgumentException $exception) {
            $error = $exception->getMessage();
        }
    } elseif ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id < 1) {
            $error = 'ID konten tidak valid.';
        } elseif (!$repository->delete($id)) {
            $error = 'Informasi yang akan dihapus tidak ditemukan.';
        } else {
            set_flash('Konten berhasil dihapus.');
            header('Location: index.php');
            exit;
        }
    } else {
        $error = 'Aksi tidak dikenali.';
    }
}

$contents = $auth->isAuthenticated() ? $repository->all() : [];
$adminUsername = $_SESSION['admin_username'] ?? '';

require dirname(__DIR__) . '/views/' . ($auth->isAuthenticated() ? 'dashboard.php' : 'login.php');
