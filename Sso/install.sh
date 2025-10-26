# 1. Download PHP dependencies
composer install

# 2. Download JS/HTML dependencies
yarn install

# !. use extra switch if your system doesn't support symlinks (Windows; can be enabled)
yarn install --no-bin-links

# 3. Generate assets
yarn run dev // or any other alternative defined within package.json

# 4. Run migrations
php artisan migrate

# 5. Generate app key and JWT secret
php artisan key:generate
php artisan jwt:secret

# 6. Run seeders (optional)
php artisan db:seed
