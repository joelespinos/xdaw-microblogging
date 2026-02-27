<?= $this->extend('layouts/dashboard-layout.php') ?>
<?= $this->section('dashboard-content') ?>

<main class="d-flex justify-content-center mt-5 mb-5">
    <div class="bg-dark-gray text-white p-5 rounded-lg shadow" style="max-width: 700px; width: 100%;">

        <h2 class="text-vivid-blue text-center mb-4">Escriu la teva piwlada!</h2>

        <form action="" method="post" enctype="multipart/form-data" class="d-flex flex-column" id="piwladaForm">
            <?= csrf_field() ?>

            <!-- CONTINGUT MARKDOWN -->
            <div class="mb-3">
                <label for="piwladaContent" class="form-label">
                    <i class="fa-solid fa-pencil text-vivid-blue"></i> Contingut de la piwlada
                </label>
                <textarea name="piwladaContent" id="piwladaContent" class="form-control"></textarea>
            </div>

            <!-- DROP ZONE IMATGES -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="fa-solid fa-image text-vivid-blue"></i> Penja les teves imatges
                </label>

                <div id="dropZone" class="drop-zone text-center rounded p-4">
                    Arrossega imatges aquí o fes clic
                </div>

                <ul id="fileList" class="mt-3 list-unstyled"></ul>
            </div>

            <button type="submit" class="btn-vivid rounded py-2 btn-lg">Publicar</button>

            <?= validation_list_errors('list-errors') ?>
        </form>
    </div>
</main>
<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // EASYMDE
    var easyMDE = new EasyMDE({
        element: document.getElementById('piwladaContent'),
        spellChecker: false,
        placeholder: "Escriu aquí fent servir Markdown...",
        autoDownloadFontAwesome: false,
        minHeight: "150px"
    });

    easyMDE.codemirror.focus();

    // DRAG & DROP
    const form = document.getElementById('piwladaForm');
    const dropZone = document.getElementById('dropZone');
    const fileList = document.getElementById('fileList');

    let filesInMemory = [];

    dropZone.addEventListener('click', () => {
        const tempInput = document.createElement('input');
        tempInput.type = 'file';
        tempInput.multiple = true;
        tempInput.accept = "image/png, image/jpeg";
        tempInput.onchange = e => {
            addFiles(Array.from(e.target.files));
        };
        tempInput.click();
    });

    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('hover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('hover');
    });

    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('hover');
        addFiles(Array.from(e.dataTransfer.files));
    });

    function addFiles(newFiles) {
        newFiles.forEach(file => {
            if (file.type.startsWith("image/")) {
                filesInMemory.push(file);
            }
        });
        renderList();
    }

    function renderList() {
        fileList.innerHTML = '';
        filesInMemory.forEach((file, index) => {
            const li = document.createElement('li');
            li.innerHTML = `
                <span>${file.name}</span>
                <button type="button" class="btn-remove"><i class="fa-solid fa-x"></i></button>
            `;

            li.querySelector('.btn-remove').onclick = () => {
                filesInMemory.splice(index, 1);
                renderList();
            };

            fileList.appendChild(li);
        });
    }

    // SUBMIT
    form.addEventListener('submit', function(e) {

        if (filesInMemory.length > 0) {
            const dataTransfer = new DataTransfer();

            filesInMemory.forEach(file => {
                dataTransfer.items.add(file);
            });

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'file';
            hiddenInput.name = 'piwladaMedias[]';
            hiddenInput.multiple = true;
            hiddenInput.files = dataTransfer.files;
            hiddenInput.style.display = 'none';

            form.appendChild(hiddenInput);
        }
    });

});
</script>

<?= $this->endSection(); ?>