#!/usr/bin/env bash

set -e

role=${CONTAINER_ROLE:-app}
env=${APP_ENV:-production}

until nc -z -v -w30 mysql 3306
do
    echo "Waiting for database connection..."
    sleep 5
done

php /var/www/html/artisan config:cache

if [ "$role" = "app" ]; then

    exec apache2-foreground

elif [ "$role" = "queue" ]; then

    php /var/www/html/artisan horizon

elif [ "$role" = "scheduler" ]; then

    while [ true ]
    do
      php /var/www/html/artisan schedule:run --verbose --no-interaction &
      sleep 60
    done

elif [ "$role" = "migrations" ]; then

    php /var/www/html/artisan migrate --force

else
    echo "Could not match the container role \"$role\""
    exit 1
fi
