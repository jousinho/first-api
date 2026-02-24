<?php

namespace App\Tests\Unit\Application\BetDataProvider\UseCase;

use App\Application\BetDataProvider\UseCase\FootballDataTransformer;
use PHPUnit\Framework\TestCase;

class FootballDataTransformerTest extends TestCase
{
    private FootballDataTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new FootballDataTransformer();
    }

    /** @test */
    public function it_transforms_standings_structure_correctly(): void
    {
        // Given
        $rawData = $this->getSampleRawData();

        // When
        $result = $this->transformer->transformStandings($rawData);

        // Then
        $this->assertArrayHasKey('league', $result);
        $this->assertArrayHasKey('season', $result);
        $this->assertArrayHasKey('standings', $result);
        $this->assertArrayHasKey('fetched_at', $result);
        
        $this->assertEquals('Premier League', $result['league']);
        $this->assertEquals('2023-08-11 to 2024-05-19', $result['season']);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $result['fetched_at']);
    }

    /** @test */
    public function it_transforms_single_team_correctly(): void
    {
        // Given
        $rawData = $this->getSampleRawData();   

        // When
        $result = $this->transformer->transformStandings($rawData);

        // Then
        $firstTeam = $result['standings'][0];
        
        $this->assertEquals(1, $firstTeam['position']);
        $this->assertEquals('Manchester City', $firstTeam['team_name']);
        $this->assertEquals(65, $firstTeam['team_id']);
        $this->assertEquals(91, $firstTeam['points']);
        $this->assertEquals(38, $firstTeam['played']);
        $this->assertEquals(94, $firstTeam['goals_for']);
        $this->assertEquals(33, $firstTeam['goals_against']);
    }

    /** @test */
    public function it_handles_multiple_standing_groups(): void
    {
        // Given
        $rawData = $this->getSampleRawDataWithMultipleGroups();

        // When
        $result = $this->transformer->transformStandings($rawData);

        // Then
        $this->assertCount(4, $result['standings']);
        
        $teamFromGroupA = $result['standings'][0];
        $teamFromGroupB = $result['standings'][2];
        
        $this->assertEquals('Team A1', $teamFromGroupA['team_name']);
        $this->assertEquals('Team B1', $teamFromGroupB['team_name']);
    }

    /** @test */
    public function it_handles_empty_standings(): void
    {
        // Given
        $rawData = [
            'competition' => ['name' => 'Empty League'],
            'season' => [
                'startDate' => '2023-08-01',
                'endDate' => '2024-05-01'
            ],
            'standings' => []
        ];

        // When
        $result = $this->transformer->transformStandings($rawData);

        // Then
        $this->assertEquals([], $result['standings']);
        $this->assertEquals('Empty League', $result['league']);
    }

    /** @test */
    public function it_handles_empty_table_within_standing_group(): void
    {
        // Given
        $rawData = [
            'competition' => ['name' => 'Test League'],
            'season' => [
                'startDate' => '2023-08-01',
                'endDate' => '2024-05-01'
            ],
            'standings' => [
                ['table' => []],
                ['table' => [['position' => 1, 'team' => ['name' => 'Solo Team', 'id' => 1], 'points' => 10, 'playedGames' => 5, 'goalsFor' => 5, 'goalsAgainst' => 2]]]
            ]
        ];

        // When
        $result = $this->transformer->transformStandings($rawData);

        // Then
        $this->assertCount(1, $result['standings']);
        $this->assertEquals('Solo Team', $result['standings'][0]['team_name']);
    }

    // Helper methods para datos de prueba

    private function getSampleRawData(): array
    {
        return [
            'competition' => ['name' => 'Premier League'],
            'season' => [
                'startDate' => '2023-08-11',
                'endDate' => '2024-05-19'
            ],
            'standings' => [
                [
                    'table' => [
                        [
                            'position' => 1,
                            'team' => ['name' => 'Manchester City', 'id' => 65],
                            'points' => 91,
                            'playedGames' => 38,
                            'goalsFor' => 94,
                            'goalsAgainst' => 33
                        ],
                        [
                            'position' => 2,
                            'team' => ['name' => 'Arsenal', 'id' => 57],
                            'points' => 89,
                            'playedGames' => 38,
                            'goalsFor' => 88,
                            'goalsAgainst' => 43
                        ]
                    ]
                ]
            ]
        ];
    }

    private function getSampleRawDataWithMultipleGroups(): array
    {
        return [
            'competition' => ['name' => 'Champions League'],
            'season' => [
                'startDate' => '2023-09-01',
                'endDate' => '2024-06-01'
            ],
            'standings' => [
                [
                    'table' => [
                        ['position' => 1, 'team' => ['name' => 'Team A1', 'id' => 1], 'points' => 9, 'playedGames' => 3, 'goalsFor' => 10, 'goalsAgainst' => 2],
                        ['position' => 2, 'team' => ['name' => 'Team A2', 'id' => 2], 'points' => 6, 'playedGames' => 3, 'goalsFor' => 5, 'goalsAgainst' => 4]
                    ]
                ],
                [
                    'table' => [
                        ['position' => 1, 'team' => ['name' => 'Team B1', 'id' => 3], 'points' => 7, 'playedGames' => 3, 'goalsFor' => 8, 'goalsAgainst' => 3],
                        ['position' => 2, 'team' => ['name' => 'Team B2', 'id' => 4], 'points' => 4, 'playedGames' => 3, 'goalsFor' => 4, 'goalsAgainst' => 6]
                    ]
                ]
            ]
        ];
    }

    public function testTransformTeamLeagueMatchesWithValidData(): void
    {
        $teamId = 81; // Barça
        $leagueCode = 'PD';
        
        $rawData = $this->getMockLeagueMatchesData();
        
        $result = $this->transformer->transformTeamLeagueMatches($rawData, $teamId, $leagueCode);
        
        // Verificar estructura básica
        $this->assertArrayHasKey('team', $result);
        $this->assertArrayHasKey('form', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('last_matches', $result);
        
        // Verificar datos del equipo
        $this->assertSame($teamId, $result['team']['id']);
        $this->assertSame('FC Barcelona', $result['team']['name']);
        $this->assertSame('PD', $result['team']['league']);
        $this->assertSame('La Liga', $result['team']['league_name']);
        
        // Verificar que solo hay partidos de liga
        foreach ($result['last_matches'] as $match) {
            $this->assertArrayNotHasKey('competition', $match); // No debe tener competición externa
        }
    }
    
    public function testTransformTeamLeagueMatchesFiltersNonLeagueMatches(): void
    {
        $teamId = 81;
        $leagueCode = 'PD';
        
        $rawData = $this->getMockMixedMatchesData(); // Incluye Champions y Copa
        
        $result = $this->transformer->transformTeamLeagueMatches($rawData, $teamId, $leagueCode);

        // Deberían ser solo 2 partidos de liga (los otros filtrados)
        $this->assertCount(2, $result['last_matches']);
        
        // Verificar que los partidos son de liga (fechas específicas)
        $dates = array_map(fn($m) => $m['date']->format('Y-m-d'), $result['last_matches']);
        $this->assertContains('2024-02-10', $dates); // Partido de liga
        $this->assertContains('2024-02-03', $dates); // Partido de liga
        $this->assertNotContains('2024-02-14', $dates); // Champions
        $this->assertNotContains('2024-02-07', $dates); // Copa
    }
    
    public function testTransformTeamLeagueMatchesCalculatesFormCorrectly(): void
    {
        $teamId = 81;
        $leagueCode = 'PD';
        
        $rawData = $this->getMockLeagueMatchesData();
        
        $result = $this->transformer->transformTeamLeagueMatches($rawData, $teamId, $leagueCode);
        
        // Verificar formato de forma (W=Win, L=Loss, D=Draw)
        $this->assertMatchesRegularExpression('/^[WLD]{1,5}$/', $result['form']['last_5_overall']);
        $this->assertMatchesRegularExpression('/^[WLD]{1,5}$/', $result['form']['last_5_home']);
        $this->assertMatchesRegularExpression('/^[WLD]{1,5}$/', $result['form']['last_5_away']);
    }
    
    public function testTransformTeamLeagueMatchesCalculatesStatsCorrectly(): void
    {
        $teamId = 81;
        $leagueCode = 'PD';
        
        $rawData = $this->getMockLeagueMatchesData();
        
        $result = $this->transformer->transformTeamLeagueMatches($rawData, $teamId, $leagueCode);
        
        // Verificar estadísticas globales
        $stats = $result['stats']['overall'];
        $this->assertArrayHasKey('avg_goals_for', $stats);
        $this->assertArrayHasKey('avg_goals_against', $stats);
        $this->assertArrayHasKey('wins', $stats);
        $this->assertArrayHasKey('draws', $stats);
        $this->assertArrayHasKey('losses', $stats);
        $this->assertArrayHasKey('matches_played', $stats);
        
        // Las medias deben ser números float
        $this->assertIsFloat($stats['avg_goals_for']);
        $this->assertIsFloat($stats['avg_goals_against']);
        
        // Los totales deben ser enteros
        $this->assertIsInt($stats['wins']);
        $this->assertIsInt($stats['matches_played']);
    }
    
    public function testTransformTeamLeagueMatchesWithEmptyData(): void
    {
        $teamId = 81;
        $leagueCode = 'PD';
        
        $rawData = [
            'matches' => []
        ];
        
        $result = $this->transformer->transformTeamLeagueMatches($rawData, $teamId, $leagueCode);
        
        // Debe devolver estructura vacía pero válida
        $this->assertEmpty($result['last_matches']);
        $this->assertEmpty($result['form']['last_5_overall']);
        $this->assertSame(0, $result['stats']['overall']['matches_played']);
    }
    
    private function getMockLeagueMatchesData(): array
    {
        return [
            'matches' => [
                [
                    'id' => 1,
                    'status' => 'FINISHED',
                    'utcDate' => '2024-02-10T20:00:00Z',
                    'matchday' => 24,
                    'homeTeam' => ['id' => 81, 'name' => 'FC Barcelona'],
                    'awayTeam' => ['id' => 86, 'name' => 'Real Madrid'],
                    'score' => [
                        'fullTime' => ['home' => 2, 'away' => 1]
                    ],
                    'competition' => ['name' => 'La Liga']
                ],
                [
                    'id' => 2,
                    'status' => 'FINISHED',
                    'utcDate' => '2024-02-03T18:30:00Z',
                    'matchday' => 23,
                    'homeTeam' => ['id' => 77, 'name' => 'Athletic Bilbao'],
                    'awayTeam' => ['id' => 81, 'name' => 'FC Barcelona'],
                    'score' => [
                        'fullTime' => ['home' => 0, 'away' => 3]
                    ],
                    'competition' => ['name' => 'La Liga']
                ],
                [
                    'id' => 3,
                    'status' => 'FINISHED',
                    'utcDate' => '2024-01-27T16:15:00Z',
                    'matchday' => 22,
                    'homeTeam' => ['id' => 81, 'name' => 'FC Barcelona'],
                    'awayTeam' => ['id' => 83, 'name' => 'Sevilla'],
                    'score' => [
                        'fullTime' => ['home' => 1, 'away' => 1]
                    ],
                    'competition' => ['name' => 'La Liga']
                ],
            ]
        ];
    }
    
    private function getMockMixedMatchesData(): array
    {
        return [
            'matches' => [
                // Partido de liga
                [
                    'id' => 1,
                    'status' => 'FINISHED',
                    'utcDate' => '2024-02-10T20:00:00Z',
                    'matchday' => 24,
                    'homeTeam' => ['id' => 81, 'name' => 'FC Barcelona'],
                    'awayTeam' => ['id' => 86, 'name' => 'Real Madrid'],
                    'score' => ['fullTime' => ['home' => 2, 'away' => 1]],
                    'competition' => ['name' => 'La Liga']
                ],
                // Partido de Champions (NO debe incluirse)
                [
                    'id' => 2,
                    'status' => 'FINISHED',
                    'utcDate' => '2024-02-14T20:00:00Z',
                    'matchday' => null,
                    'homeTeam' => ['id' => 81, 'name' => 'FC Barcelona'],
                    'awayTeam' => ['id' => 5, 'name' => 'Borussia Dortmund'],
                    'score' => ['fullTime' => ['home' => 2, 'away' => 0]],
                    'competition' => ['name' => 'UEFA Champions League']
                ],
                // Partido de liga
                [
                    'id' => 3,
                    'status' => 'FINISHED',
                    'utcDate' => '2024-02-03T18:30:00Z',
                    'matchday' => 23,
                    'homeTeam' => ['id' => 77, 'name' => 'Athletic Bilbao'],
                    'awayTeam' => ['id' => 81, 'name' => 'FC Barcelona'],
                    'score' => ['fullTime' => ['home' => 0, 'away' => 3]],
                    'competition' => ['name' => 'La Liga']
                ],
                // Partido de Copa del Rey (NO debe incluirse)
                [
                    'id' => 4,
                    'status' => 'FINISHED',
                    'utcDate' => '2024-02-07T21:30:00Z',
                    'matchday' => null,
                    'homeTeam' => ['id' => 78, 'name' => 'Atlético Madrid'],
                    'awayTeam' => ['id' => 81, 'name' => 'FC Barcelona'],
                    'score' => ['fullTime' => ['home' => 1, 'away' => 2]],
                    'competition' => ['name' => 'Copa del Rey']
                ],
            ]
        ];
    }

    public function testTransformTeamLeagueMatchesReturnsCorrectStructure(): void
    {
        $teamId = 81; // Barça
        $leagueCode = 'PD';
        $rawData = $this->getMockBarcelonaMatchesData();
        
        $result = $this->transformer->transformTeamLeagueMatches($rawData, $teamId, $leagueCode);
        
        // Estructura básica
        $this->assertArrayHasKey('team', $result);
        $this->assertArrayHasKey('form', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('last_matches', $result);
        
        // Team
        $this->assertSame(81, $result['team']['id']);
        $this->assertSame('FC Barcelona', $result['team']['name']);
        $this->assertSame('PD', $result['team']['league']);
        $this->assertSame('La Liga', $result['team']['league_name']);
        
        // Form debe tener 3 claves
        $this->assertArrayHasKey('last_5_home', $result['form']);
        $this->assertArrayHasKey('last_5_away', $result['form']);
        $this->assertArrayHasKey('last_5_overall', $result['form']);
    }

    private function getMockBarcelonaMatchesData(): array
    {
        return [
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
                ],
                [
                    'id' => 2,
                    'status' => 'FINISHED',
                    'utcDate' => '2024-02-03T18:30:00Z',
                    'matchday' => 23,
                    'homeTeam' => ['id' => 77, 'name' => 'Athletic Bilbao'],
                    'awayTeam' => ['id' => 81, 'name' => 'FC Barcelona'],
                    'score' => ['fullTime' => ['home' => 0, 'away' => 3]],
                    'competition' => ['code' => 'PD', 'name' => 'Primera Division']
                ],
                [
                    'id' => 3,
                    'status' => 'FINISHED',
                    'utcDate' => '2024-01-27T16:15:00Z',
                    'matchday' => 22,
                    'homeTeam' => ['id' => 81, 'name' => 'FC Barcelona'],
                    'awayTeam' => ['id' => 83, 'name' => 'Sevilla'],
                    'score' => ['fullTime' => ['home' => 1, 'away' => 1]],
                    'competition' => ['code' => 'PD', 'name' => 'Primera Division']
                ],
            ]
        ];
    }

    
}