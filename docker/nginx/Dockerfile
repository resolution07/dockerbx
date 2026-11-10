FROM nginx:1.22.0-alpine

# Конфигурация сайтов подключается через sites-available/sites-enabled
RUN rm -f /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html
