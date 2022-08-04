FROM php:8.1-apache

WORKDIR /var/www/html/HomeDashboard
COPY ./httpd.conf /usr/local/apache2/conf.d/

# enable pdo_mysql
RUN docker-php-source extract \
    && docker-php-ext-install mysqli pdo pdo_mysql

# reloads apache and all configuarations
RUN /etc/init.d/apache2 reload
# initiate db tables wit default value
CMD [ "php", "./initializer.php" ]
