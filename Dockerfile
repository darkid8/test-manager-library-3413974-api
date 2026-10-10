FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libpq-dev \
    unzip \
    && docker-php-ext-install pdo_pgsql pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Run composer install if needed (for now, we assume it's installed or mapped)
# ENV PORT=8000
EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
