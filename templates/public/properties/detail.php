<?php

use App\Application\Support\ViewHelper as H;
use App\Domain\Property\Property;

/** @var Property $property */
/** @var string $backUrl */

$pageTitle = $property->getDisplayableAddress() . ' — Property Search';
ob_start();
?>
<div class="mb-6">
    <a href="<?= H::e($backUrl) ?>" class="text-slate-600 hover:text-slate-900 text-sm">&larr; Back to search</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden max-w-3xl mx-auto">
    <?php require __DIR__ . '/../../shared/properties/_property_detail_body.php'; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
