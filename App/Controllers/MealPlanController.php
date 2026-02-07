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
    public function index(Request $request): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->redirect(Configuration::LOGIN_URL);
        }

        $plans = MealPlan::getAll('`user_id` = ?', [$this->user->getId()]);
        $recipes = Recipe::getAll('`is_public` = 1 OR `user_id` = ?', [$this->user->getId()]);

        return $this->html(compact('plans', 'recipes'));
    }
}