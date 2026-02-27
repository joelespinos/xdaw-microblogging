<?= $this->extend('layouts/auth-forms-layout.php') ?>

<?= $this->section('auth-form-content') ?>

<div class="mt-4 mx-auto bg-dark-gray p-4 text-white rounded-lg" 
     style="width: 500px; box-shadow: 0 5px 25px #0a52bf9d;">

    <h2 class="text-vivid-blue text-center">Registra't</h2>

    <form action="" method="post" class="d-flex flex-column mt-3">
        <?= csrf_field() ?>

        <label for="username">
            <i class="fa-solid fa-user text-vivid-blue"></i>
            Nom d'usuari
        </label>
        <input type="text" name="username" id="username" placeholder="nom d'usuari" value="<?= old('username') ?>">

        <label for="email" class="mt-3">
            <i class="fa-solid fa-at text-vivid-blue"></i>
            Correu electrònic
        </label>
        <input type="email" name="email" id="email" placeholder="exemple@gmail.com" value="<?= old('email') ?>">

        <label for="password" class="mt-3">
            <i class="fa-solid fa-key text-vivid-blue"></i>
            Contrasenya
        </label>
        <input type="password" name="password" id="password" placeholder="contrasenya">

        <label for="confirmPassword" class="mt-3">
            <i class="fa-solid fa-lock text-vivid-blue"></i>
            Confirma la contrasenya
        </label>
        <input type="password" name="confirmPassword" id="confirmPassword" placeholder="confirma la contrasenya">

        <?= render_captcha() ?>

        <button type="submit" class="btn-vivid rounded py-2">
            Registrar-se
        </button>
    </form>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="text-center mt-3 mb-0 alert alert-danger py-2" role="alert">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <p class="text-center mt-3 mb-0">
        Ja tens compta? 
        <a href="<?= base_url('/login') ?>">Inicia sessió</a>
    </p>

    <?= validation_list_errors('list-errors') ?>

</div>

<?= $this->endSection(); ?>