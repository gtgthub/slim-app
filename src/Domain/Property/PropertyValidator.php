<?php

declare(strict_types=1);

namespace App\Domain\Property;

class PropertyValidator
{
    private const MAX_TEXT = 100;

    private const MAX_ADDRESS = 255;

    private const MAX_DESCRIPTION = 5000;

    private const MAX_BED_BATH = 20;

    private const MAX_UPLOAD_BYTES = 5242880;

    private const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $files $_FILES-style structure
     * @return array<string, string> field => error message
     */
    public function validate(array $data, ?array $files, bool $isCreate, ?Property $existing): array
    {
        $errors = [];

        $county = $this->trimString($data['county'] ?? '');
        if ($county === '' || strlen($county) > self::MAX_TEXT) {
            $errors['county'] = 'County is required (max ' . self::MAX_TEXT . ' characters).';
        }

        $country = $this->trimString($data['country'] ?? '');
        if ($country === '' || strlen($country) > self::MAX_TEXT) {
            $errors['country'] = 'Country is required (max ' . self::MAX_TEXT . ' characters).';
        }

        $town = $this->trimString($data['town'] ?? '');
        if ($town === '' || strlen($town) > self::MAX_TEXT) {
            $errors['town'] = 'Town is required (max ' . self::MAX_TEXT . ' characters).';
        }

        $description = $this->trimString($data['description'] ?? '');
        if ($description === '' || strlen($description) > self::MAX_DESCRIPTION) {
            $errors['description'] = 'Description is required (max ' . self::MAX_DESCRIPTION . ' characters).';
        }

        $address = $this->trimString($data['displayable_address'] ?? '');
        if ($address === '' || strlen($address) > self::MAX_ADDRESS) {
            $errors['displayable_address'] = 'Displayable address is required (max ' . self::MAX_ADDRESS . ' characters).';
        }

        $lat = $this->trimString($data['latitude'] ?? '');
        if ($lat !== '') {
            if (!is_numeric($lat) || (float) $lat < -90 || (float) $lat > 90) {
                $errors['latitude'] = 'Latitude must be between -90 and 90.';
            }
        }

        $lng = $this->trimString($data['longitude'] ?? '');
        if ($lng !== '') {
            if (!is_numeric($lng) || (float) $lng < -180 || (float) $lng > 180) {
                $errors['longitude'] = 'Longitude must be between -180 and 180.';
            }
        }

        $bedrooms = $data['bedrooms'] ?? '';
        if ($bedrooms === '' || !is_numeric($bedrooms) || (int) $bedrooms < 0 || (int) $bedrooms > self::MAX_BED_BATH) {
            $errors['bedrooms'] = 'Select a valid number of bedrooms (0–' . self::MAX_BED_BATH . ').';
        }

        $bathrooms = $data['bathrooms'] ?? '';
        if ($bathrooms === '' || !is_numeric($bathrooms) || (int) $bathrooms < 0 || (int) $bathrooms > self::MAX_BED_BATH) {
            $errors['bathrooms'] = 'Select a valid number of bathrooms (0–' . self::MAX_BED_BATH . ').';
        }

        $price = $this->trimString($data['price'] ?? '');
        if ($price === '' || !is_numeric($price) || (float) $price < 0) {
            $errors['price'] = 'Price must be a valid non-negative number.';
        }

        $propertyType = $data['property_type'] ?? '';
        if (!array_key_exists($propertyType, Property::PROPERTY_TYPES)) {
            $errors['property_type'] = 'Select a valid property type.';
        }

        $listingType = $data['listing_type'] ?? '';
        if (!in_array($listingType, [Property::LISTING_SALE, Property::LISTING_RENT], true)) {
            $errors['listing_type'] = 'Select For Sale or For Rent.';
        }

        if ($files === null) {
            $files = ['image' => null, 'thumbnail' => null];
        }

        $hasImage = $existing !== null && $existing->getImagePath() !== null;
        $hasThumb = $existing !== null && $existing->getThumbnailPath() !== null;

        $imageError = $this->validateUpload($files['image'] ?? null, 'Image', $isCreate || !$hasImage);
        if ($imageError !== null) {
            $errors['image'] = $imageError;
        }

        $thumbError = $this->validateUpload($files['thumbnail'] ?? null, 'Thumbnail', $isCreate || !$hasThumb);
        if ($thumbError !== null) {
            $errors['thumbnail'] = $thumbError;
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function buildPropertyFromInput(array $data, ?Property $existing, ?int $createdBy): Property
    {
        $lat = $this->trimString($data['latitude'] ?? '');
        $lng = $this->trimString($data['longitude'] ?? '');

        return new Property(
            $existing !== null ? $existing->getId() : null,
            $existing !== null ? $existing->getSource() : Property::SOURCE_MANUAL,
            $existing !== null ? $existing->getApiUuid() : null,
            $this->trimString($data['county'] ?? ''),
            $this->trimString($data['country'] ?? ''),
            $this->trimString($data['town'] ?? ''),
            $this->trimString($data['description'] ?? ''),
            $this->trimString($data['displayable_address'] ?? ''),
            $existing !== null ? $existing->getImagePath() : null,
            $existing !== null ? $existing->getThumbnailPath() : null,
            $lat !== '' ? (float) $lat : null,
            $lng !== '' ? (float) $lng : null,
            (int) ($data['bedrooms'] ?? 0),
            (int) ($data['bathrooms'] ?? 0),
            (float) ($data['price'] ?? 0),
            (string) ($data['property_type'] ?? ''),
            (string) ($data['listing_type'] ?? ''),
            $existing !== null ? $existing->getCreatedBy() : $createdBy
        );
    }

    /**
     * @param array<string, mixed>|null $file
     */
    private function validateUpload(?array $file, string $label, bool $required): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required ? "{$label} is required." : null;
        }

        $error = (int) ($file['error'] ?? UPLOAD_ERR_OK);
        if ($error !== UPLOAD_ERR_OK) {
            return "{$label} upload failed.";
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_UPLOAD_BYTES) {
            return "{$label} must be at most 5MB.";
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            return "{$label} is invalid.";
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo !== false ? finfo_file($finfo, $tmp) : false;
        if ($finfo !== false) {
            finfo_close($finfo);
        }

        if ($mime === false || !isset(self::ALLOWED_MIMES[$mime])) {
            return "{$label} must be JPEG, PNG, or WebP.";
        }

        return null;
    }

    private function trimString(string $value): string
    {
        return trim($value);
    }
}
