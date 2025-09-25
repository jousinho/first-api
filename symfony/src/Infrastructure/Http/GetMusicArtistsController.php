<?php
// src/Infrastructure/Http/MusicController.php
namespace App\Infrastructure\Http;

use App\Application\UseCase\GetMusicArtistsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GetMusicArtistsController extends AbstractController
{
    #[Route('/music/artists', name: 'music_artists')]
    public function artists(GetMusicArtistsService $musicApiService): Response
    {
        $artists = $musicApiService->getPopularRockArtists();
        
        return $this->render('@views/music/artists.html.twig', [
            'artists' => $artists
        ]);
    }
    
    #[Route('/music/artist/{name}', name: 'music_artist')]
    public function artist(string $name, GetMusicArtistsService $musicApiService): Response
    {
        $artist = $musicApiService->getArtistInfo($name);
        
        if (empty($artist)) {
            throw $this->createNotFoundException('Artista no encontrado');
        }
        
        return $this->render('@views/music/artist.html.twig', [
            'artist' => $artist
        ]);
    }
}