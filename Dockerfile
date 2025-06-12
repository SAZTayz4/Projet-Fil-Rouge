FROM php:8.2-apache

# Installe les extensions nécessaires à PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Active mod_rewrite (utile pour les frameworks ou routes custom)
RUN a2enmod rewrite

# Copie tout dans /var/www/html
COPY . /var/www/html/

# Change le DocumentRoot d’Apache vers le dossier `public/`
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
