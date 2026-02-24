<?php
// src/Model/BetDataProvider/ValueObject/LeagueConfiguration.php
namespace App\Domain\BetDataProvider\ValueObject;

class LeagueConfigurationValueObject
{
    private const LEAGUES = [
        'PD' => [
            'name' => 'La Liga',
            'teams' => [
                81 => 'FC Barcelona',
                86 => 'Real Madrid',
                78 => 'Atlético Madrid',
                77 => 'Athletic Bilbao',
                83 => 'Sevilla',
                94 => 'Villarreal',
            ]
        ],
        'PL' => [
            'name' => 'Premier League',
            'teams' => [
                65 => 'Manchester City',
                64 => 'Liverpool',
                57 => 'Arsenal',
                66 => 'Manchester United',
                61 => 'Chelsea',
                73 => 'Tottenham',
            ]
        ],
        'SA' => [
            'name' => 'Serie A',
            'teams' => [
                109 => 'Juventus',
                108 => 'Inter',
                98 => 'Milan',
                112 => 'Napoli',
                110 => 'Roma',
            ]
        ],
        'BL1' => [
            'name' => 'Bundesliga',
            'teams' => [
                4 => 'Bayern Munich',
                5 => 'Borussia Dortmund',
                18 => 'Bayer Leverkusen',
            ]
        ],
        'FL1' => [
            'name' => 'Ligue 1',
            'teams' => [
                524 => 'PSG',
                525 => 'Olympique Marseille',
                533 => 'Lyon',
            ]
        ],
        'PPL' => [
            'name' => 'Liga Portugal',
            'teams' => [
                498 => 'Sporting CP',
                496 => 'Benfica',
                497 => 'Porto',
            ]
        ],
    ];
    
    public static function getLeagueCodeByTeam(int $teamId): ?string
    {
        foreach (self::LEAGUES as $leagueCode => $config) {
            if (array_key_exists($teamId, $config['teams'])) {
                return $leagueCode;
            }
        }
        return null;
    }
    
    public static function getLeagueName(string $leagueCode): ?string
    {
        return self::LEAGUES[$leagueCode]['name'] ?? null;
    }
    
    public static function getTeamName(int $teamId): ?string
    {
        foreach (self::LEAGUES as $config) {
            if (isset($config['teams'][$teamId])) {
                return $config['teams'][$teamId];
            }
        }
        return null;
    }
    
    public static function getAllTeams(): array
    {
        $teams = [];
        foreach (self::LEAGUES as $leagueCode => $config) {
            foreach ($config['teams'] as $teamId => $teamName) {
                $teams[$teamId] = [
                    'name' => $teamName,
                    'league' => $leagueCode,
                    'league_name' => $config['name'],
                ];
            }
        }
        return $teams;
    }

    public static function getAllLeagueNames(): array
    {
        $leagueNames = [];

        foreach (self::LEAGUES as $leagueCode => $config) {
            $leagueNames[] = $config['name'];
        }

        return $leagueNames;
    }

    public static function getTeamsByLeague(string $leagueCode): array
    {
        return self::LEAGUES[$leagueCode]['teams'] ?? [];
    }

    public static function getAllLeagues(): array
    {
        $leagues = [];
        foreach (self::LEAGUES as $code => $data) {
            $leagues[$code] = [
                'code' => $code,
                'name' => $data['name'],
                'flag' => self::getFlagEmoji($code),
                'teams_count' => count($data['teams']),
                'teams' => $data['teams']
            ];
        }
        return $leagues;
    }

    public static function getFlagEmoji(string $leagueCode): string
    {
        return match($leagueCode) {
            'PD' => '🇪🇸',
            'PL' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
            'SA' => '🇮🇹',
            'BL1' => '🇩🇪',
            'FL1' => '🇫🇷',
            'PPL' => '🇵🇹',
            default => '🏆'
        };
    }
}