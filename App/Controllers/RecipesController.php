<?php

namespace App\Controllers;

use App\Models\Recipe;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class RecipesController extends BaseController
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
}