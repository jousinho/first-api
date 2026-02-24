<?php
namespace App\Tests\Integration\Infrastructure\BetDataProvider\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomePageReturnsSuccessfulResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/bets');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Estadísticas de Ligas Europeas');
    }
    
    public function testHomePageDisplaysAllLeagues(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/bets');
        
        // Verificar que hay tarjetas de ligas (6 ligas configuradas)
        $this->assertCount(6, $crawler->filter('.league-card'));
        
        // Verificar que aparecen los nombres de las ligas
        $this->assertSelectorExists('a[href="/api/league/PD/stats"]');
        $this->assertSelectorExists('a[href="/api/league/PL/stats"]');
        $this->assertSelectorExists('a[href="/api/league/SA/stats"]');
    }
    
    public function testHomePageShowsTeamCounts(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/bets');
        
        // La Liga debe tener 6 equipos
        $laLigaCard = $crawler->filter('a[href="/api/league/PD/stats"]')->first();
        $this->assertStringContainsString('6 equipos', $laLigaCard->text());
    }
}