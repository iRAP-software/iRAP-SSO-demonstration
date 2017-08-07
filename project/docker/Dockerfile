# Due to layout of this project, the dockerfile will be moved up two directories and run during
# the build. Thus when performing any ADD commands, remember that this is "where you are"
# The layout is this way so that the settings file sits one level above the project (trunk) so that
# each dev can have their own settings and they do not get merged into the trunk.

FROM ubuntu:16.04

RUN apt-get update
RUN apt-get dist-upgrade -y


# Install the relevant packages
RUN apt-get install vim apache2 libapache2-mod-php7.0 php7.0-cli php7.0-mysql curl php7.0-curl -y

# Enable the php mod we just installed
RUN a2enmod php7.0

# expose port 80 and 443 for the web requests
EXPOSE 80


# Manually set the apache environment variables in order to get apache to work immediately.
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

# It appears that the new apache requires these env vars as well
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid

# Set up url rewrite ability
RUN a2enmod rewrite

# Install the cron service
RUN apt-get install cron -y

# Add our websites files to the default apache directory (/var/www)
# This should be as close to the last step as possible for faster rebuilds
COPY project /var/www/site/project

# Add our websites files to the default apache directory (/var/www)
COPY settings /var/www/site/settings

# Update our apache sites available with the config we created
COPY project/docker/apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# Use the crontab file.
# The crontab file was already added when we added "project"
RUN crontab /var/www/site/project/docker/crons.conf

# Make the uploads folder writeable
RUN chown root:www-data -R /var/www
RUN chmod 750 -R /var/www/site

# Execute the containers startup script which will start many processes/services
# The startup file was already added when we added "project"
CMD ["/bin/bash", "/var/www/site/project/docker/startup.sh"]
