<!DOCTYPE html>
<html lang="<?= WEBSITE_LANG ?>">

<head>
    <meta charset="<?= CHARSET ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= $meta["title"] ?>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        clifford: '#da373d',
                    }
                }
            }
        }
    </script>
</head>

<body>
