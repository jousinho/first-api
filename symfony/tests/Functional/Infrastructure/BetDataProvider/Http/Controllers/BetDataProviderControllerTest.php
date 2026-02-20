<?php
// tests/Integration/Infrastructure/BetDataProvider/Http/Controllers/BetDataProviderControllerTest.php
namespace App\Tests\Functional\Infrastructure\BetDataProvider\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BetDataProviderControllerTest extends WebTestCase
{
    public function testApiEndpointExists(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/bet-data-provider/football-data/league/PD');
        
        // Al menos verifica que la ruta existe y devuelve JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        // Puede devolver error 400 (validación) o 500 (API real), pero no 404
        $this->assertNotEquals(404, $client->getResponse()->getStatusCode());
    }
    
    public function testApiAcceptsBothMethods(): void
    {
        $client = static::createClient();
        
        // Test GET
        $client->request('GET', '/api/bet-data-provider/football-data/league/PD');
        $statusGet = $client->getResponse()->getStatusCode();
        
        // Test POST  
        $client->request('POST', '/api/bet-data-provider/football-data/league/PD');
        $statusPost = $client->getResponse()->getStatusCode();
        
        // Ambos métodos deberían funcionar (pueden fallar por validación, pero no 405)
        $this->assertNotEquals(405, $statusGet);
        $this->assertNotEquals(405, $statusPost);
    }

    public function testFetchLeagueDataSuccess(): void
    {
        $client = static::createClient();
        
        // 1. Mock del FootballDataRawClient para no llamar a la API real
        $mockClient = $this->createMock(\App\Infrastructure\BetDataProvider\Http\Client\FootballDataRawClient::class);
        $mockClient->method('fetchRawStandings')
            ->willReturn($this->getMockApiResponse());
        
        // 2. Mock del Transformer
        $mockTransformer = $this->createMock(\App\Application\BetDataProvider\UseCase\FootballDataTransformer::class);
        $mockTransformer->method('transformStandings')
            ->willReturn(['transformed' => 'data']);
        
        // 3. Reemplazar servicios en el container
        $container = static::getContainer();
        $container->set(FootballDataRawClient::class, $mockClient);
        $container->set(FootballDataTransformer::class, $mockTransformer);
        
        // 4. Hacer la petición
        $client->request('GET', '/api/bet-data-provider/football-data/league/PD');
        
        // 5. Verificaciones
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $response = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $response);
        $this->assertSame('success', $response['status']);
        $this->assertArrayHasKey('data', $response);
    }
    
    /*public function testFetchLeagueDataWithSeason(): void
    {
        $client = static::createClient();
        
        // Mock services
        $container = static::getContainer();
        // ... similar al anterior
        
        $client->request('GET', '/api/bet-data-provider/football-data/league/PD/2023');
        
        $this->assertResponseIsSuccessful();
    }*/
    
    public function testInvalidProviderReturnsBadRequest(): void
    {
        $client = static::createClient();
        
        // No need to mock - should fail validation
        $client->request('GET', '/api/bet-data-provider/invalid-provider/league/PD');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $response);
        $this->assertSame('error', $response['status']);
    }
    
    public function testInvalidLeagueCode(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/bet-data-provider/football-data/league/INVALID');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
    
    private function getMockApiResponse(): array
    {
        return [
            'competition' => [
                'id' => 2014,
                'name' => 'Primera Division',
                'code' => 'PD',
            ],
            'season' => [
                'id' => 1234,
                'startDate' => '2023-08-11',
                'endDate' => '2024-05-26',
                'currentMatchday' => 25,
            ],
            'standings' => [
                [
                    'stage' => 'REGULAR_SEASON',
                    'type' => 'TOTAL',
                    'table' => [
                        [
                            'position' => 1,
                            'team' => [
                                'id' => 81,
                                'name' => 'FC Barcelona',
                                'shortName' => 'Barça',
                                'tla' => 'FCB',
                                'crest' => 'https://crests.football-data.org/81.png'
                            ],
                            'playedGames' => 25,
                            'won' => 20,
                            'draw' => 3,
                            'lost' => 2,
                            'points' => 63,
                            'goalsFor' => 50,
                            'goalsAgainst' => 20,
                            'goalDifference' => 30,
                            'form' => 'WWWWD'
                        ]
                    ]
                ]
            ]
        ];
    }
}