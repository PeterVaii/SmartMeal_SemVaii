<?php

namespace App\Models;

use Framework\Core\Model;

class Recipe extends Model
{
    protected ?int $id = null;
    protected int $user_id;
    protected string $title;
    protected ?string $description = null;
    protected string $instructions;
    protected ?int $prep_time = null;
    protected ?int $servings = null;
    protected string $difficulty = 'easy';
    protected int $is_public = 1;
    protected ?string $created_at = null;

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->user_id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getInstructions(): string { return $this->instructions; }
    public function getPrepTime(): ?int { return $this->prep_time; }
    public function getServings(): ?int { return $this->servings; }
    public function getDifficulty(): string { return $this->difficulty; }
    public function getIsPublic(): bool { return $this->is_public === 1; }
    public function getCreatedAt(): ?string { return $this->created_at; }

    public function setUserId(int $userId): void { $this->user_id = $userId; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setInstructions(string $instructions): void { $this->instructions = $instructions; }
    public function setPrepTime(?int $prep_time): void { $this->prep_time = $prep_time; }
    public function setServings(?int $servings): void { $this->servings = $servings; }
    public function setDifficulty(string $difficulty): void { $this->difficulty = $difficulty; }
    public function setPublic(bool $isPublic): void { $this->is_public = $isPublic ? 1 : 0; }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
}