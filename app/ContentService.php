<?php
declare(strict_types=1);

final class ContentService
{
    public function __construct(private ContentRepository $repository)
    {
    }

    /** @param array<string, mixed> $input */
    public function save(array $input): void
    {
        $title = $this->field($input, 'title');
        $category = $this->field($input, 'category');
        $body = $this->field($input, 'body');

        if ($title === '' || $category === '' || $body === '') {
            throw new InvalidArgumentException('Judul, kategori, dan isi wajib diisi.');
        }
        if (strlen($title) > 500 || strlen($category) > 200 || strlen($body) > 60000) {
            throw new InvalidArgumentException('Ukuran konten melebihi batas yang diizinkan.');
        }

        $record = ['title' => $title, 'category' => $category, 'body' => $body];
        $idInput = $input['id'] ?? '';
        if ($idInput === '') {
            $this->repository->create($record);
            return;
        }

        $id = filter_var($idInput, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id < 1) {
            throw new InvalidArgumentException('ID konten tidak valid.');
        }
        if (!$this->repository->update($id, $record)) {
            throw new InvalidArgumentException('Informasi yang akan diperbarui tidak ditemukan.');
        }
    }

    private function field(array $input, string $name): string
    {
        $value = $input[$name] ?? '';
        if (!is_string($value)) {
            throw new InvalidArgumentException('Format data formulir tidak valid.');
        }

        return trim($value);
    }
}
