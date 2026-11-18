<?php
declare(strict_types=1);

final class ContentRepository
{
    public function __construct(
        private PDO $pdo,
        private CryptoService $crypto
    ) {
    }

    /** @return list<array{id: int, title: string, category: string, body: string, ciphertext: string, nonce: string, auth_tag: string, wrapped_key: string, created_at: string, updated_at: string}> */
    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT id, ciphertext, nonce, auth_tag, wrapped_key, created_at, updated_at
             FROM admin_contents ORDER BY updated_at DESC, id DESC'
        );

        $contents = [];
        foreach ($statement->fetchAll() as $row) {
            $record = $this->crypto->decryptRecord($row);
            $contents[] = [
                'id' => (int) $row['id'],
                ...$record,
                'ciphertext' => $row['ciphertext'],
                'nonce' => $row['nonce'],
                'auth_tag' => $row['auth_tag'],
                'wrapped_key' => $row['wrapped_key'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];
        }

        return $contents;
    }

    /** @param array{title: string, category: string, body: string} $record */
    public function create(array $record): void
    {
        $encrypted = $this->crypto->encryptRecord($record);
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_contents (ciphertext, nonce, auth_tag, wrapped_key)
             VALUES (:ciphertext, :nonce, :auth_tag, :wrapped_key)'
        );
        $statement->execute($encrypted);
    }

    /** @param array{title: string, category: string, body: string} $record */
    public function update(int $id, array $record): bool
    {
        $encrypted = $this->crypto->encryptRecord($record);
        $statement = $this->pdo->prepare(
            'UPDATE admin_contents
             SET ciphertext = :ciphertext, nonce = :nonce, auth_tag = :auth_tag,
                 wrapped_key = :wrapped_key
             WHERE id = :id'
        );
        $statement->execute([...$encrypted, 'id' => $id]);

        return $statement->rowCount() === 1;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM admin_contents WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() === 1;
    }
}
