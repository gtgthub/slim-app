<?php

use App\Application\Support\ViewHelper as H;

/** @var string $csrf */
/** @var array{type: string, message: string}|null $flash */
/** @var string|null $authUserName */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= H::e($pageTitle ?? 'Real Estate Admin') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-slate-100 min-h-screen text-slate-900">
<?php if (!empty($authUserName)): ?>
<nav class="bg-slate-800 text-white shadow">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-6">
            <a href="/admin/properties" class="font-semibold text-lg">Real Estate Admin</a>
            <a href="/admin/properties" class="text-slate-300 hover:text-white text-sm">Properties</a>
            <a href="/admin/properties/create" class="text-slate-300 hover:text-white text-sm">Add Property</a>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <span class="text-slate-300"><?= H::e($authUserName) ?></span>
            <form method="post" action="/admin/logout" class="inline">
                <input type="hidden" name="_csrf" value="<?= H::e($csrf) ?>">
                <button type="submit" class="text-slate-300 hover:text-white">Logout</button>
            </form>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="max-w-6xl mx-auto px-4 py-8">
    <?php if (!empty($flash)): ?>
        <div class="mb-6 rounded px-4 py-3 <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
            <?= H::e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?= $content ?? '' ?>
</main>

<script src="/assets/js/jquery.min.js"></script>
<?= $scripts ?? '' ?>
</body>
</html>
