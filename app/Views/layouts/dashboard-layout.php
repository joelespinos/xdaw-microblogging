<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XDaw | <?= esc($title) ?></title>
    
    <!-- CSS STYLE -->
    <link rel="stylesheet" href="<?= base_url("assets/bootstrap/css/bootstrap.min.css") ?>">
    <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
    <link rel="stylesheet" href="<?= base_url("assets/styles/custom.css") ?>">
    
    <!-- FONTAWESOME -->
    <link rel="preload" href="<?= base_url("assets/fontawesome/webfonts/fa-solid-900.woff2") ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= base_url("assets/fontawesome/webfonts/fa-regular-400.woff2") ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= base_url("assets/fontawesome/css/all.min.css") ?>">
</head>
<body class="text-white" style="height: 100%;">

    <header class="d-flex justify-content-between align-items-center bottom-border-gray py-2">
        <a href="<?= base_url("dashboard") ?>">
            <img src="<?= base_url("assets/images/xdaw-logo.png") ?>" alt="XDAW Logo" class="ms-1" width="120px">
        </a>

        <nav>
            <ul>
                <li><a href="<?= base_url("dashboard") ?>">Dashboard</a></li>
            </ul>
        </nav>

        <div class="dropdown">
            <a class="mb-0 me-3 text-reset text-decoration-none" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                @<?= esc(session()->user_username) ?>
            </a>

            <ul class="dropdown-menu bg-dark-gray text-white border-gray mt-1 p-2">
                <li class="d-flex justify-content-evenly align-items-center"><a class="text-reset text-decoration-none p-1" href="<?= base_url('/dashboard/piw/write') ?>">Escriu una piwlada</a><i class="fa-solid fa-pencil text-vivid-blue"></i></li>
                <li><hr class="dropdown-divider"></li>
                <li class="d-flex justify-content-evenly align-items-center"><a class="text-reset text-decoration-none p-1" href="<?= base_url('/logout') ?>">Tanca sessió</a><i class="fa-solid fa-arrow-right-from-bracket text-danger"></i></li>
            </ul>
        </div>
    </header>

    <?= $this->renderSection('dashboard-content') ?>

    <footer class="bg-dark-gray border-gray mt-4 py-3">
        <div class="ms-3">
            &copy; <?= date('Y') ?> XDaw. Creat per Joel Espinós amb molt d'amor per CodeIgniter.
        </div>
    </footer>
    
    <!-- SWEET ALERTS JS -->
     <script src="<?= base_url("assets/sweetalert2/js/sweetalert2.js") ?>"></script>
     
    <!-- BOOTSTRAP JS -->
    <script src="<?= base_url("assets/bootstrap/js/bootstrap.bundle.min.js") ?>"></script>
</body>
</html>