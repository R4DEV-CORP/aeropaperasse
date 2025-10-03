<?php

namespace App\Services\Auth;

use App\Models\User;

class LoginResult
{
    public function __construct(
        private bool $success,
        private ?User $user = null,
        private ?string $error = null,
        private bool $requiresTwoFactor = false,
        private bool $isFirstLogin = false
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function requiresTwoFactor(): bool
    {
        return $this->requiresTwoFactor;
    }

    public function isFirstLogin(): bool
    {
        return $this->isFirstLogin;
    }

    public static function success(User $user, bool $requiresTwoFactor = false): self
    {
        return new self(
            success: true,
            user: $user,
            requiresTwoFactor: $requiresTwoFactor,
            isFirstLogin: $user->is_new
        );
    }

    public static function twoFactorRequired(User $user): self
    {
        return new self(
            success: true,
            user: $user,
            requiresTwoFactor: true,
            isFirstLogin: $user->is_new
        );
    }

    public static function error(string $error): self
    {
        return new self(
            success: false,
            error: $error
        );
    }
}
