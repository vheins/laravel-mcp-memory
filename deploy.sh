php artisan opcache:clear
php artisan down

git pull

# update PHP dependencies
composer install --no-interaction --no-dev --prefer-dist --apcu-autoloader --optimize-autoloader --classmap-authoritative

# run migration
php artisan migrate --force

# build assets
yarn install
yarn build

# clear cache
php artisan modelCache:clear
php artisan optimize:clear

php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

php artisan storage:link
php artisan queue:restart

# disable maintenance mode
php artisan up

php artisan opcache:compile --force
