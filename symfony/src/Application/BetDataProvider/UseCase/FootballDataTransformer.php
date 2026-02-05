<?php
namespace App\Application\BetDataProvider\UseCase;

class FootballDataTransformer
{
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
}