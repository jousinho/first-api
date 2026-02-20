<?php
namespace App\Infrastructure\BetDataProvider\Http\Controllers;  

use App\Application\BetDataProvider\Command\BetDataProviderCommand; 
use App\Infrastructure\BetDataProvider\Http\Client\FootballDataRawClient;
use App\Application\BetDataProvider\UseCase\FootballDataTransformer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;  

class BetDataProviderController extends AbstractController    // Este controlador es el que se encarga de procesar las peticiones de los usuarios
{
    #[Route('/api/bet-data-provider/{provider}/league/{leagueCode}', 
            name: 'bet_data_provider', 
            methods: ['GET', 'POST'])]
    public function betDataProvider(
        Request $request,
        FootballDataRawClient $client,   
        FootballDataTransformer $transformer,
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

            // STANDINGS
            //$rawData = $client->fetchRawStandings($command);
            //$transformedData = $transformer->transformStandings($rawData);
            
            // MATCHES by TEAM
            // Para Barça (ID 81)   
            //$matches = $client->fetchRawTeamMatchesFiltered(81, null, 15); // Últimos 15 partidos
            //$homeMatches = $client->fetchRawTeamMatchesFiltered(81, 'HOME', 7); // Últimos 5 en casa
            //$awayMatches = $client->fetchRawTeamMatchesFiltered(81, 'AWAY', 7); // Últimos 5 fuera

            // Transformar
            // Para Barça (solo partidos de La Liga)
            $rawData = $client->fetchRawTeamLeagueMatches(81, 'PD', 20);
            $transformed = $transformer->transformTeamLeagueMatches($rawData, 81, 'PD');
            
            // Para Real Madrid
            $rawData = $client->fetchRawTeamLeagueMatches(86, 'PD', 20);
            $transformed = $transformer->transformTeamLeagueMatches($rawData, 86, 'PD');

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Datos en proceso de obtención',
                'data' => [
                    'provider' => $command->provider(),
                    'league' => $command->leagueCode(),
                    'season' => $command->season()
                ]
            ]);
            
        } catch (\InvalidArgumentException $e) {
            //dump($e->getMessage()); die;
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            //dump($e->getMessage()); die;
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}