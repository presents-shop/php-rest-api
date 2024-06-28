<?php require ("views/partials/head.php"); ?>
<?php require ("styles/index.php"); ?>

<header>
    <div class="border">
        <div class="container mx-auto">
            <?php require ("views/partials/nav.php"); ?>
        </div>
    </div>
    <h1 class="bg-gray-100 text-center py-10 text-4xl">Потребители</h1>
</header>

<main>
    <div class="container mx-auto py-10">
        <table class="border w-full border-collapse text-left">
            <thead>
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Имейл</th>
                    <th class="border p-2">Телефон</th>
                    <th class="border p-2">Име</th>
                    <th class="border p-2">Фамилия</th>
                    <th class="border p-2">Операции</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td class="border p-2"><?= $user["id"] ?></td>
                        <td class="border p-2">
                            <a href="/admin/users/<?= $user["id"] ?>"><?= $user["email"] ?></a>
                        </td>
                        <td class="border p-2"></td>
                        <td class="border p-2"></td>
                        <td class="border p-2"></td>
                        <td class="border p-2">
                            <a href="/admin/users/<?= $user["id"] ?>">Преглед</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require ("views/partials/foot.php"); ?>