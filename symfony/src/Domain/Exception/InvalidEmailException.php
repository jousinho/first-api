<?php

// src/Domain/User/Exception/InvalidEmailException.php
namespace App\Domain\Exception;

class InvalidEmailException extends \DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(
            sprintf('El email "%s" no tiene un formato válido', $email),
            400
        );
    }
}