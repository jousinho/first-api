TESTS_DIR="tests/Unit"
DOCKER_CONTAINER="php-fpm"

# Ejecución en el contenedor
if [ $# -eq 0 ]; then
    docker-compose exec -T $DOCKER_CONTAINER sh -c "cd /var/www/project && ./vendor/bin/phpunit $TESTS_DIR"
else
    docker-compose exec -T $DOCKER_CONTAINER sh -c "cd /var/www/project && ./vendor/bin/phpunit $TESTS_DIR --filter \"$@\""
fi

# Opcional: Mostrar logs de DB solo si fallan
if [ $? -ne 0 ]; then
    echo "🔍 Mostrando logs de DB por fallo:"
    docker-compose logs db_test | tail -n 20
fi