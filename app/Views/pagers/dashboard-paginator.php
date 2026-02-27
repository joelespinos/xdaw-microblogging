<?php $pager->setSurroundCount(4); ?>

<nav class="mt-4">
    <ul class="pagination justify-content-center">

        <li class="page-item <?= $pager->hasPreviousPage() ? '' : 'disabled' ?>">
            <a class="page-link bg-dark-gray border-gray text-white"
               href="<?= $pager->getFirst() ?>">
               &laquo;&laquo;
            </a>
        </li>

        <li class="page-item <?= $pager->hasPreviousPage() ? '' : 'disabled' ?>">
            <a class="page-link bg-dark-gray border-gray text-white"
               href="<?= $pager->getPreviousPage() ?>">
               &laquo;
            </a>
        </li>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item">
                <a class="page-link border-gray 
                    <?= $link['active'] 
                        ? 'btn-vivid' 
                        : 'bg-dark-gray text-white' ?>"
                   href="<?= $link['uri'] ?>">
                   <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <li class="page-item <?= $pager->hasNextPage() ? '' : 'disabled' ?>">
            <a class="page-link bg-dark-gray border-gray text-white"
               href="<?= $pager->getNextPage() ?>">
               &raquo;
            </a>
        </li>

        <li class="page-item <?= $pager->hasNextPage() ? '' : 'disabled' ?>">
            <a class="page-link bg-dark-gray border-gray text-white"
               href="<?= $pager->getLast() ?>">
               &raquo;&raquo;
            </a>
        </li>

    </ul>
</nav>