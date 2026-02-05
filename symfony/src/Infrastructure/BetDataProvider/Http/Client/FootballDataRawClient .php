<?php
namespace App\Infrastructure\BetDataProvider\Http\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Application\BetDataProvider\Command\BetDataProviderCommand;

class FootballDataRawClient
{
    private const BASE_URL = 'https://api.football-data.org/v4';
    
    public function __construct(
        private string $apiKey,
        private HttpClientInterface $httpClient,
        private CacheInterface $cache
    ) {}
    
    public function fetchRawStandings(BetDataProviderCommand $command): array
    {
        $cacheKey = "fd_raw_standings_{$command->leagueCode()}_{$command->season()}";
        
        return $this->cache->get($cacheKey, function() use ($command) {
            $response = $this->httpClient->request('GET', 
                self::BASE_URL . "/competitions/{$command->leagueCode()}/standings", 
                [
                    'headers' => ['X-Auth-Token' => $this->apiKey],
                    'query' => ['season' => $command->season()]
                ]
            );
            
            return $response->toArray(); // Datos CRUDOS de la API
        });
    }
}