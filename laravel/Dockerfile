FROM php:8.2-fpm

WORKDIR /var/www

# 1. Install system deps and PHP extensions (ceci ne change jamais, donc on le fait très tôt)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 2. On ajoute composer (pareil, stable)
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# 3. On copie juste les fichiers de dépendences au début
COPY composer.json composer.lock ./

# 4. On installe les dépendances PHP sans copier tout le projet (maxime le cache !)
RUN composer install --no-scripts --no-autoloader

# 5. Ensuite seulement on copie le reste (code Laravel, public, etc)
COPY . .

# 6. Maintenant on termine le composer install (pour scripts/autoloader qui peuvent dépendre du code)
RUN composer install

# Ajoute AVANT d’entrer dans la CMD d’exécution
ADD https://github.com/vishnubob/wait-for-it/raw/master/wait-for-it.sh /wait-for-it.sh
RUN chmod +x /wait-for-it.sh

CMD ["php-fpm"]
