<?php
namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;

trait ResetDatabaseTrait
{
    protected function resetDatabase(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();
        $schemaManager = $connection->createSchemaManager();
        
        $tables = $schemaManager->listTableNames();
        
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        
        foreach ($tables as $table) {
            if ($table !== 'doctrine_migration_versions') {
                $connection->executeStatement("TRUNCATE TABLE $table");
            }
        }
        
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }
}