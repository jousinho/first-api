<?php
namespace App\Infrastructure\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OtroTestController extends AbstractController
{
    #[Route('/test1', name: 'test1')]
    public function test1(): JsonResponse
    {
         return new JsonResponse(['status' => 'ok']);
    }
}