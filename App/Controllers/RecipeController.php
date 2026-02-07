<?php

namespace App\Controllers;

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
}