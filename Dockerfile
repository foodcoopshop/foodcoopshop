FROM node:18-alpine AS node
FROM webdevops/php-nginx:8.2-alpine

#https://stackoverflow.com/questions/44447821/how-to-create-a-docker-image-for-php-and-node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

RUN apk update && \
    apk add gettext && \
    npm install -g npm-check-updates && \
    npm install -g eslint && \
    npm install -g npm@9.7.2

#avoid permission error on gitpod on running npm install
RUN npm config set cache /app/tmp --global
