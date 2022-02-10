FROM php:8.1-cli
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
#CMD ["php", "./main.php"]