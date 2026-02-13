FROM php:8.2-cli

WORKDIR /app

# Extensions needed by the project (Supabase/PostgreSQL via PDO)
RUN docker-php-ext-install pdo pdo_pgsql

COPY . .

ENV PORT=8080
EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t . index.php"]
