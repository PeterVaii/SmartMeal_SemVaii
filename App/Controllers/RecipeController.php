<?php

namespace App\Controllers;

use App\Configuration;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\RedirectResponse;
use Framework\Http\Responses\Response;

class RecipeController extends BaseController
{
    /**
     * @throws Exception
     */
    public function index(Request $request): Response
    {
        $where = '`is_public` = 1';
        $params = [];

        if ($this->user->isLoggedIn())
        {
            $where = '(`is_public` = 1 OR `user_id` = ?)';
            $params = [$this->user->getId()];
        }

        $recipes = Recipe::getAll($where, $params, orderBy: '`created_at` DESC');

        return $this->html(compact('recipes'));
    }

    /**
     * @throws Exception
     */
    public function show(Request $request): Response
    {
        $id = (int)$request->value('id');
        $recipe = Recipe::getOne($id);

        if ($recipe === null) {
            return $this->html([
                'recipe' => null,
                'ingredients' => [],
            ]);
        }

        $ingredients = RecipeIngredient::getAll('`recipe_id` = ?', [$id]);

        return $this->html(compact('recipe', 'ingredients'));
    }

    /**
     * @throws Exception
     */
    public function create(Request $request): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->redirect(Configuration::LOGIN_URL);
        }

        $message = null;

        if ($request->hasValue('submit')) {
            $data = $this->parseRecipeFields($request);

            if ($data['title'] === '' || $data['instructions'] === '') {
                $message = 'Vyplň názov a postup.';
            } else {
                $recipe = new Recipe();
                $recipe->setUserId((int)$this->user->getId());
                $this->applyRecipeFieldsAndSave($recipe, $data);
                $recipeId = (int)$recipe->getId();

                return $this->saveIngredientsAndRedirect($request, $recipeId);
            }
        }
        return $this->html(compact('message'));
    }

    /**
     * @throws Exception
     */
    public function edit(Request $request): Response
    {
        $recipe = $this->requireOwnedRecipe($request);
        $recipeId = (int)$recipe->getId();

        $ingredients = RecipeIngredient::getAll('`recipe_id` = ?', [$recipeId], orderBy: '`id` asc');

        $message = null;

        if ($request->hasValue('submit')) {
            $data = $this->parseRecipeFields($request);

            if ($data['title'] === '' || $data['instructions'] === '') {
                $message = 'Vyplň názov a postup.';
            } else {
                $this->applyRecipeFieldsAndSave($recipe, $data);

                foreach ($ingredients as $old) {
                    $old->delete();
                }

                return $this->saveIngredientsAndRedirect($request, $recipeId);
            }
        }
        return $this->html(compact('recipe', 'ingredients', 'message'));
    }

    /**
     * @throws Exception
     */
    public function delete(Request $request): Response
    {
        $recipe = $this->requireOwnedRecipe($request);
        $recipe->delete();
        return $this->redirect($this->url('recipe.index'));
    }

    /**
     * @throws Exception
     */
    public function search(Request $request): Response
    {
        $q = trim((string)($request->value('q') ?? ''));
        $qLike = '%' . $q . '%';

        $where = '`is_public` = 1';
        $params = [];

        if ($this->user->isLoggedIn()) {
            $where = '(`is_public` = 1 OR `user_id` = ?)';
            $params[] = (int)$this->user->getId();
        }

        if ($q !== '') {
            $where .= ' AND `title` LIKE ?';
            $params[] = $qLike;
        }

        $recipes = Recipe::getAll($where, $params, orderBy: '`created_at` DESC', limit: 30);

        $out = array_map(fn($r) => [
            'id' => (int)$r->getId(),
            'title' => $r->getTitle(),
            'description' => $r->getDescription(),
            'is_public' => $r->getIsPublic(),
        ], $recipes);

        return $this->json(['ok' => true, 'items' => $out]);
    }

    private function parseRecipeFields(Request $request): array
    {
        $title = trim((string)$request->value('title'));
        $description = trim((string)$request->value('description'));
        $instructions = trim((string)$request->value('instructions'));
        $isPublic = $request->hasValue('is_public');

        $prepTimeRaw = trim((string)($request->value('prep_time') ?? ''));
        $servingsRaw = trim((string)($request->value('servings') ?? ''));
        $difficulty = (string)($request->value('difficulty') ?? 'easy');

        $prepTime = $prepTimeRaw === '' ? null : (int)$prepTimeRaw;
        $servings = $servingsRaw === '' ? null : (int)$servingsRaw;

        if (!in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            $difficulty = 'easy';
        }

        return [
            'title' => $title,
            'description' => ($description !== '' ? $description : null),
            'instructions' => $instructions,
            'is_public' => $isPublic,
            'prep_time' => $prepTime,
            'servings' => $servings,
            'difficulty' => $difficulty,
        ];
    }

    /**
     * @param Request $request
     * @param int $recipeId
     * @return RedirectResponse
     * @throws Exception
     */
    private function saveIngredientsAndRedirect(Request $request, int $recipeId): RedirectResponse
    {
        $names = (array)($request->value('ing_name') ?? []);
        $amounts = (array)($request->value('ing_amount') ?? []);
        $units = (array)($request->value('ing_unit') ?? []);

        for ($i = 0; $i < count($names); $i++) {
            $name = trim((string)$names[$i]);
            if ($name === '') continue;

            $ing = new RecipeIngredient();
            $ing->setRecipeId($recipeId);
            $ing->setName($name);

            $amountRaw = isset($amounts[$i]) ? trim((string)$amounts[$i]) : '';
            $amount = ($amountRaw === '') ? null : (float)str_replace(',', '.', $amountRaw);
            $ing->setAmount($amount);

            $unit = isset($units[$i]) ? trim((string)$units[$i]) : '';
            $ing->setUnit($unit !== '' ? $unit : null);

            $ing->save();
        }
        return $this->redirect("?c=recipe&a=show&id=" . $recipeId);
    }

    /**
     * @param Recipe $recipe
     * @param array $data
     * @return void
     * @throws Exception
     */
    private function applyRecipeFieldsAndSave(Recipe $recipe, array $data): void
    {
        $recipe->setTitle($data['title']);
        $recipe->setDescription($data['description']);
        $recipe->setInstructions($data['instructions']);
        $recipe->setPublic($data['is_public']);
        $recipe->setPrepTime($data['prep_time']);
        $recipe->setServings($data['servings']);
        $recipe->setDifficulty($data['difficulty']);

        $recipe->save();
    }

    /**
     * @throws Exception
     */
    private function requireOwnedRecipe(Request $request): Recipe
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect(Configuration::LOGIN_URL);
            exit;
        }

        $id = (int)$request->value('id');
        $recipe = Recipe::getOne($id);

        if ($recipe === null) {
            $this->redirect($this->url('recipe.index'));
            exit;
        }

        if ($recipe->getUserId() !== $this->user->getId()) {
            $this->redirect("?c=recipe&a=show&id=" . $id);
            exit;
        }
        return $recipe;
    }
}