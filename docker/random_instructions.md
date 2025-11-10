Enter db container

# Acceder al contenedor de la DB de test
docker exec -it first-api-db_test-1 mysql -u user -p db_test

# Una vez dentro, puedes ejecutar queries:
SHOW TABLES;
SELECT * FROM users;
DESCRIBE users;

-----

Desde fuera del container

# Ver todas las tablas
php bin/console doctrine:query:sql "SHOW TABLES" --env=test

# Ver estructura de la tabla users
php bin/console doctrine:query:sql "DESCRIBE users" --env=test

# Ver datos de usuarios
php bin/console doctrine:query:sql "SELECT * FROM users" --env=test

# Schema update (si hiciste cambios)
php bin/console doctrine:schema:update --dump-sql --env=test


------

# Ver el estado de las migrations
php bin/console doctrine:migrations:status --env=test

# Listar migrations disponibles
php bin/console doctrine:migrations:list --env=test

# Ejecutar migrations pendientes
php bin/console doctrine:migrations:migrate --env=test

# O forzar la ejecución
php bin/console doctrine:migrations:migrate --no-interaction --env=test