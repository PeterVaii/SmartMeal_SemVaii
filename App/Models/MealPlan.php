<?php

namespace App\Models;

use Framework\Core\Model;

class MealPlan extends Model
{
    protected static ?string $tableName = 'meal_plans';
    protected ?int $id = null;
    protected int $user_id;
    protected int $recipe_id;
    protected string $day;
    protected ?string $created_at = null;

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->user_id; }
    public function getRecipeId(): int { return $this->recipe_id; }
    public function getDay(): string { return $this->day; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    public function setUserId(int $user_id): void { $this->user_id = $user_id; }
    public function setRecipeId(int $recipe_id): void { $this->recipe_id = $recipe_id; }
    public function setDay(string $day): void { $this->day = $day; }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
}