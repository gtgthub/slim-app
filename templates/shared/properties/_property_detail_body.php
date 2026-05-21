<?php

use App\Application\Support\ViewHelper as H;
use App\Domain\Property\Property;

/** @var Property $property */

$heroImage = $property->getImagePath() ?? $property->getThumbnailPath();
$locationParts = array_filter([
    $property->getTown(),
    $property->getCounty(),
    $property->getCountry(),
]);
$locationLine = implode(', ', $locationParts);
$latitude = $property->getLatitude();
$longitude = $property->getLongitude();
?>
<!-- Hero image -->
<section class="w-full overflow-hidden bg-slate-100" data-purpose="hero-image">
    <?php if ($heroImage): ?>
        <img src="<?= H::e($heroImage) ?>"
             alt="<?= H::e($property->getDisplayableAddress()) ?>"
             class="w-full h-auto block">
    <?php else: ?>
        <div class="w-full aspect-[4/3] flex items-center justify-center text-slate-400 text-sm" aria-hidden="true">
            No image
        </div>
    <?php endif; ?>
</section>

<!-- Title & stats -->
<section class="px-6 pt-5 pb-4" data-purpose="property-info">
    <div class="flex flex-col gap-1 mb-4">
        <h2 class="text-2xl font-bold text-slate-900 leading-tight"><?= H::e($property->getDisplayableAddress()) ?></h2>
        <?php if ($locationLine !== ''): ?>
            <p class="text-slate-600"><?= H::e($locationLine) ?></p>
        <?php endif; ?>
    </div>
    <div class="flex flex-col gap-1 mb-5">
        <p class="text-2xl font-bold text-slate-900">&pound;<?= H::e(number_format($property->getPrice(), 2)) ?></p>
        <p class="text-sm text-slate-600"><?= H::e($property->getListingTypeLabel()) ?></p>
    </div>
    <div class="flex flex-wrap gap-x-5 gap-y-2 text-slate-600 text-sm border-t border-slate-200 pt-4">
        <div>
            <span class="font-medium"><?= (int) $property->getBedrooms() ?></span>
            <?= (int) $property->getBedrooms() === 1 ? 'Bed' : 'Beds' ?>
        </div>
        <div>
            <span class="font-medium"><?= (int) $property->getBathrooms() ?></span>
            <?= (int) $property->getBathrooms() === 1 ? 'Bath' : 'Baths' ?>
        </div>
        <div>
            <span class="font-medium"><?= H::e($property->getPropertyTypeLabel()) ?></span>
        </div>
    </div>
</section>

<!-- Description -->
<section class="px-6 py-5 border-t border-slate-200" data-purpose="description">
    <h3 class="text-base font-bold mb-2 uppercase tracking-wide text-slate-900">Description</h3>
    <div class="text-slate-600 leading-relaxed text-sm whitespace-pre-wrap"><?= H::e($property->getDescription()) ?></div>
</section>

<!-- Highlights -->
<section class="px-6 py-5 bg-slate-50 border-t border-slate-200" data-purpose="home-highlights">
    <div class="bg-white p-4 rounded-lg border border-slate-200">
        <h3 class="text-base font-bold mb-4 uppercase tracking-wide text-slate-900">Property Details</h3>
        <dl class="grid grid-cols-2 gap-y-5 gap-x-4 text-sm">
            <div>
                <dt class="text-xs text-slate-500 uppercase tracking-wider font-bold">Property type</dt>
                <dd class="font-medium text-slate-900 mt-0.5"><?= H::e($property->getPropertyTypeLabel()) ?></dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 uppercase tracking-wider font-bold">Listing</dt>
                <dd class="font-medium text-slate-900 mt-0.5"><?= H::e($property->getListingTypeLabel()) ?></dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 uppercase tracking-wider font-bold">County</dt>
                <dd class="font-medium text-slate-900 mt-0.5"><?= H::e($property->getCounty()) ?></dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500 uppercase tracking-wider font-bold">Country</dt>
                <dd class="font-medium text-slate-900 mt-0.5"><?= H::e($property->getCountry()) ?></dd>
            </div>
            <?php if ($latitude !== null): ?>
            <div>
                <dt class="text-xs text-slate-500 uppercase tracking-wider font-bold">Latitude</dt>
                <dd class="font-medium text-slate-900 mt-0.5"><?= H::e(rtrim(rtrim(sprintf('%.7f', $latitude), '0'), '.')) ?></dd>
            </div>
            <?php endif; ?>
            <?php if ($longitude !== null): ?>
            <div>
                <dt class="text-xs text-slate-500 uppercase tracking-wider font-bold">Longitude</dt>
                <dd class="font-medium text-slate-900 mt-0.5"><?= H::e(rtrim(rtrim(sprintf('%.7f', $longitude), '0'), '.')) ?></dd>
            </div>
            <?php endif; ?>
        </dl>
    </div>
</section>
