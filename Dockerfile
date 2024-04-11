FROM node:20-alpine AS node
FROM webdevops/php-nginx:8.3-alpine

# https://stackoverflow.com/questions/44447821/how-to-create-a-docker-image-for-php-and-node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

RUN apk update && \
    npm install -g npm-check-updates && \
    npm install -g eslint
    
RUN npm install -g npm

# avoid permission error on gitpod on running npm install
RUN npm config set cache /app/tmp --global

# install pcov on alpine requires some addtional packages
RUN apk add gcc musl-dev make autoconf && \
    pecl install pcov && \
    docker-php-ext-enable pcov && \
    echo 'pcov.directory = /app' >> /usr/local/etc/php/php.ini
