#!/usr/bin/env bash
#create or update db
./wait-for-it.sh $DATABASE_HOST_PORT -t 30
composer install
php bin/console doctrine:database:create --if-not-exists --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
php bin/console doctrine:fixtures:load