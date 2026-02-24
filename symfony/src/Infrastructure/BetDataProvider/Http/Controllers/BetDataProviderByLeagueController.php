<?php
namespace App\Infrastructure\BetDataProvider\Http\Controllers;

use App\Application\BetDataProvider\Command\BetDataProviderCommand;
use App\Application\BetDataProvider\UseCase\FootballDataTransformer;
use App\Infrastructure\BetDataProvider\Http\Client\FootballDataRawClient;
use App\Domain\BetDataProvider\ValueObject\LeagueConfigurationValueObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BetDataProviderByLeagueController extends AbstractController
{
    public function __construct(
        private FootballDataRawClient $client,
        private FootballDataTransformer $transformer
    ) {}
    
    #[Route('/api/league/{leagueCode}/stats', 
            name: 'league_stats', 
            methods: ['GET'])]
    public function getLeagueStats(string $leagueCode): Response
    {
        try {
            // Validar que la liga existe
            $leagueName = LeagueConfigurationValueObject::getLeagueName($leagueCode);
            if (!$leagueName) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => "Liga '{$leagueCode}' no encontrada"
                ], 400);
            }
            
            // Obtener todos los equipos de esta liga
            $teams = LeagueConfigurationValueObject::getTeamsByLeague($leagueCode);
            
            $results = [];
            $errors = [];
            
            foreach ($teams as $teamId => $teamName) {
                try {
                    // Obtener datos del equipo
                    $rawData = $this->client->fetchRawTeamLeagueMatches($teamId, $leagueCode, 15);
                    $stats = $this->transformer->transformTeamLeagueMatches($rawData, $teamId, $leagueCode);
                    
                    $results[] = [
                        'team_id' => $teamId,
                        'team_name' => $teamName,
                        'form' => $stats['form'],
                        'stats' => $stats['stats'],
                        'last_matches' => array_slice($stats['last_matches'], 0, 5), // Últimos 5 partidos
                    ];
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'team_id' => $teamId,
                        'team_name' => $teamName,
                        'error' => $e->getMessage()
                    ];
                }
            }
           
            return $this->render('@bets/league_stats.html.twig', [
                'league' => [
                    'code' => $leagueCode,
                    'name' => $leagueName,
                    'flag' => LeagueConfigurationValueObject::getFlagEmoji($leagueCode),
                    'teams_count' => count($results)
                ],
                'teams' => $results,
                'errors' => $errors
            ]);

            // return new JsonResponse([
            //     'status' => 'success',
            //     'league' => [
            //         'code' => $leagueCode,
            //         'name' => $leagueName
            //     ],
            //     'data' => [
            //         'teams' => $results,
            //         'total_teams' => count($results),
            //         'errors' => $errors
            //     ]
            // ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}