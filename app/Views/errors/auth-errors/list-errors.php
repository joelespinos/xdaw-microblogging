<?php if (!empty($errors)): ?>
    <div class="mt-4 alert alert-danger" role="alert">
        <ul class="mb-0 list-unstyled">
            <?php foreach ($errors as $error): ?>
                <li class="my-1"><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>