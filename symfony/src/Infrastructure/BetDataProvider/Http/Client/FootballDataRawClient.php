<?php
namespace App\Infrastructure\BetDataProvider\Http\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Application\BetDataProvider\Command\BetDataProviderCommand;
use App\Domain\BetDataProvider\ValueObject\LeagueConfigurationValueObject;

class FootballDataRawClient
{
    private const BASE_URL = 'https://api.football-data.org/v4';
    
    public function __construct(
        private string $apiKey,
        private HttpClientInterface $httpClient,
        private CacheInterface $cache
    ) {}
    
    /*public function fetchRawStandings(BetDataProviderCommand $command): array
    {
        $leagueConfig = LeagueConfigurationValueObject::forLaLiga();
        $leagueId = (int) $leagueConfig->leagueId();

        $response = $this->httpClient->request('GET', 
            self::BASE_URL . "/competitions/{$leagueId}/standings",
            [
                'headers' => ['X-Auth-Token' => $this->apiKey],
            ]
        );
        
        return $response->toArray(); // Datos CRUDOS de la API 
    }*/

    public function fetchRawTeamMatches(int $teamId, int $limit = 15): array
    {
        $cacheKey = "fd_team_matches_{$teamId}_{$limit}";
        
        return $this->cache->get($cacheKey, function() use ($teamId, $limit) {
            $response = $this->httpClient->request(
                'GET',
                self::BASE_URL . "/teams/{$teamId}/matches",
                [
                    'headers' => ['X-Auth-Token' => $this->apiKey],
                    'query' => [
                        'limit' => $limit,
                        'status' => 'FINISHED',
                        'dateFrom' => (new \DateTime('-6 months'))->format('Y-m-d'),
                        'dateTo' => (new \DateTime())->format('Y-m-d')
                    ]
                ]
            );
            
            return $response->toArray();
        });
    }
    
    public function fetchRawTeamMatchesFiltered(
        int $teamId, 
        ?string $venue = null, // 'HOME' o 'AWAY'
        int $limit = 15
    ): array {
        $cacheKey = "fd_team_matches_{$teamId}_{$venue}_{$limit}";
        
        return $this->cache->get($cacheKey, function() use ($teamId, $venue, $limit) {
            $query = [
                'limit' => $limit,
                'status' => 'FINISHED',
                'dateFrom' => (new \DateTime('-6 months'))->format('Y-m-d'),
                'dateTo' => (new \DateTime())->format('Y-m-d')
            ];
            
            if ($venue) {
                $query['venue'] = $venue;
            }
            
            $response = $this->httpClient->request(
                'GET',
                self::BASE_URL . "/teams/{$teamId}/matches",
                [
                    'headers' => ['X-Auth-Token' => $this->apiKey],
                    'query' => $query
                ]
            );
            
            return $response->toArray();
        });
    }

    public function fetchRawTeamLeagueMatches(int $teamId, string $leagueCode, int $limit = 15): array
    {
        $cacheKey = "fd_team_league_matches_{$teamId}_{$leagueCode}_{$limit}";
        
        return $this->cache->get($cacheKey, function() use ($teamId, $leagueCode, $limit) {
            $response = $this->httpClient->request(
                'GET',
                self::BASE_URL . "/competitions/{$leagueCode}/matches",
                [
                    'headers' => ['X-Auth-Token' => $this->apiKey],
                    'query' => [
                        'limit' => $limit,
                        'status' => 'FINISHED',
                        'dateFrom' => (new \DateTime('-1 year'))->format('Y-m-d'),
                        'dateTo' => (new \DateTime())->format('Y-m-d'),
                    ]
                ]
            );
            
            $data = $response->toArray();
            
            // Filtrar solo partidos del equipo
            $data['matches'] = array_filter($data['matches'], function($match) use ($teamId) {
                return $match['homeTeam']['id'] == $teamId || $match['awayTeam']['id'] == $teamId;
            });
            
            return $data;
        });
    }
}