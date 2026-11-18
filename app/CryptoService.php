<?php
declare(strict_types=1);

final class CryptoService
{
    private OpenSSLAsymmetricKey $publicKey;
    private OpenSSLAsymmetricKey $privateKey;

    public function __construct(string $publicKeyPath, string $privateKeyPath)
    {
        if (!is_file($publicKeyPath) || !is_file($privateKeyPath)) {
            throw new RuntimeException(
                'Kunci RSA belum tersedia. Jalankan: php scripts/generate_keys.php'
            );
        }

        $publicKey = openssl_pkey_get_public((string) file_get_contents($publicKeyPath));
        $privateKey = openssl_pkey_get_private((string) file_get_contents($privateKeyPath));
        if ($publicKey === false || $privateKey === false) {
            throw new RuntimeException('Kunci RSA tidak dapat dibaca. Periksa file di storage/keys.');
        }

        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    public static function generateKeyPair(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Folder penyimpanan kunci gagal dibuat.');
        }

        $options = [
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ];
        $opensslConfig = self::openSslConfigPath();
        if ($opensslConfig !== null) {
            $options['config'] = $opensslConfig;
        }
        $key = openssl_pkey_new($options);
        if ($key === false || !openssl_pkey_export(
            $key,
            $privatePem,
            null,
            $opensslConfig === null ? [] : ['config' => $opensslConfig]
        )) {
            throw new RuntimeException('Pembuatan kunci RSA gagal.');
        }

        $details = openssl_pkey_get_details($key);
        if ($details === false || !isset($details['key'])) {
            throw new RuntimeException('Kunci publik RSA gagal dibuat.');
        }

        $privatePath = $directory . DIRECTORY_SEPARATOR . 'private.pem';
        $publicPath = $directory . DIRECTORY_SEPARATOR . 'public.pem';
        if (file_put_contents($privatePath, $privatePem, LOCK_EX) === false
            || file_put_contents($publicPath, $details['key'], LOCK_EX) === false
        ) {
            throw new RuntimeException('File kunci RSA gagal disimpan.');
        }

        @chmod($privatePath, 0600);
    }

    private static function openSslConfigPath(): ?string
    {
        $configuredPath = getenv('OPENSSL_CONF');
        if ($configuredPath !== false && $configuredPath !== '') {
            return $configuredPath;
        }

        $candidates = [
            dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf',
        ];
        $iniPath = php_ini_loaded_file();
        if (is_string($iniPath)) {
            $candidates[] = dirname($iniPath) . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf';
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                putenv('OPENSSL_CONF=' . $candidate);
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Encrypts an entire information record using AES-GCM and wraps its key with RSA.
     *
     * @param array{title: string, category: string, body: string} $record
     * @return array{ciphertext: string, nonce: string, auth_tag: string, wrapped_key: string}
     */
    public function encryptRecord(array $record): array
    {
        $key = random_bytes(32);
        $nonce = random_bytes(12);
        $plaintext = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($ciphertext === false || !openssl_public_encrypt(
            $key,
            $wrappedKey,
            $this->publicKey,
            OPENSSL_PKCS1_OAEP_PADDING
        )) {
            throw new RuntimeException('Enkripsi konten gagal.');
        }

        return [
            'ciphertext' => base64_encode($ciphertext),
            'nonce' => base64_encode($nonce),
            'auth_tag' => base64_encode($tag),
            'wrapped_key' => base64_encode($wrappedKey),
        ];
    }

    /**
     * @param array{ciphertext: string, nonce: string, auth_tag: string, wrapped_key: string} $encrypted
     * @return array{title: string, category: string, body: string}
     */
    public function decryptRecord(array $encrypted): array
    {
        $ciphertext = base64_decode($encrypted['ciphertext'], true);
        $nonce = base64_decode($encrypted['nonce'], true);
        $tag = base64_decode($encrypted['auth_tag'], true);
        $wrappedKey = base64_decode($encrypted['wrapped_key'], true);
        if ($ciphertext === false || $nonce === false || $tag === false || $wrappedKey === false) {
            throw new RuntimeException('Data terenkripsi rusak atau formatnya tidak valid.');
        }

        if (!openssl_private_decrypt(
            $wrappedKey,
            $key,
            $this->privateKey,
            OPENSSL_PKCS1_OAEP_PADDING
        )) {
            throw new RuntimeException('Kunci konten tidak dapat dibuka dengan kunci RSA.');
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );
        if ($plaintext === false) {
            throw new RuntimeException('Verifikasi AES-GCM gagal; data mungkin telah berubah.');
        }

        $record = json_decode($plaintext, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($record)
            || !isset($record['title'], $record['category'], $record['body'])
            || !is_string($record['title'])
            || !is_string($record['category'])
            || !is_string($record['body'])
        ) {
            throw new RuntimeException('Format konten hasil dekripsi tidak valid.');
        }

        return [
            'title' => $record['title'],
            'category' => $record['category'],
            'body' => $record['body'],
        ];
    }
}
