<?php

namespace App\Models;

use Framework\Core\Model;

class User extends Model
{
    protected ?int $id = null;
    protected string $username;
    protected string $password_hash;
    protected ?string $created_at = null;
    protected ?string $email = null;

    public function getId(): ?int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getPasswordHash(): string { return $this->password_hash; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getEmail(): ?string { return $this->email; }

    public function setUsername(string $username): void { $this->username = $username; }
    public function setPasswordHash(string $hash): void { $this->password_hash = $hash; }
    public function setCreatedAt(?string $createdAt): void { $this->created_at = $createdAt; }
    public function setEmail(?string $email): void { $this->email = $email; }
}