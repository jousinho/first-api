#!/bin/bash

# USO:
# ./scripts/test-functional.sh           → Todos los tests funcionales
# ./scripts/test-functional.sh Product   → Tests de ProductController
# ./scripts/test-functional.sh --filter testCreate → Solo métodos con 'testCreate'

# Configuración
#DOCKER_NETWORK="your_network_name"
DB_SERVICE="db_test"
PHP_SERVICE="php-fpm"
TESTS_DIR="tests/Functional"

# Levantar servicios esenciales
# docker-compose up -d "$DB_SERVICE" "$PHP_SERVICE"

# Esperar a que MySQL esté listo (timeout de 30s)
# timeout 30s bash -c "until docker-compose exec -T $DB_SERVICE mysqladmin ping -uuser -ppassword --silent; do sleep 2; done" || {
#   echo "Error: MySQL no disponible después de 30s"
#   exit 1
# }

# Configurar base de datos de test
# docker-compose exec -T "$PHP_SERVICE" bin/console --env=test doctrine:database:drop --force || true
# docker-compose exec -T "$PHP_SERVICE" bin/console --env=test doctrine:database:create
# docker-compose exec -T "$PHP_SERVICE" bin/console --env=test doctrine:schema:create

# Ejecutar tests (pasando argumentos al comando PHPUnit)
if [ $# -eq 0 ]; then
  docker-compose exec -T "$PHP_SERVICE" ./vendor/bin/phpunit "$TESTS_DIR"
else
  docker-compose exec -T "$PHP_SERVICE" ./vendor/bin/phpunit "$TESTS_DIR" "$@"
fi

# Opcional: Limpiar después (descomenta si lo prefieres)
# docker-compose down