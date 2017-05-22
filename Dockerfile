FROM ubuntu:16.04

RUN apt-get update
RUN apt-get dist-upgrade -y


# Install the relevant packages
RUN apt-get install apache2 libapache2-mod-php7.0 php7.0-cli


# Enable php and its mods in apache
RUN a2enmod php7.0

# expose port 80 and 443 for the web requests
EXPOSE 80
EXPOSE 443


###### Update the php INI settings #########
RUN sed -i 's;display_errors = .*;display_errors = Off;' /etc/php/7.0/apache2/php.ini

####### END of updating php INI ########
########################################

# Manually set the apache environment variables in order to get apache to work immediately.
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

# It appears that the new apache requires these env vars as well
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid


# Install the cron service
RUN apt-get install cron -y

# Add our websites files to the default apache directory (/var/www)
# This should be as close to the last step as possible for faster rebuilds
ADD settings /var/www/site/settings

# Remove the docker_settings file from the container because this file contains the encryption key
RUN /bin/rm /var/www/site/settings/docker_settings.sh

# Add our websites files to the default apache directory (/var/www)
ADD project /var/www/site/project

# Update our apache sites available with the config we created
ADD project/docker/apache-config.conf /etc/apache2/sites-enabled/000-default.conf


RUN chown root:www-data -R /var/www
RUN chmod 750 -R /var/www/site

# Execute the containers startup script which will start many processes/services
# The startup file was already added when we added "project"
CMD ["/bin/bash", "/var/www/site/project/docker/startup.sh"]
