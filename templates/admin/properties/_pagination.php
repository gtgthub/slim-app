<?php

use App\Application\Support\PaginationQuery as P;
use App\Application\Support\ViewHelper as H;

/** @var PaginationQuery $pagination */
/** @var int $currentPage */
/** @var int $totalPages */
?>
<div class="flex flex-wrap items-center justify-between w-full gap-4 px-4 py-3 border-t border-slate-200 bg-slate-50 text-sm">
    <span aria-hidden="true"></span>
    <div class="flex flex-wrap items-center gap-4">
        <form method="get" action="/admin/properties" class="flex items-center gap-2">
            <input type="hidden" name="page" value="1">
            <label for="per_page" class="text-slate-600 whitespace-nowrap">Per page</label>
            <select name="per_page" id="per_page"
                    class="border border-slate-300 rounded px-2 py-1 bg-white text-slate-900"
                    onchange="this.form.submit()">
                <?php foreach (P::PER_PAGE_OPTIONS as $option): ?>
                    <option value="<?= $option ?>"<?= H::selected((string) $pagination->getPerPage(), (string) $option) ?>>
                        <?= $option ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($totalPages > 0): ?>
        <span class="text-slate-600 whitespace-nowrap">
            <?= (int) $currentPage ?> of <?= (int) $totalPages ?> pages
        </span>

        <nav class="flex items-center gap-1" aria-label="Properties pagination">
            <?php if ($currentPage > 1): ?>
                <a href="<?= H::e($pagination->buildUrl($currentPage - 1)) ?>"
                   class="px-2 py-1 rounded text-slate-700 hover:bg-slate-200"
                   aria-label="Previous page">Prev</a>
            <?php else: ?>
                <span class="px-2 py-1 rounded text-slate-400 cursor-not-allowed" aria-hidden="true">Prev</span>
            <?php endif; ?>

            <?php foreach ($pagination->pageNumberWindow($totalPages, $currentPage) as $pageNum): ?>
                <?php if ($pageNum === null): ?>
                    <span class="px-1 text-slate-400" aria-hidden="true">&hellip;</span>
                <?php elseif ($pageNum === $currentPage): ?>
                    <span class="px-2 py-1 rounded bg-slate-800 text-white font-medium"
                          aria-current="page"><?= (int) $pageNum ?></span>
                <?php else: ?>
                    <a href="<?= H::e($pagination->buildUrl($pageNum)) ?>"
                       class="px-2 py-1 rounded text-slate-700 hover:bg-slate-200"><?= (int) $pageNum ?></a>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="<?= H::e($pagination->buildUrl($currentPage + 1)) ?>"
                   class="px-2 py-1 rounded text-slate-700 hover:bg-slate-200"
                   aria-label="Next page">Next</a>
            <?php else: ?>
                <span class="px-2 py-1 rounded text-slate-400 cursor-not-allowed" aria-hidden="true">Next</span>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</div>
