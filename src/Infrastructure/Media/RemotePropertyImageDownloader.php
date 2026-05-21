<?php

declare(strict_types=1);

namespace App\Infrastructure\Media;

class RemotePropertyImageDownloader
{
    private const MAX_BYTES = 5242880;

    /** @var array<string, string> */
    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private string $projectRoot;

    public function __construct(string $projectRoot)
    {
        $this->projectRoot = rtrim($projectRoot, '/');
    }

    /**
     * @return array{image_path: string|null, thumbnail_path: string|null}
     */
    public function downloadForProperty(
        string $apiUuid,
        ?string $imageUrl,
        ?string $thumbnailUrl,
        ?string $existingImagePath,
        ?string $existingThumbnailPath
    ): array {
        $dir = $this->projectRoot . '/public/uploads/properties/api/' . $this->sanitizeUuid($apiUuid);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return [
                'image_path' => $existingImagePath,
                'thumbnail_path' => $existingThumbnailPath,
            ];
        }

        $imagePath = $this->downloadOne($imageUrl, $dir, 'image', $existingImagePath);
        $thumbnailPath = $this->downloadOne($thumbnailUrl, $dir, 'thumbnail', $existingThumbnailPath);

        return [
            'image_path' => $imagePath,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    private function downloadOne(?string $url, string $dir, string $basename, ?string $fallback): ?string
    {
        if ($url === null || !$this->isValidHttpsUrl($url)) {
            return $fallback;
        }

        if (!function_exists('curl_init')) {
            return $fallback;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return $fallback;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $httpCode < 200 || $httpCode >= 300) {
            return $fallback;
        }

        if (strlen($body) <= 0 || strlen($body) > self::MAX_BYTES) {
            return $fallback;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'prop_img_');
        if ($tmp === false) {
            return $fallback;
        }

        file_put_contents($tmp, $body);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo !== false ? finfo_file($finfo, $tmp) : false;
        if ($finfo !== false) {
            finfo_close($finfo);
        }
        @unlink($tmp);

        if ($mime === false || !isset(self::ALLOWED_MIMES[$mime])) {
            return $fallback;
        }

        $ext = self::ALLOWED_MIMES[$mime];
        $relative = '/uploads/properties/api/' . basename($dir) . '/' . $basename . '.' . $ext;
        $absolute = $this->projectRoot . '/public' . $relative;

        if (file_put_contents($absolute, $body) === false) {
            return $fallback;
        }

        return $relative;
    }

    private function isValidHttpsUrl(string $url): bool
    {
        $parts = parse_url($url);

        return isset($parts['scheme'], $parts['host'])
            && strtolower($parts['scheme']) === 'https';
    }

    private function sanitizeUuid(string $uuid): string
    {
        return preg_replace('/[^a-zA-Z0-9\-]/', '', $uuid) ?? $uuid;
    }
}
