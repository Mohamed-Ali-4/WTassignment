# PHP + Apache image used for cloud hosting (Render, Railway, Fly.io, Google Cloud Run...)
FROM php:8.3-apache

# Enable .htaccess overrides and the headers module
RUN a2enmod headers \
 && sed -ri 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Upload limits a little above the 2 MB photo limit
RUN { echo 'upload_max_filesize=3M'; echo 'post_max_size=4M'; echo 'expose_php=Off'; } \
    > /usr/local/etc/php/conf.d/app.ini

COPY . /var/www/html/
RUN rm -rf /var/www/html/.git /var/www/html/Dockerfile /var/www/html/render.yaml \
 && chown -R www-data:www-data /var/www/html

# Cloud platforms pass the port in $PORT (Render/Cloud Run); default to 80 locally
ENV PORT=80
CMD sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf \
 && sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf \
 && apache2-foreground
