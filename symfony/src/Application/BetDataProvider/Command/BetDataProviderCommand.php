<?php

namespace App\Application\BetDataProvider\Command;             // Este comando es el que se encarga de procesar los datos de la petición

class BetDataProviderCommand
{
    private function __construct(
        public readonly string $provider,
        public readonly string $leagueCode,
        public readonly int $season
    ) {
        $this->validate();
    }

    public static function create(
        string $provider, 
        string $leagueCode, 
        ?int $season = null
    ): self
    {
        return new self(
            provider: $provider,
            leagueCode: $leagueCode,
            season: $season ?? date('Y')
        );
    }

    private function validate(): void
    {
        $this->validateProvider();
        $this->validateLeagueCode();
        $this->validateSeason();
    }

    private function validateProvider(): void
    {
        if (empty(trim($this->provider))) {
            throw new \InvalidArgumentException('Provider cannot be empty');
        }
    }

    private function validateSeason(): void 
    {
        if ($this->season < 2000) {
            throw new \InvalidArgumentException('Season must be at least 2000');
        }
    }

    public function provider(): string 
    {
        return trim($this->provider);
    }

    public function leagueCode(): string 
    {
        return strtolower(trim($this->leagueCode));
    }

    public function season(): int 
    {
        return $this->season;
    }
}