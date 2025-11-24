<?php

// src/Domain/User/Exception/InvalidEmailException.php
namespace App\Domain\Exception;

class UserAlreadyExistsException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct(
            sprintf($message),
            400
        );
    }
}