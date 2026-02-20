<?php
namespace App\Application\BetDataProvider\UseCase;

use App\Domain\BetDataProvider\ValueObject\LeagueConfigurationValueObject;

class FootballDataTransformer
{
    const limit = 15;

    private const FORM_MAP = [
        'WIN' => 'W',
        'LOSS' => 'L',
        'DRAW' => 'D',
        'PENDING' => '-'
    ];

    public function transformStandings(array $rawData): array
    {
        $standings = [];
        
        foreach ($rawData['standings'] as $standingGroup) {
            foreach ($standingGroup['table'] as $team) {
                $standings[] = [
                    'position' => $team['position'],
                    'team_name' => $team['team']['name'],
                    'team_id' => $team['team']['id'],
                    'points' => $team['points'],
                    'played' => $team['playedGames'],
                    'goals_for' => $team['goalsFor'],
                    'goals_against' => $team['goalsAgainst'],
                ];
            }
        }
        
        return [
            'league' => $rawData['competition']['name'],
            'season' => $rawData['season']['startDate'] . ' to ' . $rawData['season']['endDate'],
            'standings' => $standings,
            'fetched_at' => date('Y-m-d H:i:s')
        ];
    }

    public function transformTeamMatches(array $rawData, int $teamId, int $limit = 15): array
    {
        // Identificar el equipo
        $teamName = $this->extractTeamName($rawData['matches'], $teamId);
        $matches = $this->extractMatches($rawData['matches'], $teamId, $limit);
        
        // Separar por local/visitante
        $homeMatches = array_filter($matches, fn($m) => $m['venue'] === 'HOME');
        $awayMatches = array_filter($matches, fn($m) => $m['venue'] === 'AWAY');
        
        // Tomar últimos 5 de cada
        $last5Home = array_slice(array_values($homeMatches), -5, 5);
        $last5Away = array_slice(array_values($awayMatches), -5, 5);
        
        return [
            'team' => [
                'id' => $teamId,
                'name' => $teamName,
            ],
            'form' => [
                'last_5_home' => $this->formatForm($last5Home),
                'last_5_away' => $this->formatForm($last5Away),
                'last_5_overall' => $this->formatForm(array_slice($matches, -5, 5)),
            ],
            'stats' => [
                'home' => $this->calculateVenueStats($last5Home),
                'away' => $this->calculateVenueStats($last5Away),
                'overall' => $this->calculateVenueStats(array_slice($matches, -5, 5)),
            ],
            'last_matches' => array_slice($matches, -15, 15), // últimos 15 para referencia
        ];
    }
    
    private function extractTeamName(array $matches, int $teamId): string
    {
        foreach ($matches as $match) {
            if ($match['homeTeam']['id'] === $teamId) {
                return $match['homeTeam']['name'];
            }
            if ($match['awayTeam']['id'] === $teamId) {
                return $match['awayTeam']['name'];
            }
        }
        return 'Unknown';
    }
    
    private function extractMatches(array $matches, int $teamId, int $limit): array
    {
        $result = [];
        
        foreach ($matches as $match) {
            if ($match['status'] !== 'FINISHED') {
                continue;
            }
            
            $isHome = ($match['homeTeam']['id'] == $teamId);
            $opponent = $isHome ? $match['awayTeam']['name'] : $match['homeTeam']['name'];
            
            $homeScore = $match['score']['fullTime']['home'] ?? 0;
            $awayScore = $match['score']['fullTime']['away'] ?? 0;
            
            $goalsFor = $isHome ? $homeScore : $awayScore;
            $goalsAgainst = $isHome ? $awayScore : $homeScore;
            
            $result[] = [
                'date' => new \DateTimeImmutable($match['utcDate']),
                'venue' => $isHome ? 'HOME' : 'AWAY',
                'opponent' => $opponent,
                'competition' => $match['competition']['name'],
                'matchday' => $match['matchday'],
                'score' => sprintf('%d-%d', $homeScore, $awayScore),
                'goals_for' => $goalsFor,
                'goals_against' => $goalsAgainst,
                'result' => $this->calculateResult($goalsFor, $goalsAgainst),
            ];
        }
        
        // Ordenar por fecha descendente (más reciente primero)
        usort($result, fn($a, $b) => $b['date'] <=> $a['date']);
        
        return array_slice($result, 0, $limit);
    }
    
    private function calculateResult(int $goalsFor, int $goalsAgainst): string
    {
        if ($goalsFor > $goalsAgainst) return 'WIN';
        if ($goalsFor < $goalsAgainst) return 'LOSS';
        return 'DRAW';
    }
    
    private function formatForm(array $matches): string
    {
        $form = '';
        foreach ($matches as $match) {
            $form .= self::FORM_MAP[$match['result']] ?? '-';
        }
        return $form;
    }
    
    private function calculateVenueStats(array $matches): array
    {
        if (empty($matches)) {
            return [
                'avg_goals_for' => 0,
                'avg_goals_against' => 0,
                'avg_goals_total' => 0,
                'total_goals_for' => 0,
                'total_goals_against' => 0,
                'matches_played' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
            ];
        }
        
        $totalGoalsFor = array_sum(array_column($matches, 'goals_for'));
        $totalGoalsAgainst = array_sum(array_column($matches, 'goals_against'));
        $count = count($matches);
        
        $wins = count(array_filter($matches, fn($m) => $m['result'] === 'WIN'));
        $draws = count(array_filter($matches, fn($m) => $m['result'] === 'DRAW'));
        $losses = count(array_filter($matches, fn($m) => $m['result'] === 'LOSS'));
        
        return [
            'avg_goals_for' => round($totalGoalsFor / $count, 2),
            'avg_goals_against' => round($totalGoalsAgainst / $count, 2),
            'avg_goals_total' => round(($totalGoalsFor + $totalGoalsAgainst) / $count, 2),
            'total_goals_for' => $totalGoalsFor,
            'total_goals_against' => $totalGoalsAgainst,
            'matches_played' => $count,
            'wins' => $wins,
            'draws' => $draws,
            'losses' => $losses,
        ];
    }

    public function transformTeamLeagueMatches(
        array $rawData, 
        int $teamId, 
        string $leagueCode,
        int $limit = 15
    ): array {
        $teamName = LeagueConfigurationValueObject::getTeamName($teamId);
        $leagueName = LeagueConfigurationValueObject::getLeagueName($leagueCode);
        
        $matches = $this->extractLeagueMatches($rawData['matches'], $teamId);
        
        // Ordenar y limitar
        usort($matches, fn($a, $b) => $b['date'] <=> $a['date']);
        $matches = array_slice($matches, 0, $limit);
        
        // Separar por local/visitante
        $homeMatches = array_filter($matches, fn($m) => $m['venue'] === 'HOME');
        $awayMatches = array_filter($matches, fn($m) => $m['venue'] === 'AWAY');
        
        // Últimos 5 de cada (o todos si hay menos)
        $last5Home = array_slice(array_values($homeMatches), -5, 5);
        $last5Away = array_slice(array_values($awayMatches), -5, 5);
        
        return [
            'team' => [
                'id' => $teamId,
                'name' => $teamName,
                'league' => $leagueCode,
                'league_name' => $leagueName,
            ],
            'form' => [
                'last_5_home' => $this->formatForm($last5Home),
                'last_5_away' => $this->formatForm($last5Away),
                'last_5_overall' => $this->formatForm(array_slice($matches, 0, 5)),
            ],
            'stats' => [
                'home' => $this->calculateVenueStats($last5Home),
                'away' => $this->calculateVenueStats($last5Away),
                'overall' => $this->calculateVenueStats(array_slice($matches, 0, 5)),
                'home_all' => $this->calculateVenueStats($homeMatches),
                'away_all' => $this->calculateVenueStats($awayMatches),
            ],
            'last_matches' => $matches,
        ];
    }
    
    private function extractLeagueMatches(array $matches, int $teamId): array
    {
        $result = [];
        
        foreach ($matches as $match) {
            if ($match['status'] !== 'FINISHED') {
                continue;
            }

            if (!in_array(
                    $match['competition']['name'], 
                    LeagueConfigurationValueObject::getAllLeagueNames()
                )) {
                
                    continue;
            }

            $isHome = ($match['homeTeam']['id'] == $teamId);
            $opponent = $isHome ? $match['awayTeam']['name'] : $match['homeTeam']['name'];
            
            $homeScore = $match['score']['fullTime']['home'] ?? 0;
            $awayScore = $match['score']['fullTime']['away'] ?? 0;
            
            $goalsFor = $isHome ? $homeScore : $awayScore;
            $goalsAgainst = $isHome ? $awayScore : $homeScore;
            
            $result[] = [
                'date' => new \DateTimeImmutable($match['utcDate']),
                'venue' => $isHome ? 'HOME' : 'AWAY',
                'opponent' => $opponent,
                'matchday' => $match['matchday'],
                'score' => sprintf('%d-%d', $homeScore, $awayScore),
                'goals_for' => $goalsFor,
                'goals_against' => $goalsAgainst,
                'result' => $this->calculateResult($goalsFor, $goalsAgainst),
            ];
        }
        
        return $result;
    }
}