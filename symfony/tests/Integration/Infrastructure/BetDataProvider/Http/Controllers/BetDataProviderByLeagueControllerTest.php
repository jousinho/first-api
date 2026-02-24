<?php
namespace App\Tests\Integration\Infrastructure\BetDataProvider\Http\Controllers;

use App\Infrastructure\BetDataProvider\Http\Client\FootballDataRawClient;
use App\Application\BetDataProvider\UseCase\FootballDataTransformer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class BetDataProviderByLeagueControllerTest extends WebTestCase
{
    public function testGetLeagueStatsReturnsSuccessForValidLeague(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        
        // Mock del cliente HTTP para evitar llamadas reales
        $mockResponse = new MockResponse(
            json_encode([
                'matches' => [
                    [
                        'id' => 1,
                        'status' => 'FINISHED',
                        'utcDate' => '2024-02-10T20:00:00Z',
                        'matchday' => 24,
                        'homeTeam' => ['id' => 81, 'name' => 'FC Barcelona'],
                        'awayTeam' => ['id' => 86, 'name' => 'Real Madrid'],
                        'score' => ['fullTime' => ['home' => 2, 'away' => 1]],
                        'competition' => ['code' => 'PD', 'name' => 'Primera Division']
                    ]
                ]
            ]),
            ['http_code' => 200]
        );
        
        $mockHttpClient = new MockHttpClient($mockResponse);
        
        // Reemplazar el cliente real por el mock
        $realClient = $container->get(FootballDataRawClient::class);
        $reflection = new \ReflectionClass($realClient);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($realClient, $mockHttpClient);
        
        // Hacer la petición
        $client->request('GET', '/api/league/PD/stats');
        
        // Verificar respuesta HTTP
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $response = json_decode($client->getResponse()->getContent(), true);
        
        // Verificar estructura de respuesta
        $this->assertSame('success', $response['status']);
        $this->assertArrayHasKey('league', $response);
        $this->assertArrayHasKey('data', $response);
        
        // Verificar datos de la liga
        $this->assertSame('PD', $response['league']['code']);
        $this->assertSame('La Liga', $response['league']['name']);
        
        // Verificar que hay equipos
        $this->assertArrayHasKey('teams', $response['data']);
        $this->assertIsArray($response['data']['teams']);
        $this->assertGreaterThan(0, count($response['data']['teams']));
    }
    
    public function testGetLeagueStatsReturnsErrorForInvalidLeague(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/league/INVALID/stats');
        
        $this->assertResponseStatusCodeSame(400);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertSame('error', $response['status']);
        $this->assertStringContainsString('Liga', $response['message']);
    }
    
    public function testGetLeagueStatsReturnsAllTeamsFromConfiguration(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        
        // Mock para devolver datos para múltiples equipos
        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode(['matches' => []]), ['http_code' => 200]),
            new MockResponse(json_encode(['matches' => []]), ['http_code' => 200]),
            new MockResponse(json_encode(['matches' => []]), ['http_code' => 200]),
        ]);
        
        $realClient = $container->get(FootballDataRawClient::class);
        $reflection = new \ReflectionClass($realClient);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($realClient, $mockHttpClient);
        
        $client->request('GET', '/api/league/PD/stats');
        
        $response = json_decode($client->getResponse()->getContent(), true);
      
        // La Liga tiene 6 equipos configurados
        $this->assertCount(6, $response['data']['teams']);
        $this->assertSame(6, $response['data']['total_teams']);
    }
    
    public function testGetLeagueStatsHandlesApiErrorsGracefully(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        
        // Mock que devuelve error para algunos equipos
        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode(['matches' => []]), ['http_code' => 200]), // Barça OK
            new MockResponse('', ['http_code' => 500]), // Real Madrid error
            new MockResponse(json_encode(['matches' => []]), ['http_code' => 200]), // Atleti OK
            new MockResponse(json_encode(['matches' => []]), ['http_code' => 200]), // Athletic OK
            new MockResponse(json_encode(['matches' => []]), ['http_code' => 200]), // Sevilla OK
            new MockResponse(json_encode(['matches' => []]), ['http_code' => 200]), // Villarreal OK
        ]);
        
        $realClient = $container->get(FootballDataRawClient::class);
        $reflection = new \ReflectionClass($realClient);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($realClient, $mockHttpClient);
        
        $client->request('GET', '/api/league/PD/stats');
        
        $response = json_decode($client->getResponse()->getContent(), true);
        
        // Debe tener 5 equipos exitosos y 1 error
        $this->assertCount(6, $response['data']['teams']);
        $this->assertCount(1, $response['data']['errors']);
        $this->assertSame(86, $response['data']['errors'][0]['team_id']); // Real Madrid
    }
    
    public function testGetLeagueStatsResponseStructureIsConsistent(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        
        // Mock simple
        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode(['matches' => []]), ['http_code' => 200]),
        ]);
        
        $realClient = $container->get(FootballDataRawClient::class);
        $reflection = new \ReflectionClass($realClient);
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($realClient, $mockHttpClient);
        
        $client->request('GET', '/api/league/PD/stats');
        
        $response = json_decode($client->getResponse()->getContent(), true);
        
        // Verificar estructura completa
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('league', $response);
        $this->assertArrayHasKey('data', $response);
        
        $this->assertArrayHasKey('teams', $response['data']);
        $this->assertArrayHasKey('total_teams', $response['data']);
        $this->assertArrayHasKey('errors', $response['data']);
        
        // Si hay equipos, verificar su estructura
        if (!empty($response['data']['teams'])) {
            $team = $response['data']['teams'][0];
            $this->assertArrayHasKey('team_id', $team);
            $this->assertArrayHasKey('team_name', $team);
            $this->assertArrayHasKey('form', $team);
            $this->assertArrayHasKey('stats', $team);
            $this->assertArrayHasKey('last_matches', $team);
        }
    }
}