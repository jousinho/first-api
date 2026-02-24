<?php
namespace App\Infrastructure\BetDataProvider\Http\Controllers;

use App\Domain\BetDataProvider\ValueObject\LeagueConfigurationValueObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/bets', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        // Obtener todas las ligas configuradas
        $leagues = LeagueConfigurationValueObject::getAllLeagues();
        
        return $this->render('@bets/index.html.twig', [
            'leagues' => $leagues
        ]);
    }
}