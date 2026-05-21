<?php

declare(strict_types=1);

namespace App\Application\Support;

use Psr\Log\LoggerInterface;
use Throwable;

class PropertyUploadFailureHandler
{
    public const STEP_IMAGE = 'image';

    public const STEP_THUMBNAIL = 'thumbnail';

    public const STEP_SAVE = 'save';

    private LoggerInterface $logger;

    private bool $displayErrorDetails;

    public function __construct(LoggerInterface $logger, bool $displayErrorDetails)
    {
        $this->logger = $logger;
        $this->displayErrorDetails = $displayErrorDetails;
    }

    public function log(int $propertyId, string $failedStep, Throwable $exception): void
    {
        $this->logger->error('Property image upload failed', [
            'property_id' => $propertyId,
            'failed_step' => $failedStep,
            'exception' => $exception->getMessage(),
            'exception_class' => $exception::class,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function fieldErrors(string $failedStep, Throwable $exception): array
    {
        $reason = $this->describeFailure($exception);

        if ($failedStep === self::STEP_THUMBNAIL) {
            return ['thumbnail' => $this->formatMessage('Thumbnail', $reason)];
        }

        if ($failedStep === self::STEP_SAVE) {
            return ['_form' => $this->formatSaveMessage($reason)];
        }

        return ['image' => $this->formatMessage('Image', $reason)];
    }

    private function formatMessage(string $label, string $reason): string
    {
        if ($reason === '') {
            return "{$label} upload failed. Please try again.";
        }

        return "{$label} upload failed: {$reason} Please try again.";
    }

    private function formatSaveMessage(string $reason): string
    {
        if ($reason === '') {
            return 'Property could not be saved. Please try again.';
        }

        return "Property could not be saved: {$reason} Please try again.";
    }

    private function describeFailure(Throwable $exception): string
    {
        $mapped = match ($exception->getMessage()) {
            'No file uploaded.' => 'no file was received',
            'Upload failed.' => 'the server rejected the upload',
            'Invalid upload.' => 'the uploaded file is invalid',
            'Invalid file type.' => 'only JPEG, PNG, and WebP are allowed',
            'Could not create upload directory.' => 'the upload folder could not be created',
            'Upload directory is not writable.' => 'the upload folder is not writable',
            'Could not save uploaded file.' => 'the file could not be saved to disk',
            default => null,
        };

        if ($mapped !== null) {
            return $mapped;
        }

        if ($this->displayErrorDetails) {
            return $exception->getMessage();
        }

        return '';
    }
}
