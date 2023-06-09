<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= $this->renderSection('title') ?></title>

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <link rel="icon" href="<?= $_ENV['app.baseURL'] ?>/favicon.svg">

    <?= $this->renderSection('pageStyles') ?>
</head>

<body class="bg-light">
    <main role="main" class="container">
        <div class="mt-4">
            <h2 class="text-center"><?= $_ENV['app.appTitle'] ?? '' ?></h2>
        </div>

        <?= $this->renderSection('main') ?>
    </main>

    <?= $this->renderSection('pageScripts') ?>
</body>
</html>
