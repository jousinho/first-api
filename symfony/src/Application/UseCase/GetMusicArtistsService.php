<?php
namespace App\Application\UseCase;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetMusicArtistsService
{
    private $httpClient;
    
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    public function getArtistInfo(string $artistName): array
    {
        $response = $this->httpClient->request(
            'GET',
            'https://theaudiodb.com/api/v1/json/2/search.php?s=' . urlencode($artistName)
        );
        
        $data = $response->toArray();
        
        return $data['artists'][0] ?? [];
    }
    
    public function getPopularRockArtists(): array
    {
        $rockArtists = ['AC/DC', 'Metallica', 'Led Zeppelin', 'Queen', 'The Beatles'];
        $artists = [];
        
        foreach ($rockArtists as $artist) {
            $artists[] = $this->getArtistInfo($artist);
        }
        
        return array_filter($artists);
    }
}