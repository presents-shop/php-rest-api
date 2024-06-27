<?php require ("views/partials/head.php"); ?>
<?php require ("styles/index.php"); ?>

<header>
    <div class="border">
        <div class="container mx-auto">
            <?php require ("views/partials/nav.php"); ?>
        </div>
    </div>
    <h1 class="bg-gray-100 text-center py-10 text-4xl">Създаване на профил</h1>
</header>

<main>
    <div class="max-w-xl mx-auto mt-5">
        <div class="text-red-500 mb-5">
            <?= $errors["dublicate_email"] ?? null ?>
        </div>

        <form action="/users/register" method="POST">
            <div class="mb-5">
                <label for="email" class="block mb-1">Имейл</label>
                <input type="email" name="email" title="Въведете вашия имейл адрес" id="email" value="<?= $input["email"] ?? null ?>" class="<?= FORM_CONTROL ?>">

                <?php if (!empty($errors["email_empty"]) || !empty($errors["email_length"])): ?>
                    <div class="text-red-500">
                        <?= $errors["email_empty"] ?? null ?><br />
                        <?= $errors["email_length"] ?? null ?><br />
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-5">
                <label for="password" class="block mb-1">Нова парола</label>
                <?= $errors["email"] ?? null ?>
                <input type="password" name="password" title="Въведете вашата нова парола" id="password" value="<?= $input["password"] ?? null ?>" class="<?= FORM_CONTROL ?>">

                <?php if (!empty($errors["password_empty"]) || !empty($errors["password_length"])): ?>
                    <div class="text-red-500">
                        <?= $errors["password_empty"] ?? null ?><br />
                        <?= $errors["password_length"] ?? null ?><br />
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-5">
                <?= $errors["email"] ?? null ?>
                <label for="cpassword" class="block mb-1">Потвърдете паролата</label>
                <input type="password" name="cpassword" title="Потвърдете вашата нова парола" id="cpassword" value="<?= $input["cpassword"] ?? null ?>" class="<?= FORM_CONTROL ?>">

                <?php if (!empty($errors["passwords_match"])): ?>
                    <div class="text-red-500">
                        <?= $errors["passwords_match"] ?? null ?><br />
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-5">
                <?= $errors["email"] ?? null ?>
                <label for="code" class="block mb-1">Код за сигурност (<?= $code ?>)</label>
                <input type="text" name="code" title="Въведете кода за сигурност" id="code" class="<?= FORM_CONTROL ?>">

                <?php if (!empty($errors["invalid_code"])): ?>
                    <div class="text-red-500">
                        <?= $errors["invalid_code"] ?? null ?><br />
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <button type="submit" class="<?= PRIMARY_BUTTON ?>" title="Създашане на профил">Регистрация</button><br />
                <a href="/users/login" class="mt-5 <?= NAV_LINK ?>" title="Отиване към страницата за вход">Влизане в профила</a>
            </div>
        </form>
    </div>
</main>

<?php require ("views/partials/foot.php"); ?>
