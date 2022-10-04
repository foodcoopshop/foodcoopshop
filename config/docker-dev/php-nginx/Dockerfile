FROM node:latest AS node
FROM webdevops/php-nginx:8.1

#https://stackoverflow.com/questions/44447821/how-to-create-a-docker-image-for-php-and-node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

#avoid permission error on gitpod on running npm install
RUN npm config set cache /var/www/html/tmp --global

RUN apt-get update && \
    apt-get install gettext-base && apt-get install gettext && \
    npm install -g npm-check-updates
