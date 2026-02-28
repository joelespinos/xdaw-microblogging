<?= $this->extend('layouts/dashboard-layout.php') ?>

<?= $this->section('dashboard-content') ?>

<main>
    <?php if (!empty($piwlades)): ?>
        
        <div class="container pt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">

                <?php if (session()->getFlashdata('error-advice')): ?>
                    <div class="text-center mb-5 alert alert-danger py-2" role="alert">
                        <?= session()->getFlashdata('error-advice') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('info-advice')): ?>
                    <div class="text-center mb-5 alert alert-info py-2" role="alert">
                        <?= session()->getFlashdata('info-advice') ?>
                    </div>
                <?php endif; ?>

                <?php foreach($piwlades as $piwlada): ?>

                    <div class="bg-dark-gray border-gray rounded-lg mb-4 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center">

                            <?php if ($piwlada->canManipulate): ?>
                                <div class="ms-2">
                                    <a href="<?= base_url('/dashboard/piw/edit/'.$piwlada->piwlada_uuid) ?>" class="text-decoration-none">
                                        <i class="fa-solid fa-pen-to-square fa-lg"></i>
                                    </a>
                                    <a type="button" class="btn btn-link p-0 text-decoration-none" onclick="confirmationDelete('<?= $piwlada->piwlada_uuid ?>')">
                                        <i class="fa-solid fa-trash fa-lg text-danger"></i>
                                    </a>
                                    <form id="delete-form-<?= $piwlada->piwlada_uuid ?>" method="post" action="<?= base_url('/dashboard/piw/delete/'.$piwlada->piwlada_uuid) ?>" class="d-none">
                                        <?= csrf_field() ?>
                                    </form>
                                </div>
                            <?php endif; ?>
                            <?php if ($piwlada->canChangeVisibility): ?>
                                <a type="button" class="ms-2 btn btn-link p-0 text-decoration-none" onclick="confirmationVisibility('<?= $piwlada->piwlada_uuid ?>')">
                                    <?php if ($piwlada->visibility === 'public'): ?>
                                        <i class="fa-solid fa-lock-open fa-lg"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-lock fa-lg"></i>
                                    <?php endif; ?>
                                </a>
                                <form id="visibility-form-<?= $piwlada->piwlada_uuid ?>" method="post" action="<?= base_url('/dashboard/piw/visibility/'.$piwlada->piwlada_uuid) ?>" class="d-none">
                                    <?= csrf_field() ?>
                                </form>
                            <?php endif; ?>
                            
                            <!-- USUARI -->
                            <div class="text-end py-3 ms-auto me-3">
                                <strong class="text-white">
                                    @<?= esc($piwlada->username) ?>
                                </strong>
                            </div>
                        </div>

                        <!-- CAROUSEL -->
                        <?php if (!empty($piwlada->media)): ?>
                            <div id="carousel-<?= esc($piwlada->piwlada_uuid) ?>" class="carousel slide" data-bs-ride="false">

                                <div class="carousel-inner">

                                    <?php foreach($piwlada->media as $index => $mediaItem): ?>
                                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                            <img src="<?= base_url('dashboard/media/' . $mediaItem->media_uuid) ?>" class="d-block w-100" style="object-fit: cover; max-height: 400px;" alt="imatge piwalda">  
                                        </div>
                                    <?php endforeach; ?>

                                </div>

                                <?php if (count($piwlada->media) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= esc($piwlada->piwlada_uuid) ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                    </button>

                                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= esc($piwlada->piwlada_uuid) ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                    </button>
                                <?php endif; ?>

                            </div>
                        <?php endif; ?>

                        <!-- CONTENT -->
                        <div class="p-3 top-border-gray text-white">
                            <?= esc($piwlada->content) ?>
                        </div>
                    </div>

                <?php endforeach; ?>

                </div>
            </div>
        </div>
                
    <?php endif; ?>
    
    <?= $pager->links('default', 'dashboard-paginator') ?>
                
</main>

<script>
    function confirmationDelete(uuid) {
        Swal.fire({
            title: '<i class="fa-solid fa-trash me-2 text-vivid-blue"></i>Confirma l\'esborrat',
            html: "<p>Estàs a punt d'esborrar aquesta piwlada. Aquesta acció és irreversible.</p>",
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Esborrar',
            cancelButtonText: 'Cancel·lar',
            customClass: {
                popup: 'bg-dark-gray text-white p-4 rounded-lg',
                confirmButton: 'btn-vivid px-4 py-2 rounded ms-5',
                cancelButton: 'btn text-vivid-blue px-4 py-2 rounded border border-vivid-blue'
            },
            buttonsStyling: false,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                // Submet el formulari existent amb CSRF
                const form = document.getElementById('delete-form-' + uuid);
                if (form) form.submit();
            }
        });
    }

    function confirmationVisibility(uuid) {
        Swal.fire({
            title: '<i class="fa-solid fa-eye me-2 text-vivid-blue"></i>Canviar visibilitat',
            html: "<p>Estàs a punt de canviar la visibilitat d'aquesta piwlada. Vols continuar?</p>",
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Canviar',
            cancelButtonText: 'Cancel·lar',
            customClass: {
                popup: 'bg-dark-gray text-white p-4 rounded-lg',
                confirmButton: 'btn-vivid px-4 py-2 rounded ms-5',
                cancelButton: 'btn text-vivid-blue px-4 py-2 rounded border border-vivid-blue'
            },
            buttonsStyling: false,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('visibility-form-' + uuid);
                if (form) form.submit();
            }
        });
    }
</script>
<?= $this->endSection(); ?>