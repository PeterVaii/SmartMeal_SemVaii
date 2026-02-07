<?php

namespace App\Controllers;

use App\Configuration;
use App\Models\MealPlan;
use App\Models\RecipeIngredient;
use Exception;
use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;
use App\Models\ShoppingItem;

class ShoppingItemController extends BaseController
{
    /**
     * @throws Exception
     */
    public function index(Request $request): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->redirect(Configuration::LOGIN_URL);
        }

        $userId = (int)$this->user->getId();

        $recipeCounts = $this->getRecipeCountsForUser($userId);
        $items = $this->buildShoppingItemsFromRecipeCounts($recipeCounts);
        $this->applyCheckedState($userId, $items);

        $recipeIds = array_keys($recipeCounts);

        return $this->html(compact('items', 'recipeIds'));
    }

    /**
     * @throws Exception
     */
    public function toggle(): Response
    {
        if (!$this->user->isLoggedIn()) {
            return $this->json(['ok' => false, 'error' => 'not_logged_in']);
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];

        $name = trim((string)($data['name'] ?? ''));
        $unit = trim((string)($data['unit'] ?? ''));
        $unit = $unit !== '' ? $unit : null;
        $checked = !empty($data['checked']) ? 1 : 0;

        if ($name === '') {
            return $this->json(['ok' => false, 'error' => 'missing_name']);
        }

        $userId = (int)$this->user->getId();

        $existing = ShoppingItem::getAll(
            '`user_id` = ? AND `name` = ? AND COALESCE(`unit`, \'\') = COALESCE(?, \'\')',
            [$userId, $name, $unit],
            limit: 1
        );
        $item = $existing[0] ?? null;

        if ($item === null) {
            $item = new ShoppingItem();
            $item->setUserId($userId);
            $item->setName($name);
            $item->setUnit($unit);
        }

        $item->setChecked($checked === 1);
        $item->save();

        return $this->json(['ok' => true]);
    }

    /**
     * @throws Exception
     */
    private function getRecipeCountsForUser(int $userId): array
    {
        $plans = MealPlan::getAll('`user_id` = ?', [$userId]);

        $counts = [];
        foreach ($plans as $p) {
            $rid = $p->getRecipeId();
            $counts[$rid] = ($counts[$rid] ?? 0) + 1;
        }
        return $counts; // [recipeId => times]
    }

    /**
     * @throws Exception
     */
    private function buildShoppingItemsFromRecipeCounts(array $recipeCounts): array
    {
        $recipeIds = array_keys($recipeCounts);
        if (empty($recipeIds)) return [];

        $placeholders = implode(',', array_fill(0, count($recipeIds), '?'));
        $ings = RecipeIngredient::getAll("`recipe_id` IN ($placeholders)", $recipeIds);

        $items = []; // key => item
        foreach ($ings as $ing) {
            $rid = $ing->getRecipeId();
            $mult = $recipeCounts[$rid] ?? 1;

            $nameNorm = trim(mb_strtolower($ing->getName()));
            $unitNorm = $ing->getUnit() ? trim(mb_strtolower($ing->getUnit())) : '';
            $key = $nameNorm . '|' . $unitNorm;

            if (!isset($items[$key])) {
                $items[$key] = [
                    'name' => $ing->getName(),
                    'unit' => $ing->getUnit(),
                    'amount' => 0.0,
                    'hasAmount' => false,
                    'countNoAmount' => 0,
                    'times' => 0,
                ];
            }

            $items[$key]['times'] += $mult;

            $amount = $ing->getAmount();
            if ($amount !== null) {
                $items[$key]['amount'] += $amount * $mult;
                $items[$key]['hasAmount'] = true;
            } else {
                $items[$key]['countNoAmount'] += $mult;
            }
        }

        $out = array_values($items);
        usort($out, fn($a, $b) => strcasecmp($a['name'], $b['name']));
        return $out;
    }

    /**
     * @throws Exception
     */
    private function applyCheckedState(int $userId, array &$items): void
    {
        $checked = ShoppingItem::getAll('`user_id` = ?', [$userId]);

        $map = [];
        foreach ($checked as $c) {
            $key = trim(mb_strtolower($c->getName())) . '|' . trim(mb_strtolower($c->getUnit() ?? ''));
            $map[$key] = $c->getIsChecked();
        }

        foreach ($items as &$it) {
            $key = trim(mb_strtolower($it['name'])) . '|' . trim(mb_strtolower($it['unit'] ?? ''));
            $it['checked'] = $map[$key] ?? false;
        }
        unset($it);
    }
}