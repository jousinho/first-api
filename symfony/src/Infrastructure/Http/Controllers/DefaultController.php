<?php
namespace App\Infrastructure\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Domain\Model\Test;
use App\Domain\UseCase\TestUseCase;

class DefaultController extends AbstractController
{
    #[Route('/status', name: 'module1_status')]
    public function status(TestUseCase $useCase): JsonResponse
    {
        // $response = Test::hello();
        $response = $useCase->execute();

        return new JsonResponse(['status' => $response]);
    }
}