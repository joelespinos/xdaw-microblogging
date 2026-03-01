<?= $this->extend('layouts/dashboard-layout.php') ?>

<?= $this->section('dashboard-content') ?>

<main style="flex: 1 0 auto;">        
    <div class="container pt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <?php if (session()->getFlashdata('info-advice')): ?>
                    <div class="text-center mb-3 alert alert-info py-2" role="alert">
                        <?= session()->getFlashdata('info-advice') ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-6 d-flex">

                        <div class="bg-dark-gray border-gray rounded-lg overflow-hidden h-100 d-flex flex-column w-100">                   

                            <div class="text-end py-3 ms-auto me-3">
                                <strong class="text-white">
                                    @<?= esc($piwlada->username) ?>
                                </strong>
                            </div>

                            <?php if (!empty($piwlada->media)): ?>
                                <div id="carousel-<?= esc($piwlada->piwlada_uuid) ?>" class="carousel slide carousel-fixed-height" data-bs-ride="false">
                                    <div class="carousel-inner">
                                        <?php foreach($piwlada->media as $index => $mediaItem): ?>
                                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                <img src="<?= base_url('dashboard/media/' . $mediaItem->media_uuid) ?>" class="d-block w-100 carousel-img" alt="imatge piwalda">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php if (count($piwlada->media) > 1): ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= esc($piwlada->piwlada_uuid) ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= esc($piwlada->piwlada_uuid) ?>" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="p-3 top-border-gray text-white mt-auto">
                                <?= $piwlada->content ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 d-flex">
                        <div class="bg-dark-gray border-gray rounded-lg p-4 h-100 d-flex flex-column w-100">
                            <h5 class="text-white mb-4">
                                <i class="fa-solid fa-comments me-2 text-vivid-blue"></i>
                                Comentaris
                            </h5>

                            <div class="flex-grow-1 overflow-auto">

                                <?php if (!empty($piwlada->comments)): ?>

                                    <?php foreach ($piwlada->comments as $comment): ?>
                                        <div class="mb-3 pb-3 bottom-border-gray">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-vivid-blue">
                                                    @<?= esc($comment->username) ?>
                                                </strong>

                                                <small class="text-white">
                                                    <?= date('d/m/Y H:i', strtotime($comment->created_at)) ?>
                                                </small>
                                            </div>

                                            <div class="text-white">
                                                <?= esc($comment->content) ?>
                                            </div>
                                        </div>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <div class="text-center text-white py-3">
                                        Encara no hi ha comentaris en aquesta piwlada.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($piwlada->comments)): ?>
                                <div class="mb-3">
                                    <?= $pager->links('default', 'dashboard-paginator') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-gray border-gray rounded-lg p-4 mt-4">

                    <form action="" method="post" class="d-flex flex-column" id="piwladaForm">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="commentContent" class="form-label text-white">
                                <i class="fa-solid fa-pencil text-vivid-blue"></i> Contingut del comentari
                            </label>
                            <textarea name="commentContent" id="commentContent" class="form-control"></textarea>
                        </div>

                        <button type="submit" class="btn-vivid rounded py-2 btn-lg">
                            Comentar
                        </button>

                        <?= validation_list_errors('list-errors') ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    var easyMDE = new EasyMDE({
        element: document.getElementById('commentContent'),
        spellChecker: false,
        placeholder: "Escriu aquí fent servir Markdown...",
        autoDownloadFontAwesome: false,
        minHeight: "30px"
    });

    easyMDE.codemirror.focus();

});
</script>

<?= $this->endSection(); ?>