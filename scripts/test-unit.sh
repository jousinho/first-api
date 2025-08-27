#!/bin/bash

# USO: 
# ./scripts/test-unit.sh       → Ejecuta TODOS los tests unitarios
### NO FUNCIONA ### ./scripts/test-unit.sh ProductTest → Ejecuta solo tests en ProductTest
# ./scripts/test-unit.sh --filter testCreate → Ejecuta métodos que contengan 'testCreate'

# Configuración
TESTS_DIR="tests/Unit"
DOCKER_CONTAINER="php-fpm"

# Ejecución en el contenedor
if [ $# -eq 0 ]; then
    docker-compose exec -T $DOCKER_CONTAINER ./vendor/bin/phpunit $TESTS_DIR
else
    docker-compose exec -T $DOCKER_CONTAINER ./vendor/bin/phpunit $TESTS_DIR --filter "$@"
fi