FROM php:8.4-fpm

# Dependências do sistema
RUN apt-get update && apt-get install -y \
  git \
  curl \
  zip \
  unzip \
  libpq-dev \
  libzip-dev \
  supervisor \
  && rm -rf /var/lib/apt/lists/*

# Extensões PHP
RUN docker-php-ext-install pdo pdo_pgsql zip pcntl opcache

# Redis via pecl (não disponível via apt na image oficial do Docker)
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia composer.json/lock primeiro para aproveitar cache de camadas
# O contexto é a raiz do monorepo, então referenciamos backend/
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Copia o restante do código do backend
COPY backend/ .

# Gera autoload otimizado
RUN composer dump-autoload --optimize

# Permissões do Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Supervisor gerencia php-fpm + queue + scheduler
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]