FROM php:8.4-fpm

# Dependências do sistema
RUN apt-get update && apt-get install -y \
  git \
  curl \
  zip \
  unzip \
  libpq-dev \
  libzip-dev \
  libpng-dev \
  libjpeg-dev \
  libfreetype6-dev \
  libicu-dev \
  supervisor \
  && rm -rf /var/lib/apt/lists/*

# Extensões PHP
# - gd: necessário pra TCPDF (logo na DANFSe customizada do SDK NFSe)
# - intl: necessário pra Filament 5 (symfony/translation pluralization,
#         formatação de números/datas localizadas)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install pdo pdo_pgsql zip pcntl opcache gd intl

# OpenSSL legacy provider (rsa-sha1 do SDK NFSe — DPS exige)
COPY docker/openssl-sha1.cnf /etc/ssl/openssl-sha1.cnf
ENV OPENSSL_CONF=/etc/ssl/openssl-sha1.cnf

# Redis via pecl (não disponível via apt na image oficial do Docker)
RUN pecl install redis && docker-php-ext-enable redis

# Composer
# Atenção GHSA-f9f8-rm49-7jv2 (publicado 13/mai/2026): Composer < 2.9.8
# vaza GHS JWT tokens em mensagens de erro. composer:latest no Docker Hub
# pode estar atrasada algumas horas após o release — forçamos self-update
# pra garantir >=2.9.8. Em runtime do container produção não autenticamos
# contra GitHub, mas é defesa em profundidade (qualquer composer install
# manual dentro do container fica protegido).
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer self-update --2 --stable --no-interaction 2>/dev/null || true

WORKDIR /var/www/html

# Copia composer.json/lock primeiro para aproveitar cache de camadas
# O contexto é a raiz do monorepo, então referenciamos backend/
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Copia o restante do código do backend
COPY backend/ .

# Gera autoload otimizado SEM rodar package:discover.
# package:discover boota o app (precisa de .env + cachePath válido) e
# alguns providers (BladeUI\Icons do Filament) falham em build-time
# quando o cache path ainda não existe. O cache de packages é gerado
# em runtime pelo entrypoint/scheduler ou no primeiro request.
RUN composer dump-autoload --optimize --no-scripts

# Permissões do Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Supervisor gerencia php-fpm + queue + scheduler
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]