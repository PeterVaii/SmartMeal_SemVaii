<?php
/** @var LinkGenerator $link */
/** @var AppUser $user */
/** @var Recipe $recipe */
/** @var RecipeIngredient[] $ingredients */
/** @var string|null $message */

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use Framework\Auth\AppUser;
use Framework\Support\LinkGenerator;

$e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!--Pomáhanie s dizajnom ChatGPT-->
<div class="container page-narrow">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="fw-bold mb-0">Upraviť recept</h1>
        <a class="btn btn-outline-secondary btn-sm"
           href="<?= $link->url('recipe.show', ['id' => (int)$recipe->getId()]) ?>">Späť</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-danger"><?= $e($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="card mb-3">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Názov</label>
                    <input name="title" class="form-control"
                           value="<?= $e($recipe->getTitle()) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Popis</label>
                    <textarea name="description" class="form-control"
                              rows="2"><?= $e($recipe->getDescription() ?? '') ?></textarea>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Čas prípravy (min)</label>
                        <input name="prep_time" type="number" min="1" class="form-control"
                               value="<?= $e($recipe->getPrepTime() ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Počet porcií</label>
                        <input name="servings" type="number" min="1" class="form-control"
                               value="<?= $e($recipe->getServings() ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Náročnosť</label>
                        <select name="difficulty" class="form-select">
                            <option value="easy"   <?= $recipe->getDifficulty() === 'easy' ? 'selected' : '' ?>>ľahká</option>
                            <option value="medium" <?= $recipe->getDifficulty() === 'medium' ? 'selected' : '' ?>>stredná</option>
                            <option value="hard"   <?= $recipe->getDifficulty() === 'hard' ? 'selected' : '' ?>>ťažká</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Postup</label>
                    <textarea name="instructions" class="form-control"
                              rows="8" required><?= $e($recipe->getInstructions()) ?></textarea>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_public" id="is_public"
                        <?= $recipe->getIsPublic() ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_public">Verejný recept</label>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Ingrediencie</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-ingredient">
                        + Pridať ingredienciu
                    </button>
                </div>

                <div id="ingredients-list">
                    <?php if (empty($ingredients)): ?>
                        <div class="row g-2 mb-2 ingredient-row">
                            <div class="col-md-5">
                                <input class="form-control" name="ing_name[]" placeholder="Napr. Špagety">
                            </div>
                            <div class="col-md-2">
                                <input class="form-control" name="ing_amount[]" placeholder="200">
                            </div>
                            <div class="col-md-2">
                                <input class="form-control" name="ing_unit[]" placeholder="g / ml / ks">
                            </div>
                            <div class="col-md-3 d-grid">
                                <button type="button"
                                        class="btn btn-outline-danger remove-ingredient">
                                    Odstrániť
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ingredients as $ing): ?>
                            <div class="row g-2 mb-2 ingredient-row">
                                <div class="col-md-5">
                                    <input class="form-control" name="ing_name[]"
                                           value="<?= $e($ing->getName()) ?>">
                                </div>
                                <div class="col-md-2">
                                    <input class="form-control" name="ing_amount[]"
                                           value="<?= $e($ing->getAmount() ?? '') ?>">
                                </div>
                                <div class="col-md-2">
                                    <input class="form-control" name="ing_unit[]"
                                           value="<?= $e($ing->getUnit() ?? '') ?>">
                                </div>
                                <div class="col-md-3 d-grid">
                                    <button type="button"
                                            class="btn btn-outline-danger remove-ingredient">
                                        Odstrániť
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <template id="ingredient-row-template">
                    <div class="row g-2 mb-2 ingredient-row">
                        <div class="col-md-5">
                            <input class="form-control" name="ing_name[]" placeholder="Napr. Špagety">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" name="ing_amount[]" placeholder="200">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control" name="ing_unit[]" placeholder="g / ml / ks">
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="button"
                                    class="btn btn-outline-danger remove-ingredient">
                                Odstrániť
                            </button>
                        </div>
                    </div>
                </template>

                <div class="text-muted small mt-2">
                    Tip: prázdne riadky sa ignorujú.
                </div>
            </div>
        </div>

        <button class="btn btn-primary" type="submit" name="submit" value="1">
            Uložiť zmeny
        </button>
        <a class="btn btn-outline-secondary"
           href="<?= $link->url('recipe.show', ['id' => (int)$recipe->getId()]) ?>">
            Zrušiť
        </a>
    </form>
</div>