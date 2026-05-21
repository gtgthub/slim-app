<?php

declare(strict_types=1);

namespace App\Domain\Property;

use App\Infrastructure\ExternalPropertyApi\CurlPropertyApiClient;
use App\Infrastructure\Media\RemotePropertyImageDownloader;
use Psr\Log\LoggerInterface;

class PropertyApiSyncService
{
    private CurlPropertyApiClient $client;

    private PropertyApiMapper $mapper;

    private PropertyApiRecordValidator $validator;

    private RemotePropertyImageDownloader $imageDownloader;

    private PropertyRepository $repository;

    private LoggerInterface $logger;

    public function __construct(
        CurlPropertyApiClient $client,
        PropertyApiMapper $mapper,
        PropertyApiRecordValidator $validator,
        RemotePropertyImageDownloader $imageDownloader,
        PropertyRepository $repository,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->mapper = $mapper;
        $this->validator = $validator;
        $this->imageDownloader = $imageDownloader;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function sync(PropertyApiSyncOptions $options): PropertyApiSyncResult
    {
        $pagesFetched = 0;
        $recordsProcessed = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $stoppedReason = 'completed';

        $currentPage = 1;
        $lastPage = 1;

        do {
            if ($options->hasPageLimit() && $pagesFetched >= (int) $options->getMaxPages()) {
                $stoppedReason = 'max_pages';
                break;
            }

            if ($options->hasRecordLimit() && $recordsProcessed >= (int) $options->getMaxRecords()) {
                $stoppedReason = 'max_records';
                break;
            }

            $response = $this->client->fetchPage($currentPage);
            $lastPage = $response['last_page'];
            $pagesFetched++;

            foreach ($response['data'] as $item) {
                if ($options->hasRecordLimit() && $recordsProcessed >= (int) $options->getMaxRecords()) {
                    $stoppedReason = 'max_records';
                    break 2;
                }

                if (!is_array($item)) {
                    $skipped++;
                    $recordsProcessed++;
                    continue;
                }

                try {
                    $property = $this->mapper->map($item);
                    $validationErrors = $this->validator->validate($property);
                    if ($validationErrors !== []) {
                        $this->logger->warning('Skipping invalid API property.', [
                            'uuid' => $property->getApiUuid(),
                            'errors' => $validationErrors,
                        ]);
                        $skipped++;
                        $recordsProcessed++;
                        continue;
                    }

                    $uuid = $property->getApiUuid() ?? '';
                    $existing = $uuid !== '' ? $this->repository->findApiByUuid($uuid) : null;

                    $urls = $this->mapper->extractImageUrls($item);
                    $paths = $this->imageDownloader->downloadForProperty(
                        $property->getApiUuid() ?? '',
                        $urls['image_full'],
                        $urls['image_thumbnail'],
                        $existing !== null ? $existing->getImagePath() : null,
                        $existing !== null ? $existing->getThumbnailPath() : null
                    );

                    $property = $property->withImagePaths($paths['image_path'], $paths['thumbnail_path']);
                    $this->repository->upsertApi($property);

                    if ($existing === null) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Throwable $e) {
                    $this->logger->error('Failed to sync API property.', [
                        'uuid' => $item['uuid'] ?? null,
                        'message' => $e->getMessage(),
                    ]);
                    $errors++;
                }

                $recordsProcessed++;
            }

            if ($options->hasPageLimit() && $pagesFetched >= (int) $options->getMaxPages()) {
                $stoppedReason = 'max_pages';
                break;
            }

            $currentPage++;
        } while ($currentPage <= $lastPage);

        return new PropertyApiSyncResult(
            $pagesFetched,
            $recordsProcessed,
            $created,
            $updated,
            $skipped,
            $errors,
            $stoppedReason
        );
    }
}
