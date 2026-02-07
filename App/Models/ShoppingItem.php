<?php

namespace App\Models;

use Framework\Core\Model;

class ShoppingItem extends Model
{
    protected static ?string $tableName = 'shopping_items';
    protected ?int $id = null;
    protected int $user_id;
    protected string $name;
    protected ?string $unit = null;
    protected int $is_checked = 0;

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->user_id; }
    public function getName(): string { return $this->name; }
    public function getUnit(): ?string { return $this->unit; }
    public function isChecked(): bool { return (bool)$this->is_checked; }

    public function setUserId(int $user_id): void { $this->user_id = $user_id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setUnit(?string $unit): void { $this->unit = $unit; }
    public function setChecked(bool $is_checked): void { $this->is_checked = $is_checked ? 1 : 0; }
}