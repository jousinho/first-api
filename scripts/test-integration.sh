#!/bin/bash

# USO:
# ./scripts/test-integration.sh           → Todos los tests
# ./scripts/test-integration.sh Product   → Solo tests de Product
# ./scripts/test-integration.sh --filter testSave → Solo métodos con 'testSave'

# Configuración
DOCKER_PHP_SERVICE="php-fpm"
TESTS_DIR="tests/Integration"

# Ejecutar tests (pasando argumentos al comando PHPUnit)
if [ $# -eq 0 ]; then
  docker-compose exec -T "$DOCKER_PHP_SERVICE" ./vendor/bin/phpunit "$TESTS_DIR"
else
  docker-compose exec -T "$DOCKER_PHP_SERVICE" ./vendor/bin/phpunit "$TESTS_DIR" "$@"
fi

# Opcional: Mostrar logs de DB solo si fallan
if [ $? -ne 0 ]; then
    echo "🔍 Mostrando logs de DB por fallo:"
    docker-compose logs db_test | tail -n 20
fi