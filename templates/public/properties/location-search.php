<?php

use App\Application\Support\ViewHelper as H;
use App\Domain\Property\PropertyLocationSearchResult;

/** @var array<string, mixed> $formData */
/** @var array<string, string> $errors */
/** @var bool $searchSubmitted */
/** @var bool $hasSearch */
/** @var PropertyLocationSearchResult[] $results */
/** @var int $totalCount */
/** @var bool $resultsTruncated */
/** @var list<array<string, int|float|string>> $mapMarkers */
/** @var string $googleMapsApiKey */
/** @var float $searchLat */
/** @var float $searchLng */
/** @var float $radiusKm */
/** @var string $activeTab */

$d = $formData;
$isMapTab = $activeTab === 'map';
$mapsEnabled = $googleMapsApiKey !== '';
$pageTitle = 'Search by location';
$mapMarkersJson = json_encode(
    $mapMarkers,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
ob_start();
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold">Search by location</h1>
    <p class="text-slate-600 text-sm mt-1">
        Find properties within <?= H::e((string) $radiusKm) ?> km of a place.
        <?php if ($isMapTab): ?>
            Use the map tab to drop a pin; results update automatically.
        <?php else: ?>
            Enter an address on the Location search tab, or switch to Map to pick a point.
        <?php endif; ?>
    </p>
</div>

<?php if (!$mapsEnabled): ?>
    <div class="rounded border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 text-sm mb-6" role="alert">
        Google Maps is not configured. Set <code class="text-xs bg-amber-100 px-1 rounded">GOOGLE_MAPS_API_KEY</code> in your environment to enable the map.
    </div>
<?php endif; ?>

<form method="get" action="/properties/search/location" class="bg-white rounded-lg shadow p-6 space-y-4 mb-8" id="location-search-form"
      data-active-tab="<?= H::e($activeTab) ?>"
      data-maps-enabled="<?= $mapsEnabled ? '1' : '0' ?>">

    <div role="tablist" aria-label="Search method" class="flex border-b border-slate-200 -mx-6 px-6">
        <button type="button"
                role="tab"
                id="location-tab-text"
                aria-selected="<?= $isMapTab ? 'false' : 'true' ?>"
                aria-controls="location-panel-text"
                data-location-tab="text"
                class="location-search-tab px-4 py-2 text-sm font-medium border-b-2 -mb-px <?= $isMapTab ? 'border-transparent text-slate-600 hover:text-slate-900' : 'border-slate-800 text-slate-900' ?>">
            Location search
        </button>
        <button type="button"
                role="tab"
                id="location-tab-map"
                aria-selected="<?= $isMapTab ? 'true' : 'false' ?>"
                aria-controls="location-panel-map"
                data-location-tab="map"
                <?= !$mapsEnabled ? 'disabled' : '' ?>
                class="location-search-tab px-4 py-2 text-sm font-medium border-b-2 -mb-px <?= $isMapTab ? 'border-slate-800 text-slate-900' : 'border-transparent text-slate-600 hover:text-slate-900' ?><?= !$mapsEnabled ? ' opacity-50 cursor-not-allowed' : '' ?>">
            Map
        </button>
    </div>

    <input type="hidden" name="tab" id="search-tab" value="<?= H::e($activeTab) ?>">
    <input type="hidden" name="lat" id="latitude" value="<?= H::e((string) ($d['lat'] ?? '')) ?>">
    <input type="hidden" name="lng" id="longitude" value="<?= H::e((string) ($d['lng'] ?? '')) ?>">

    <div id="location-panel-text"
         role="tabpanel"
         aria-labelledby="location-tab-text"
         class="location-search-panel<?= $isMapTab ? ' hidden' : '' ?>">
        <div>
            <label for="location" class="block text-sm font-medium text-slate-700 mb-1">Location</label>
            <input type="text" name="location" id="location" maxlength="255"
                   autocomplete="off"
                   value="<?= H::e((string) ($d['location'] ?? '')) ?>"
                   placeholder="Type a place name or address"
                   class="w-full border rounded px-3 py-2 <?= isset($errors['location']) ? 'border-red-500' : 'border-slate-300' ?>">
            <?php if (isset($errors['location'])): ?>
                <p class="text-red-600 text-xs mt-1"><?= H::e($errors['location']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div id="location-panel-map"
         role="tabpanel"
         aria-labelledby="location-tab-map"
         class="location-search-panel<?= $isMapTab ? '' : ' hidden' ?>">
        <?php if ($mapsEnabled): ?>
            <p class="text-slate-600 text-sm mb-2">Click the map or drag the pin to search nearby properties.</p>
            <div id="location-picker-map"
                 class="w-full h-[280px] rounded border border-slate-200 bg-slate-100"
                 role="application"
                 aria-label="Pick search location on map"
                 data-default-lat="<?= H::e((string) $searchLat) ?>"
                 data-default-lng="<?= H::e((string) $searchLng) ?>"></div>
        <?php else: ?>
            <p class="text-slate-600 text-sm">Configure Google Maps to search by map.</p>
        <?php endif; ?>
    </div>

    <?php if (isset($errors['lat'])): ?>
        <p class="text-red-600 text-xs"><?= H::e($errors['lat']) ?></p>
    <?php endif; ?>
    <?php if (isset($errors['lng']) && !isset($errors['lat'])): ?>
        <p class="text-red-600 text-xs"><?= H::e($errors['lng']) ?></p>
    <?php endif; ?>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                id="location-search-submit"
                class="bg-slate-800 text-white px-4 py-2 rounded text-sm font-medium hover:bg-slate-700<?= $isMapTab ? ' hidden' : '' ?>">
            Search
        </button>
        <p id="location-map-auto-hint" class="text-slate-600 text-sm<?= $isMapTab ? '' : ' hidden' ?>">
            Searching automatically when you place or move the pin.
        </p>
        <a href="/properties/search/location" class="text-slate-600 hover:text-slate-900 text-sm underline">Clear</a>
    </div>
</form>

<?php if ($searchSubmitted && $errors !== []): ?>
    <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm mb-6" role="alert">
        Please correct the errors above before viewing results.
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div id="property-location-map"
             class="w-full h-[480px] lg:h-[600px] bg-slate-100"
             role="application"
             aria-label="Property results map"
             data-search-lat="<?= H::e((string) $searchLat) ?>"
             data-search-lng="<?= H::e((string) $searchLng) ?>"
             data-has-search="<?= $hasSearch ? '1' : '0' ?>"></div>
    </div>

    <div class="bg-white rounded-lg shadow flex flex-col h-[480px] lg:h-[600px]">
        <div class="px-4 py-3 border-b border-slate-200 shrink-0">
            <?php if (!$searchSubmitted): ?>
                <p class="text-slate-600 text-sm">Enter a location and search to see nearby properties.</p>
            <?php elseif (!$hasSearch): ?>
                <p class="text-slate-600 text-sm">Fix the form errors to view results.</p>
            <?php elseif ($totalCount === 0): ?>
                <p class="text-slate-600 text-sm">No properties with coordinates found within <?= H::e((string) $radiusKm) ?> km.</p>
            <?php else: ?>
                <p class="text-slate-600 text-sm">
                    <?= (int) $totalCount ?> <?= $totalCount === 1 ? 'property' : 'properties' ?> within <?= H::e((string) $radiusKm) ?> km
                    <?php if ($resultsTruncated): ?>
                        <span class="text-amber-700">(showing nearest <?= count($results) ?>)</span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="overflow-y-auto flex-1 p-4 space-y-4" id="location-results-sidebar">
            <?php if ($hasSearch && $totalCount > 0): ?>
                <?php foreach ($results as $result): ?>
                    <?php
                    $property = $result->getProperty();
                    $distanceKm = $result->getDistanceKm();
                    $propertyId = (int) $property->getId();
                    ?>
                    <article class="border border-slate-200 rounded-lg p-3 flex gap-3 hover:border-slate-400 transition-colors location-result-card"
                             data-property-id="<?= $propertyId ?>"
                             tabindex="0"
                             role="button"
                             aria-label="Show <?= H::e($property->getDisplayableAddress()) ?> on map">
                        <?php if ($property->getThumbnailPath()): ?>
                            <img src="<?= H::e($property->getThumbnailPath()) ?>"
                                 alt=""
                                 width="80"
                                 height="80"
                                 class="w-20 h-20 rounded border border-slate-200 object-cover shrink-0">
                        <?php else: ?>
                            <div class="w-20 h-20 rounded border border-slate-200 bg-slate-50 shrink-0" aria-hidden="true"></div>
                        <?php endif; ?>
                        <div class="min-w-0 flex-1">
                            <a href="/properties/<?= $propertyId ?>"
                               class="font-medium text-slate-800 hover:text-slate-900 underline text-sm block truncate">
                                <?= H::e($property->getDisplayableAddress()) ?>
                            </a>
                            <p class="text-xs text-slate-500 mt-0.5"><?= H::e($property->getTown()) ?></p>
                            <p class="text-sm mt-1">
                                &pound;<?= H::e(number_format($property->getPrice(), 2)) ?>
                                <span class="text-slate-500">&middot; <?= H::e($property->getListingTypeLabel()) ?></span>
                            </p>
                            <p class="text-xs text-slate-600 mt-1">
                                <?= H::e(number_format($distanceKm, 1)) ?> km away
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$apiKeyEscaped = H::e($googleMapsApiKey);
$scripts = '';
if ($googleMapsApiKey !== '') {
    $scripts = '<script>window.propertyMapMarkers = ' . $mapMarkersJson . ';</script>' . "\n"
        . '<script src="https://maps.googleapis.com/maps/api/js?key=' . $apiKeyEscaped
        . '&amp;libraries=places&amp;callback=initPropertyLocationMap" async defer></script>' . "\n"
        . '<script src="/assets/js/public-property-location-search.js"></script>';
} else {
    $scripts = '<script>window.propertyMapMarkers = [];</script>' . "\n"
        . '<script src="/assets/js/public-property-location-search.js"></script>';
}
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
