FROM webdevops/php-nginx:8.1

RUN apt-get update && apt-get install gettext-base && apt-get install gettext && apt-get -y install curl software-properties-common && curl -sL https://deb.nodesource.com/setup_16.x | bash - && apt-get install nodejs && npm install -g npm-check-updates