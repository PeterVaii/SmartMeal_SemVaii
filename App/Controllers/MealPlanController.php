<?php

namespace App\Controllers;

use App\Configuration;
use App\Models\MealPlan;
use App\Models\Recipe;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class MealPlanController extends BaseController
{
    /**
     * @throws Exception
     */
    public function authorize(Request $request, string $action): bool
    {
        if (!$this->user->isLoggedIn()) {
            return false;
        }

        switch ($action) {
            case 'index':
            case 'add':
                return true;
            case 'remove':
                $id = (int)$request->value('id');
                $mp = MealPlan::getOne($id);
                return $mp !== null && ($mp->getUserId() === (int)$this->user->getId());
            default:
                return false;
        }
    }

    /**
     * @throws Exception
     */
    public function index(Request $request): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->redirect(Configuration::LOGIN_URL);
        }

        $plans = MealPlan::getAll('`user_id` = ?', [$this->user->getId()]);
        $recipes = Recipe::getAll('`is_public` = 1 OR `user_id` = ?', [$this->user->getId()]);

        return $this->html(compact('plans', 'recipes'));
    }

    /**
     * @throws Exception
     */
    public function add(Request $request): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->redirect(Configuration::LOGIN_URL);
        }

        $day = (string)$request->value('day');
        $recipeId = (int)$request->value('recipe_id');

        $mp = new MealPlan();
        $mp->setUserId($this->user->getId());
        $mp->setRecipeId($recipeId);
        $mp->setDay($day);
        $mp->save();

        return $this->redirect($this->url('mealplan.index'));
    }

    /**
     * @throws Exception
     */
    public function remove(Request $request): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->redirect(Configuration::LOGIN_URL);
        }

        $id = (int)$request->value('id');
        $mp = MealPlan::getOne($id);

        if ($mp === null || $mp->getUserId() !== $this->user->getId()) {
            return $this->redirect($this->url('mealplan.index'));
        }

        $mp->delete();
        return $this->redirect($this->url('mealplan.index'));
    }
}