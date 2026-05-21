<?php

use App\Application\Support\PaginationQuery;
use App\Application\Support\ViewHelper as H;
use App\Domain\Property\Property;

/** @var Property[] $properties */
/** @var string $csrf */
/** @var PaginationQuery $pagination */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var int $totalCount */

$pageTitle = 'Properties — Real Estate Admin';
ob_start();
?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold">Properties</h1>
        <p class="text-slate-600 text-sm mt-1">Total <?= (int) $totalCount ?> records</p>
    </div>
    <a href="/admin/properties/create"
       class="bg-slate-800 text-white px-4 py-2 rounded text-sm font-medium hover:bg-slate-700">
        Add Property
    </a>
</div>

<?php if ($totalCount === 0): ?>
    <p class="text-slate-600 bg-white rounded-lg shadow p-6">No properties yet. <a href="/admin/properties/create" class="text-slate-800 underline">Add your first property</a>.</p>
<?php else: ?>
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
                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase">Source</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
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
                    <button type="button"
                            class="js-property-detail-trigger text-left font-medium text-slate-800 hover:text-slate-900 underline"
                            data-property-id="<?= (int) $property->getId() ?>">
                        <?= H::e($property->getDisplayableAddress()) ?>
                    </button>
                </td>
                <td class="px-4 py-3 text-sm"><?= H::e($property->getTown()) ?></td>
                <td class="px-4 py-3 text-sm"><?= H::e($property->getPropertyTypeLabel()) ?></td>
                <td class="px-4 py-3 text-sm">&pound;<?= H::e(number_format($property->getPrice(), 2)) ?></td>
                <td class="px-4 py-3 text-sm"><?= H::e($property->getListingTypeLabel()) ?></td>
                <td class="px-4 py-3 text-sm"><?= $property->getSource() === Property::SOURCE_API ? 'API' : 'Manual' ?></td>
                <td class="px-4 py-3 text-sm text-right space-x-2">
                    <a href="/admin/properties/<?= (int) $property->getId() ?>/edit"
                       class="text-slate-700 hover:text-slate-900 underline">Edit</a>
                    <form method="post" action="/admin/properties/<?= (int) $property->getId() ?>/delete"
                          class="inline js-delete-form">
                        <input type="hidden" name="_csrf" value="<?= H::e($csrf) ?>">
                        <button type="submit" class="text-red-600 hover:text-red-800 underline">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php require __DIR__ . '/_pagination.php'; ?>
</div>

<?php require __DIR__ . '/_detail_modal.php'; ?>
<?php endif; ?>

<?php
$scripts = <<<'HTML'
<script src="/assets/js/admin-properties.js"></script>
HTML;
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
