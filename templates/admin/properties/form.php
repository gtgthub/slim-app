<?php

use App\Application\Support\ViewHelper as H;
use App\Domain\Property\Property;

/** @var Property|null $property */
/** @var array<string, string> $errors */
/** @var array<string, mixed> $formData */
/** @var bool $isEdit */
/** @var string $csrf */

$d = $formData;
$pageTitle = ($isEdit ? 'Edit' : 'Add') . ' Property — Real Estate Admin';
$action = $isEdit ? '/admin/properties/' . (int) $property->getId() : '/admin/properties';

ob_start();
?>
<div class="mb-6">
    <a href="/admin/properties" class="text-slate-600 hover:text-slate-900 text-sm">&larr; Back to list</a>
    <h1 class="text-2xl font-bold mt-2"><?= $isEdit ? 'Edit Property' : 'Add Property' ?></h1>
</div>

<form method="post" action="<?= H::e($action) ?>" enctype="multipart/form-data"
      class="bg-white rounded-lg shadow p-6 space-y-6">
    <input type="hidden" name="_csrf" value="<?= H::e($csrf) ?>">

    <?php if (isset($errors['_form'])): ?>
        <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm" role="alert">
            <?= H::e($errors['_form']) ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="county" class="block text-sm font-medium text-slate-700 mb-1">County *</label>
            <input type="text" name="county" id="county" required maxlength="100"
                   value="<?= H::e((string) ($d['county'] ?? '')) ?>"
                   class="w-full border rounded px-3 py-2 <?= isset($errors['county']) ? 'border-red-500' : 'border-slate-300' ?>">
            <?php if (isset($errors['county'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['county']) ?></p><?php endif; ?>
        </div>
        <div>
            <label for="country" class="block text-sm font-medium text-slate-700 mb-1">Country *</label>
            <input type="text" name="country" id="country" required maxlength="100"
                   value="<?= H::e((string) ($d['country'] ?? '')) ?>"
                   class="w-full border rounded px-3 py-2 <?= isset($errors['country']) ? 'border-red-500' : 'border-slate-300' ?>">
            <?php if (isset($errors['country'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['country']) ?></p><?php endif; ?>
        </div>
        <div>
            <label for="town" class="block text-sm font-medium text-slate-700 mb-1">Town *</label>
            <input type="text" name="town" id="town" required maxlength="100"
                   value="<?= H::e((string) ($d['town'] ?? '')) ?>"
                   class="w-full border rounded px-3 py-2 <?= isset($errors['town']) ? 'border-red-500' : 'border-slate-300' ?>">
            <?php if (isset($errors['town'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['town']) ?></p><?php endif; ?>
        </div>
    </div>

    <div>
        <label for="displayable_address" class="block text-sm font-medium text-slate-700 mb-1">Displayable Address *</label>
        <input type="text" name="displayable_address" id="displayable_address" required maxlength="255"
               value="<?= H::e((string) ($d['displayable_address'] ?? '')) ?>"
               class="w-full border rounded px-3 py-2 <?= isset($errors['displayable_address']) ? 'border-red-500' : 'border-slate-300' ?>">
        <?php if (isset($errors['displayable_address'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['displayable_address']) ?></p><?php endif; ?>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description *</label>
        <textarea name="description" id="description" rows="5" required maxlength="5000"
                  class="w-full border rounded px-3 py-2 <?= isset($errors['description']) ? 'border-red-500' : 'border-slate-300' ?>"><?= H::e((string) ($d['description'] ?? '')) ?></textarea>
        <?php if (isset($errors['description'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['description']) ?></p><?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="image" class="block text-sm font-medium text-slate-700 mb-1">Image <?= $isEdit ? '' : '*' ?></label>
            <?php if ($isEdit && $property !== null && $property->getImagePath()): ?>
                <img src="<?= H::e($property->getImagePath()) ?>" alt="Current image" class="h-24 mb-2 rounded border">
            <?php endif; ?>
            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                   <?= $isEdit ? '' : 'required' ?>
                   class="w-full text-sm">
            <?php if (isset($errors['image'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['image']) ?></p><?php endif; ?>
        </div>
        <div>
            <label for="thumbnail" class="block text-sm font-medium text-slate-700 mb-1">Thumbnail <?= $isEdit ? '' : '*' ?></label>
            <?php if ($isEdit && $property !== null && $property->getThumbnailPath()): ?>
                <img src="<?= H::e($property->getThumbnailPath()) ?>" alt="Current thumbnail" class="h-24 mb-2 rounded border">
            <?php endif; ?>
            <input type="file" name="thumbnail" id="thumbnail" accept="image/jpeg,image/png,image/webp"
                   <?= $isEdit ? '' : 'required' ?>
                   class="w-full text-sm">
            <?php if (isset($errors['thumbnail'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['thumbnail']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="latitude" class="block text-sm font-medium text-slate-700 mb-1">Latitude</label>
            <input type="text" name="latitude" id="latitude"
                   value="<?= H::e((string) ($d['latitude'] ?? '')) ?>"
                   class="w-full border rounded px-3 py-2 <?= isset($errors['latitude']) ? 'border-red-500' : 'border-slate-300' ?>">
            <?php if (isset($errors['latitude'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['latitude']) ?></p><?php endif; ?>
        </div>
        <div>
            <label for="longitude" class="block text-sm font-medium text-slate-700 mb-1">Longitude</label>
            <input type="text" name="longitude" id="longitude"
                   value="<?= H::e((string) ($d['longitude'] ?? '')) ?>"
                   class="w-full border rounded px-3 py-2 <?= isset($errors['longitude']) ? 'border-red-500' : 'border-slate-300' ?>">
            <?php if (isset($errors['longitude'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['longitude']) ?></p><?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="bedrooms" class="block text-sm font-medium text-slate-700 mb-1">Bedrooms *</label>
            <select name="bedrooms" id="bedrooms" required class="w-full border rounded px-3 py-2 border-slate-300">
                <option value="">Select</option>
                <?php for ($i = 0; $i <= 20; $i++): ?>
                    <option value="<?= $i ?>"<?= H::selected((string) ($d['bedrooms'] ?? ''), (string) $i) ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <?php if (isset($errors['bedrooms'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['bedrooms']) ?></p><?php endif; ?>
        </div>
        <div>
            <label for="bathrooms" class="block text-sm font-medium text-slate-700 mb-1">Bathrooms *</label>
            <select name="bathrooms" id="bathrooms" required class="w-full border rounded px-3 py-2 border-slate-300">
                <option value="">Select</option>
                <?php for ($i = 0; $i <= 20; $i++): ?>
                    <option value="<?= $i ?>"<?= H::selected((string) ($d['bathrooms'] ?? ''), (string) $i) ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <?php if (isset($errors['bathrooms'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['bathrooms']) ?></p><?php endif; ?>
        </div>
        <div>
            <label for="property_type" class="block text-sm font-medium text-slate-700 mb-1">Property Type *</label>
            <select name="property_type" id="property_type" required class="w-full border rounded px-3 py-2 border-slate-300">
                <?php foreach (Property::PROPERTY_TYPES as $key => $label): ?>
                    <option value="<?= H::e($key) ?>"<?= H::selected((string) ($d['property_type'] ?? ''), $key) ?>><?= H::e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['property_type'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['property_type']) ?></p><?php endif; ?>
        </div>
        <div>
            <label for="price" class="block text-sm font-medium text-slate-700 mb-1">Price (&pound;) *</label>
            <input type="text" name="price" id="price" required
                   value="<?= H::e((string) ($d['price'] ?? '')) ?>"
                   class="w-full border rounded px-3 py-2 <?= isset($errors['price']) ? 'border-red-500' : 'border-slate-300' ?>">
            <?php if (isset($errors['price'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['price']) ?></p><?php endif; ?>
        </div>
    </div>

    <div>
        <span class="block text-sm font-medium text-slate-700 mb-2">For Sale / For Rent *</span>
        <div class="flex gap-6">
            <label class="inline-flex items-center gap-2">
                <input type="radio" name="listing_type" value="sale"
                    <?= H::checked((string) ($d['listing_type'] ?? Property::LISTING_SALE), Property::LISTING_SALE) ?>>
                For Sale
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="radio" name="listing_type" value="rent"
                    <?= H::checked((string) ($d['listing_type'] ?? ''), Property::LISTING_RENT) ?>>
                For Rent
            </label>
        </div>
        <?php if (isset($errors['listing_type'])): ?><p class="text-red-600 text-xs mt-1"><?= H::e($errors['listing_type']) ?></p><?php endif; ?>
    </div>

    <div class="flex gap-3 pt-4 border-t border-slate-200">
        <button type="submit" class="bg-slate-800 text-white px-6 py-2 rounded font-medium hover:bg-slate-700">
            <?= $isEdit ? 'Update Property' : 'Create Property' ?>
        </button>
        <a href="/admin/properties" class="px-6 py-2 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</a>
    </div>
</form>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
