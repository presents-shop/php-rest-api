<?php require ("views/admin/partials/head.php"); ?>
<?php require ("styles/index.php"); ?>

<header>
    <div class="border">
        <div class="container mx-auto">
            <?php require ("views/admin/partials/nav.php"); ?>
        </div>
    </div>
    <h1 class="bg-gray-100 text-center py-10 text-4xl">Създаване на нова категория</h1>
</header>

<main>
    <div class="max-w-xl mx-auto mt-5 mb-10">
        <form action="/admin/categories/create" method="POST" enctype="multipart/form-data">
            <div class="mb-5">
                <label for="title" class="block mb-1">Заглавие</label>
                <input type="text" name="title" title="Въведете заглавие" id="title" value="<?= $input["title"] ?? null ?>" class="<?= FORM_CONTROL ?>">

                <?php if (!empty($errors["title"])): ?>
                    <div class="text-red-500">
                        <?= $errors["title"] ?? null ?><br />
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-5">
                <label for="slug" class="block mb-1">Адрес</label>
                <input type="text" name="slug" title="Въведете слуг" id="slug" value="<?= $input["slug"] ?? null ?>" class="<?= FORM_CONTROL ?>">

                <?php if (!empty($errors["slug"])): ?>
                    <div class="text-red-500">
                        <?= $errors["slug"] ?? null ?><br />
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-5">
                <label for="description" class="block mb-1">Описание</label>
                <textarea name="description" id="description" rows="10" title="Въведете кртко описание на категорията" class="<?= FORM_CONTROL ?>"><?= $input["description"] ?? null ?></textarea>
            </div>

            <div class="mb-5">
                <label for="thumbnail" class="block mb-1">Адрес</label>
                <input type="file" name="thumbnail" title="Качване на предна снимка" accept="png,jpg,jpeg" id="thumbnail" class="<?= FORM_CONTROL ?>">
            </div>

            <div class="mb-5">
                <label for="code" class="block mb-1">Код за сигурност (<?= $code ?>)</label>
                <input type="text" name="code" title="Въведете кода за сигурност" id="code" class="<?= FORM_CONTROL ?>">

                <?php if (!empty($errors["invalid_code"])): ?>
                    <div class="text-red-500">
                        <?= $errors["invalid_code"] ?? null ?><br />
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <button type="submit" class="<?= PRIMARY_BUTTON ?>">Създаване</button><br />
            </div>
        </form>
    </div>
</main>

<?php require ("views/admin/partials/foot.php"); ?>
