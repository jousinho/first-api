<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250729164630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'First Migration';
    }

    public function up(Schema $schema): void
    {
       
    }

    public function down(Schema $schema): void
    {
       
    }
}