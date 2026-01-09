<?php
// src/Infrastructure/Http/Controllers/RegistrationSuccessController.php

namespace App\Infrastructure\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterSuccessController extends AbstractController
{
    #[Route('/registration-success', name: 'registration_success')]
    public function success(): Response
    {
        return $this->render('registration/success.html.twig');
    }
}