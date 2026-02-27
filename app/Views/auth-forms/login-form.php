<?= $this->extend('layouts/auth-forms-layout.php') ?>

<?= $this->section('auth-form-content') ?>

<div class="mt-4 mx-auto bg-dark-gray p-4 text-white rounded-lg" style="width: 500px; box-shadow: 0 5px 25px #0a52bf9d;">
    <h2 class="text-vivid-blue text-center">Inicia sessió</h2>
    <form action="" method="post" class="d-flex flex-column mt-3">
        <?= csrf_field() ?>

        <label for="email">
            <i class="fa-solid fa-at text-vivid-blue"></i>
            Correu electrònic
        </label>
        <input type="email" name="email" id="email" placeholder="exemple@gmail.com" value="<?= old('email') ?>">

        <label for="password" class="mt-3">
            <i class="fa-solid fa-key text-vivid-blue"></i>
            Contrasenya
        </label>
        <input type="password" name="password" id="password" placeholder="contrasenya">

        <?= render_captcha() ?>

        <button type="submit" class="btn-vivid rounded py-2">Iniciar sessió</button>
    </form>
        
    <p class="text-center mt-3 mb-0">Encara no tens compta? <a href="<?= base_url('/register') ?>">Registra't</a></p>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="text-center mt-3 mb-0 alert alert-danger py-2" role="alert">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('msg')): ?>
        <div class="text-center mt-3 mb-0 alert alert-info py-2" role="alert">
            <?= session()->getFlashdata('msg') ?>
        </div>
    <?php endif; ?>

</div>

<?= $this->endSection(); ?>