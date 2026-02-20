<?php
// tests/Integration/Infrastructure/BetDataProvider/Http/Controllers/BetDataProviderControllerTest.php
namespace App\Tests\Integration\Infrastructure\BetDataProvider\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BetDataProviderControllerTest extends WebTestCase
{
    public function testControllerReturnsCorrectStructure(): void
    {
        $client = static::createClient();
        
        // NO mocks - usamos servicios REALES registrados
        // (El FootballDataRawClient real fallará por falta de API key, pero eso es parte del test)
        
        $client->request('GET', '/api/bet-data-provider/football-data/league/PD');
        
        // Assertions sobre la RESPUESTA HTTP (independiente del contenido)
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $response = $client->getResponse();
        $content = json_decode($response->getContent(), true);
        
        // El controller SIEMPRE debe devolver esta estructura básica
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('message', $content);
        
        // Puede ser 'success' o 'error', pero debe existir
        $this->assertContains($content['status'], ['success', 'error']);
    }
    
    public function testControllerValidatesProvider(): void
    {
        $client = static::createClient();
        
        // Provider inválido - debe fallar en validación del Command
        $client->request('GET', '/api/bet-data-provider/INVALID_PROVIDER/league/PD');
        
        // Debe devolver 400 Bad Request por validación fallida
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('error', $content['status']);
        $this->assertStringContainsString('Provider', $content['error']);
    }
    
    public function testControllerValidatesLeagueCode(): void
    {
        $client = static::createClient();
        
        // League code vacío - debe fallar
        $client->request('GET', '/api/bet-data-provider/football-data/league/');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND); // 404 por ruta no encontrada
    }
    
    /*public function testControllerAcceptsOptionalSeason(): void
    {
        $client = static::createClient();
        
        // Con season en URL
        $client->request('GET', '/api/bet-data-provider/football-data/league/PD/2024');
        
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        // No importa si falla por API key, pero la ruta debe funcionar
    }*/
    
    public function testControllerAcceptsBothHttpMethods(): void
    {
        $client = static::createClient();
        
        // GET
        $client->request('GET', '/api/bet-data-provider/football-data/league/PD');
        $this->assertNotEquals(Response::HTTP_METHOD_NOT_ALLOWED, $client->getResponse()->getStatusCode());
        
        // POST
        $client->request('POST', '/api/bet-data-provider/football-data/league/PD');
        $this->assertNotEquals(Response::HTTP_METHOD_NOT_ALLOWED, $client->getResponse()->getStatusCode());
    }
    
    /*public function testControllerErrorHandling(): void
    {
        $client = static::createClient();
        
        // Esta llamada probablemente fallará (falta API key en .env.test)
        // Pero el controller debe manejar el error y devolver estructura consistente
        $client->request('GET', '/api/bet-data-provider/football-data/league/PD');
        
        $content = json_decode($client->getResponse()->getContent(), true);
        
        // Incluso en error, debe mantener la estructura
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('message', $content);
        $this->assertSame('error', $content['status']);
    }*/
}