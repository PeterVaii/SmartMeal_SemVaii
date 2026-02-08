<?php
/** @var LinkGenerator $link */
/** @var AppUser $user */
/** @var array $recipes */

use Framework\Auth\AppUser;
use Framework\Support\LinkGenerator;
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="fw-bold mb-0">Recepty</h1>

        <?php if ($user->isLoggedIn()) { ?>
            <a class="btn btn-primary btn-sm" href="<?= $link->url('recipe.create') ?>">+ Pridať recept</a>
        <?php } else { ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?= $link->url('auth.login') ?>">Prihlásiť sa pre pridanie</a>
        <?php } ?>
    </div>

    <div class="mb-3">
        <label for="recipe-search"></label>
        <input id="recipe-search" class="form-control" type="text" placeholder="Vyhľadať recept podľa názvu...">
    </div>

    <?php if (empty($recipes)) { ?>
        <div class="alert alert-info">
            Zatiaľ tu nie sú žiadne verejné recepty.
        </div>
    <?php } else { ?>
        <div class="list-group" id="recipes-list">
            <?php foreach ($recipes as $r) { ?>
                <a class="list-group-item list-group-item-action"
                   href="<?= $link->url('recipe.show', ['id' => (int)$r->getId()]) ?>">
                    <div class="fw-semibold">
                        <?= htmlspecialchars($r->getTitle(), ENT_QUOTES, 'UTF-8') ?>
                        <?php if (!$r->getIsPublic()) { ?>
                            <span class="badge bg-secondary ms-2">súkromný</span>
                        <?php } ?>
                    </div>

                    <?php if ($r->getDescription()) { ?>
                        <div class="text-muted small">
                            <?= htmlspecialchars($r->getDescription(), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php } ?>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</div>