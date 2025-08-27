#!/bin/bash

# USO:
# ./scripts/make-migration.sh "Descripción de la migración"

# Configuración
DOCKER_PHP_SERVICE="php-fpm"
MIGRATIONS_DIR="src/Infrastructure/Persistence/Doctrine/Migrations"

# Validar argumentos
# if [ -z "$1" ]; then
#   echo "⚠️  Uso: $0 \"Descripción de la migración\""
#   exit 1
# fi

# Crear migración
docker-compose exec "$DOCKER_PHP_SERVICE" bin/console doctrine:migrations:migrate

# Ajustar permisos (necesario en Linux/Mac)
docker-compose exec "$DOCKER_PHP_SERVICE" sh -c "chown $(id -u):$(id -g) $MIGRATIONS_DIR/*"

echo "✅ Migración creada en $MIGRATIONS_DIR"