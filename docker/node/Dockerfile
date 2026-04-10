FROM node:20-alpine

WORKDIR /var/www/html

COPY docker/node/entrypoint-dev.sh /usr/local/bin/node-entrypoint-dev
RUN chmod +x /usr/local/bin/node-entrypoint-dev

ENTRYPOINT ["node-entrypoint-dev"]
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
