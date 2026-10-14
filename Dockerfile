FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY views ./views
COPY public ./public
RUN npm run build:css

FROM php:8.4-cli-alpine
RUN apk add --no-cache libpq && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev && docker-php-ext-install pdo_pgsql && apk del .build-deps
WORKDIR /app
COPY . .
COPY --from=assets /app/public/assets/app.css ./public/assets/app.css
COPY docker/php.ini /usr/local/etc/php/conf.d/taskflow.ini
# L’application et le serveur intégré tournent sans privilèges root.
USER www-data
EXPOSE 8000
CMD ["sh", "docker/entrypoint.sh"]
