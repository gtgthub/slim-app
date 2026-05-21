<?php

use App\Application\Support\ViewHelper as H;

/** @var string|null $authUserName */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= H::e($pageTitle ?? 'Property Search') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-slate-100 min-h-screen text-slate-900">
<header class="bg-slate-800 text-white shadow">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="/" class="font-semibold text-lg hover:text-white">Property Search</a>
            <a href="/properties/search/location" class="text-slate-300 hover:text-white text-sm">Search by location</a>
        </div>
        <a href="/admin/login" class="text-slate-300 hover:text-white text-sm shrink-0">Admin</a>
    </div>
</header>

<main class="max-w-6xl mx-auto px-4 py-8">
    <?= $content ?? '' ?>
</main>

<script src="/assets/js/jquery.min.js"></script>
<?= $scripts ?? '' ?>
</body>
</html>
