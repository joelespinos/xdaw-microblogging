<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XDaw | <?= esc($title) ?></title>
    
    <!-- CSS STYLE -->
    <link rel="stylesheet" href="<?= base_url("assets/bootstrap/css/bootstrap.min.css") ?>">
    <link rel="stylesheet" href="<?= base_url("assets/styles/custom.css") ?>">
    
    <!-- FONTAWESOME -->
    <link rel="preload" href="<?= base_url("assets/fontawesome/webfonts/fa-solid-900.woff2") ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= base_url("assets/fontawesome/webfonts/fa-regular-400.woff2") ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= base_url("assets/fontawesome/css/all.min.css") ?>">
</head>
<body>
    <div class="mt-5 d-flex justify-content-center">
        <img src="<?= base_url("assets/images/xdaw-logo.png") ?>" alt="XDAW Logo" width="200px">
    </div>

    <?= $this->renderSection('auth-form-content') ?>
    
    <!-- BOOTSTRAP JS -->
    <script src="<?= base_url("assets/bootstrap/js/bootstrap.bundle.min.js") ?>"></script>
</body>
</html>