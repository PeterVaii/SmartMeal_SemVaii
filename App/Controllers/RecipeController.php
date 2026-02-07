<?php

namespace App\Controllers;

use App\Configuration;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
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
        $id = $request->value('id');
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

            if ($title === '' || $instructions === '') {
                $message = 'Vyplň názov a postup.';
            } else {
                $recipe = new Recipe();
                $recipe->setUserId((int)$this->user->getId());
                $recipe->setTitle($title);
                $recipe->setDescription($description !== '' ? $description : null);
                $recipe->setInstructions($instructions);
                $recipe->setPublic($isPublic);

                $recipe->setPrepTime($prepTime);
                $recipe->setServings($servings);
                $recipe->setDifficulty($difficulty);

                $recipe->save();
                $recipeId = (int)$recipe->getId();

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
        }
        return $this->html(compact('message'));
    }
}