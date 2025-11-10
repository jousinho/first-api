<?php

// src/Domain/User/Exception/InvalidEmailException.php
namespace App\Application\Exception;

class InvalidArgumentException extends \DomainException
{
    public function __construct()
    {
        parent::__construct(
            'Alguno de los parámentros introducidos, no es correcto',
            400
        );
    }
}