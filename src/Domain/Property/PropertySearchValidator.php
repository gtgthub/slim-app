<?php

declare(strict_types=1);

namespace App\Domain\Property;

class PropertySearchValidator
{
    private const MAX_ADDRESS = 255;

    private const MAX_BED_BATH = 20;

    /**
     * @param array<string, mixed> $data
     * @return array<string, string> field => error message
     */
    public function validate(array $data): array
    {
        $errors = [];

        $address = $this->trimString($data['displayable_address'] ?? '');
        if ($address !== '' && strlen($address) > self::MAX_ADDRESS) {
            $errors['displayable_address'] = 'Address must be at most ' . self::MAX_ADDRESS . ' characters.';
        }

        $bedrooms = $data['bedrooms'] ?? '';
        if ($bedrooms !== '' && $bedrooms !== null) {
            if (!is_numeric($bedrooms) || (int) $bedrooms < 0 || (int) $bedrooms > self::MAX_BED_BATH) {
                $errors['bedrooms'] = 'Select a valid minimum bedrooms value (0–' . self::MAX_BED_BATH . ').';
            }
        }

        $bathrooms = $data['bathrooms'] ?? '';
        if ($bathrooms !== '' && $bathrooms !== null) {
            if (!is_numeric($bathrooms) || (int) $bathrooms < 0 || (int) $bathrooms > self::MAX_BED_BATH) {
                $errors['bathrooms'] = 'Select a valid minimum bathrooms value (0–' . self::MAX_BED_BATH . ').';
            }
        }

        $price = $this->trimString($data['price'] ?? '');
        if ($price !== '') {
            if (!is_numeric($price) || (float) $price < 0) {
                $errors['price'] = 'Minimum price must be a valid non-negative number.';
            }
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed> Normalized values for criteria and form repopulation.
     */
    public function normalize(array $data): array
    {
        $address = $this->trimString($data['displayable_address'] ?? '');
        $bedrooms = $data['bedrooms'] ?? '';
        $bathrooms = $data['bathrooms'] ?? '';
        $price = $this->trimString($data['price'] ?? '');

        return [
            'displayable_address' => $address,
            'bedrooms' => $bedrooms === '' || $bedrooms === null ? '' : (string) (int) $bedrooms,
            'bathrooms' => $bathrooms === '' || $bathrooms === null ? '' : (string) (int) $bathrooms,
            'price' => $price,
        ];
    }

    private function trimString(string $value): string
    {
        return trim($value);
    }
}
