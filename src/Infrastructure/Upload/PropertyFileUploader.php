<?php

declare(strict_types=1);

namespace App\Infrastructure\Upload;

use RuntimeException;

class PropertyFileUploader
{
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private string $uploadRoot;

    public function __construct(string $uploadRoot)
    {
        $this->uploadRoot = rtrim($uploadRoot, '/');
    }

    /**
     * @param array<string, mixed> $file $_FILES entry
     */
    public function upload(int $propertyId, array $file, string $prefix): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('No file uploaded.');
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Invalid upload.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo !== false ? finfo_file($finfo, $tmp) : false;
        if ($finfo !== false) {
            finfo_close($finfo);
        }

        if ($mime === false || !isset(self::ALLOWED_MIMES[$mime])) {
            throw new RuntimeException('Invalid file type.');
        }

        $ext = self::ALLOWED_MIMES[$mime];
        $dir = $this->uploadRoot . '/' . $propertyId;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException('Could not create upload directory.');
        }

        $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $target = $dir . '/' . $filename;

        if (!move_uploaded_file($tmp, $target)) {
            throw new RuntimeException('Could not save uploaded file.');
        }

        return '/uploads/properties/' . $propertyId . '/' . $filename;
    }

    public function deleteByWebPath(?string $webPath): void
    {
        if ($webPath === null || $webPath === '') {
            return;
        }

        $fullPath = dirname(__DIR__, 3) . '/public' . $webPath;
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    public function deletePropertyDirectory(int $propertyId): void
    {
        $dir = $this->uploadRoot . '/' . $propertyId;
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        rmdir($dir);
    }
}
