<?php
// src/Domain/BetDataProvider/ValueObject/BetDataProviderValueObject.php
namespace App\Domain\BetDataProvider\ValueObject;

class BetDataProviderValueObject
{
    public const FOOTBALL_DATA = 'football-data';
    
    private string $value;
    
    public function __construct(string $provider)
    {
        if ($provider !== self::FOOTBALL_DATA) {
            throw new \InvalidArgumentException('Solo football-data soportado por ahora');
        }
        $this->value = $provider;
    }
    
    public function value(): string
    {
        return $this->value;
    }
}