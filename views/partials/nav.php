<div class="flex justify-between items-center py-4">
    <a href="/" class="text-2xl font-bold"><?= WEBSITE_TITLE ?></a>
    <ul class="flex items-center gap-2">
        <?php foreach(DESKTOP_NAV_LINKS as $navLink): ?>
            <li>
                <a href="<?= $navLink["link"] ?>" title="<?= $navLink["title"] ?>" class="block py-2 px-4 border rounded hover:bg-gray-200"><?= $navLink["name"] ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>