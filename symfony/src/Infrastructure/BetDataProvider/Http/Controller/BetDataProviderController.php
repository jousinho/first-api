<?php
namespace App\Infrastructure\BetDataProvider\Http\Controller;  

use App\Application\BetDataProvider\Command\BetDataProviderCommand; 
use App\Infrastructure\BetDataProvider\Http\Client\FootballDataRawClient;
use App\Application\BetDataProvider\UseCase\BetDataProviderTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BetDataProviderController extends AbstractController    // Este controlador es el que se encarga de procesar las peticiones de los usuarios
{
    #[Route('/api/bet-data-provider/{provider}/league/{leagueCode}', 
            '/api/bet-data-provider/{provider}/league/{leagueCode}/{season}',
            name: 'bet_data_provider', 
            methods: ['POST'])]
    public function betDataProvider(
        Request $request,
        FootballDataRawClient $client,   
        BetDataProviderTransformer $transformer,
        string $provider,  // 'football-data' o 'api-football'
        string $leagueCode, // 'PD' (España), 'PL' (Inglaterra), etc.
        ?int $season = null
    ): JsonResponse {
        try {
            $command = BetDataProviderCommand::create(    // Crear el comando con los datos de la petición
                provider: $provider,
                leagueCode: $leagueCode,
                season: $season
            );

            $rawData = $client->fetchRawStandings($command);
            $transformedData = $transformer->transformLeagueStandings($rawData);
            
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Datos en proceso de obtención',
                'data' => [
                    'provider' => $command->provider(),
                    'league' => $command->leagueCode(),
                    'season' => $command->season()
                ]
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}