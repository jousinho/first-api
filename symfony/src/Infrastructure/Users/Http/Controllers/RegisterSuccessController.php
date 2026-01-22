<?php

namespace App\Infrastructure\Users\Http\Controllers;    

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterSuccessController extends AbstractController  
{
    #[Route('/registration-success', name: 'registration_success')]
    public function success(): Response
    {
        return $this->render('@views/Users/Http/Resources/views/registrationSuccess.html.twig');
    }
}