<?php

declare(strict_types=1);

/**
 * Sync properties from the external API into the database (source = api only).
 *
 * Usage:
 *   php bin/sync-api-properties.php
 *   php bin/sync-api-properties.php --max-pages=1
 *   php bin/sync-api-properties.php --limit=100
 *   php bin/sync-api-properties.php --max-pages=10 --limit=500
 */
use App\Application\Settings\SettingsInterface;
use App\Domain\Property\PropertyApiSyncOptions;
use App\Domain\Property\PropertyApiSyncService;
use App\Infrastructure\ExternalPropertyApi\PropertyApiException;
use Psr\Log\LoggerInterface;

/**
 * @return array<string, int|null>
 */
function parseCliArgs(array $argv): array
{
    $maxPages = null;
    $limit = null;
    $showHelp = false;

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            $showHelp = true;
            continue;
        }
        if (preg_match('/^--max-pages=(\d+)$/', $arg, $m) === 1) {
            $maxPages = (int) $m[1];
            continue;
        }
        if (preg_match('/^--limit=(\d+)$/', $arg, $m) === 1) {
            $limit = (int) $m[1];
            continue;
        }
        fwrite(STDERR, "Unknown argument: {$arg}\n");
        exit(1);
    }

    if ($showHelp) {
        printHelp();
        exit(0);
    }

    return ['max_pages' => $maxPages, 'limit' => $limit];
}

function printHelp(): void
{
    $help = <<<'TEXT'
Sync properties from the external API (source = api only; manual rows are never modified).

Usage:
  php bin/sync-api-properties.php [options]

Options:
  --max-pages=N   Stop after N API pages (e.g. 1 or 10)
  --limit=N       Stop after N records processed (e.g. 100 or 500)
  --help, -h      Show this help

If both --max-pages and --limit are set, the sync stops when either cap is reached first.
With no options, all pages are synced (production CRON).

Environment (see .env.example):
  PROPERTY_API_BASE_URL, PROPERTY_API_KEY, PROPERTY_API_PAGE_SIZE
  PROPERTY_API_SYNC_MAX_PAGES, PROPERTY_API_SYNC_LIMIT (optional defaults)

Examples:
  php bin/sync-api-properties.php --max-pages=1
  php bin/sync-api-properties.php --limit=100
  php bin/sync-api-properties.php --max-pages=10 --limit=500

TEXT;
    fwrite(STDOUT, $help);
}

function resolvePositiveLimit(?int $cli, ?int $env): ?int
{
    if ($cli !== null) {
        return $cli > 0 ? $cli : null;
    }

    return $env;
}

function validateLimit(?int $value, string $name): void
{
    if ($value === null) {
        return;
    }
    if ($value <= 0) {
        fwrite(STDERR, "{$name} must be a positive integer.\n");
        exit(1);
    }
}

$cli = parseCliArgs($argv);

if ($cli['max_pages'] !== null && $cli['max_pages'] <= 0) {
    fwrite(STDERR, "--max-pages must be a positive integer.\n");
    exit(1);
}
if ($cli['limit'] !== null && $cli['limit'] <= 0) {
    fwrite(STDERR, "--limit must be a positive integer.\n");
    exit(1);
}

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/../app/bootstrap.php';
/** @var SettingsInterface $settings */
$settings = $container->get(SettingsInterface::class);
$apiConfig = $settings->get('property_api');
$apiKey = $apiConfig['api_key'] ?? '';

if ($apiKey === '') {
    $apiKey = getenv('PROPERTY_API_KEY') !== false ? (string) getenv('PROPERTY_API_KEY') : '';
}

if ($apiKey === '') {
    fwrite(STDERR, "PROPERTY_API_KEY is not set (.env or environment).\n");
    exit(1);
}

$maxPages = resolvePositiveLimit($cli['max_pages'], $apiConfig['sync_max_pages'] ?? null);
$limit = resolvePositiveLimit($cli['limit'], $apiConfig['sync_limit'] ?? null);

validateLimit($maxPages, 'max-pages');
validateLimit($limit, 'limit');

$options = new PropertyApiSyncOptions($maxPages, $limit);

/** @var PropertyApiSyncService $syncService */
$syncService = $container->get(PropertyApiSyncService::class);
/** @var LoggerInterface $logger */
$logger = $container->get(LoggerInterface::class);

try {
    $result = $syncService->sync($options);
} catch (PropertyApiException $e) {
    fwrite(STDERR, 'API sync failed: ' . $e->getMessage() . "\n");
    exit(1);
}

$summary = sprintf(
    "API sync finished (stopped: %s)\n" .
    "  pages_fetched: %d\n" .
    "  records_processed: %d\n" .
    "  created: %d\n" .
    "  updated: %d\n" .
    "  skipped: %d\n" .
    "  errors: %d\n",
    $result->getStoppedReason(),
    $result->getPagesFetched(),
    $result->getRecordsProcessed(),
    $result->getCreated(),
    $result->getUpdated(),
    $result->getSkipped(),
    $result->getErrors()
);

fwrite(STDOUT, $summary);
$logger->info('API property sync completed', [
    'stopped_reason' => $result->getStoppedReason(),
    'pages_fetched' => $result->getPagesFetched(),
    'records_processed' => $result->getRecordsProcessed(),
    'created' => $result->getCreated(),
    'updated' => $result->getUpdated(),
    'skipped' => $result->getSkipped(),
    'errors' => $result->getErrors(),
]);

exit($result->getErrors() > 0 ? 1 : 0);
