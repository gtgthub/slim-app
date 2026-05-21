<?php

use App\Application\Support\PaginationQuery;
use App\Application\Support\ViewHelper as H;
use App\Domain\Property\Property;

/** @var array<string, mixed> $formData */
/** @var array<string, string> $errors */
/** @var Property[] $properties */
/** @var PaginationQuery $pagination */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var int $totalCount */
/** @var bool $hasValidationErrors */

$d = $formData;
$pageTitle = 'Property Search';
ob_start();
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold">Find a property</h1>
    <p class="text-slate-600 text-sm mt-1">Search available listings by address, bedrooms, bathrooms, and minimum price.</p>
</div>

<form method="get" action="/" class="bg-white rounded-lg shadow p-6 space-y-4 mb-8">
    <div class="relative">
        <label for="displayable_address" class="block text-sm font-medium text-slate-700 mb-1">Displayable address</label>
        <input type="text" name="displayable_address" id="displayable_address" maxlength="255"
               autocomplete="off"
               value="<?= H::e((string) ($d['displayable_address'] ?? '')) ?>"
               class="w-full border rounded px-3 py-2 <?= isset($errors['displayable_address']) ? 'border-red-500' : 'border-slate-300' ?>"
               aria-autocomplete="list" aria-controls="address-autocomplete-list" aria-expanded="false">
        <ul id="address-autocomplete-list"
            class="hidden absolute z-10 left-0 right-0 mt-1 bg-white border border-slate-300 rounded shadow max-h-48 overflow-y-auto text-sm"
            role="listbox"></ul>
        <?php if (isset($errors['displayable_address'])): ?>
            <p class="text-red-600 text-xs mt-1"><?= H::e($errors['displayable_address']) ?></p>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="bedrooms" class="block text-sm font-medium text-slate-700 mb-1">Minimum bedrooms</label>
            <select name="bedrooms" id="bedrooms"
                    class="w-full border rounded px-3 py-2 <?= isset($errors['bedrooms']) ? 'border-red-500' : 'border-slate-300' ?>">
                <option value="">Any</option>
                <?php for ($i = 0; $i <= 20; $i++): ?>
                    <option value="<?= $i ?>"<?= H::selected((string) ($d['bedrooms'] ?? ''), (string) $i) ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <?php if (isset($errors['bedrooms'])): ?>
                <p class="text-red-600 text-xs mt-1"><?= H::e($errors['bedrooms']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label for="bathrooms" class="block text-sm font-medium text-slate-700 mb-1">Minimum bathrooms</label>
            <select name="bathrooms" id="bathrooms"
                    class="w-full border rounded px-3 py-2 <?= isset($errors['bathrooms']) ? 'border-red-500' : 'border-slate-300' ?>">
                <option value="">Any</option>
                <?php for ($i = 0; $i <= 20; $i++): ?>
                    <option value="<?= $i ?>"<?= H::selected((string) ($d['bathrooms'] ?? ''), (string) $i) ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <?php if (isset($errors['bathrooms'])): ?>
                <p class="text-red-600 text-xs mt-1"><?= H::e($errors['bathrooms']) ?></p>
            <?php endif; ?>
        </div>
        <div>
            <label for="price" class="block text-sm font-medium text-slate-700 mb-1">Minimum price (&pound;)</label>
            <input type="text" name="price" id="price" inputmode="decimal"
                   value="<?= H::e((string) ($d['price'] ?? '')) ?>"
                   class="w-full border rounded px-3 py-2 <?= isset($errors['price']) ? 'border-red-500' : 'border-slate-300' ?>">
            <?php if (isset($errors['price'])): ?>
                <p class="text-red-600 text-xs mt-1"><?= H::e($errors['price']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="bg-slate-800 text-white px-4 py-2 rounded text-sm font-medium hover:bg-slate-700">
            Search
        </button>
        <a href="/" class="text-slate-600 hover:text-slate-900 text-sm underline">Clear</a>
    </div>
</form>

<?php if ($hasValidationErrors): ?>
    <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm mb-6" role="alert">
        Please correct the errors above before viewing results.
    </div>
<?php elseif ($totalCount === 0): ?>
    <p class="text-slate-600 bg-white rounded-lg shadow p-6">No properties match your search.</p>
<?php else: ?>
<div>
    <p class="text-slate-600 text-sm mb-4"><?= (int) $totalCount ?> <?= $totalCount === 1 ? 'property' : 'properties' ?> found</p>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Thumbnail</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Address</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Town</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Price</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Listing</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <?php foreach ($properties as $property): ?>
                <tr>
                    <td class="px-4 py-3">
                        <?php if ($property->getThumbnailPath()): ?>
                            <img src="<?= H::e($property->getThumbnailPath()) ?>"
                                 alt="<?= H::e($property->getDisplayableAddress()) ?> thumbnail"
                                 width="100"
                                 height="100"
                                 class="w-[100px] h-[100px] rounded border border-slate-200 object-cover">
                        <?php else: ?>
                            <div class="w-[100px] h-[100px] rounded border border-slate-200 bg-slate-50" aria-hidden="true"></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <a href="/properties/<?= (int) $property->getId() ?>"
                           class="font-medium text-slate-800 hover:text-slate-900 underline">
                            <?= H::e($property->getDisplayableAddress()) ?>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-sm"><?= H::e($property->getTown()) ?></td>
                    <td class="px-4 py-3 text-sm"><?= H::e($property->getPropertyTypeLabel()) ?></td>
                    <td class="px-4 py-3 text-sm">&pound;<?= H::e(number_format($property->getPrice(), 2)) ?></td>
                    <td class="px-4 py-3 text-sm"><?= H::e($property->getListingTypeLabel()) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php require __DIR__ . '/_pagination.php'; ?>
    </div>
</div>
<?php endif; ?>

<?php
$scripts = <<<'HTML'
<script src="/assets/js/public-property-search.js"></script>
HTML;
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
