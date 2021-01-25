FROM php:5.6-apache


RUN apt-get update
RUN apt-get install -y nano
RUN apt-get update && apt-get -y install cron
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get -y install rsyslog

RUN apt-get update && apt-get install -y git

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

# If the container's stdio is connected to systemd-journald,
# /proc/self/fd/{1,2} are Unix sockets and apache will not be able to open()
# them. Use "cat" to write directly to the already opened fds without opening
# them again.
RUN sed -i 's#ErrorLog /proc/self/fd/2#ErrorLog "|$/bin/cat 1>\&2"#' /etc/apache2/apache2.conf
RUN sed -i 's#CustomLog /proc/self/fd/1 combined#CustomLog "|/bin/cat" combined#' /etc/apache2/apache2.conf

RUN mkdir /var/euro-champ

RUN mkdir /var/euro-champ/app
RUN mkdir /var/euro-champ/app/public
RUN mkdir /var/euro-champ/framework
RUN mkdir /var/euro-champ/framework/cache
RUN mkdir /var/euro-champ/framework/sessions
RUN mkdir /var/euro-champ/framework/views
RUN mkdir /var/euro-champ/logs
RUN mkdir /var/euro-champ/images

ADD ./ /var/www/html

RUN chmod 777 -R /var/www/html/bootstrap/cache

RUN chmod 777 -R /var/www/html/storage/

RUN mv /var/www/html/.env_prod /var/www/html/.env

RUN rm /etc/apache2/sites-enabled/000-default.conf
ADD ./000-default.conf /etc/apache2/sites-enabled/000-default.conf

WORKDIR /var/www/html

RUN composer install

RUN chmod 777 -R /var/euro-champ/images

RUN ln -s /var/euro-champ/images /var/www/html/public/images
RUN ln -s /var/euro-champ /var/www/html/storage


# Add these lines to your own Dockerfile
ADD cron/files/crontab /app/crontab
RUN crontab /app/crontab
ADD cron/files/bin/start-cron.sh /usr/bin/start-cron.sh
RUN chmod +x /usr/bin/start-cron.sh
RUN touch /var/log/cron.log

#CMD /usr/bin/start-cron.sh

RUN a2enmod rewrite

