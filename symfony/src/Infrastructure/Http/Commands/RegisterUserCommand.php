<?php

namespace App\Infrastructure\Http\Commands;

class RegisterUserCommand
{
    private function __construct(
        public readonly string $name,
        public readonly string $email, 
        public readonly string $plainPassword
    ) {
        $this->validate();
    }

    public static function create(string $name, string $email, string $plainPassword): self
    {
        return new self($name, $email, $plainPassword);
    }

    private function validate(): void
    {
        $this->validateName();
        $this->validateEmail();
        $this->validatePassword();
    }

    private function validateName(): void
    {
        if (empty(trim($this->name))) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }
    }

    private function validateEmail(): void
    {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }

    private function validatePassword(): void
    {
        if (strlen($this->plainPassword) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters long');
        }
    }

    public function name(): string 
    {
        return trim($this->name);
    }

    public function email(): string 
    {
        return strtolower(trim($this->email));
    }

    public function plainPassword(): string 
    {
        return $this->plainPassword;
    }
}