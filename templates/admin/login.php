<?php

use App\Application\Support\ViewHelper as H;

/** @var string|null $error */
/** @var string $email */
/** @var string $csrf */

$pageTitle = 'Login — Real Estate Admin';
ob_start();
?>
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
    <h1 class="text-2xl font-bold mb-6 text-center">SuperAdmin Login</h1>

    <?php if (!empty($error)): ?>
        <div class="mb-4 rounded px-4 py-3 bg-red-100 text-red-800 border border-red-200 text-sm">
            <?= H::e($error) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/login" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= H::e($csrf) ?>">

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" id="email" required
                   value="<?= H::e($email) ?>"
                   class="w-full border border-slate-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input type="password" name="password" id="password" required
                   class="w-full border border-slate-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-500">
        </div>

        <button type="submit"
                class="w-full bg-slate-800 text-white py-2 rounded font-medium hover:bg-slate-700">
            Sign in
        </button>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
