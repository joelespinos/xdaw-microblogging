<?= $this->extend('layouts/dashboard-layout.php') ?>

<?= $this->section('dashboard-content') ?>

<main>
    <?php if (!empty($piwlades)): ?>
        
        <div class="container pt-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">

                <?php foreach($piwlades as $piwlada): ?>

                    <div class="bg-dark-gray border-gray rounded-lg mb-4 overflow-hidden">

                        <!-- USUARI -->
                        <button>
                            <a href="<?= base_url('/dashboard/piw/edit/'.$piwlada->piwlada_uuid) ?>">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        </button>
                        <div class="text-end py-3 me-3 bottom-border-gray">
                            <strong class="text-white">
                                @<?= esc($piwlada->username) ?>
                            </strong>
                        </div>

                        <!-- CAROUSEL -->
                        <?php if (!empty($piwlada->media)): ?>
                            <div id="carousel-<?= esc($piwlada->piwlada_uuid) ?>" 
                                class="carousel slide" 
                                data-bs-ride="false">

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
<?= $this->endSection(); ?>