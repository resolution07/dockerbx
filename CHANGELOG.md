# Changelog

Все заметные изменения проекта `dockerbx` собраны в этом файле.

Формат основан на [Keep a Changelog](https://keepachangelog.com/ru/1.1.0/),
версии следуют [Semantic Versioning](https://semver.org/lang/ru/).

## [1.0.0] — 2026-11-11

Первый публичный релиз Docker-окружения для разработки на 1С-Битрикс.

### Добавлено

- PHP 8.2, 8.3, 8.4 и 8.5 FPM в отдельных каталогах `docker/php/php82`–`php85`.
- Переключение версии PHP через переменную `PHP_VERSION` в `.env`.
- Полный набор PHP-расширений для 1С-Битрикс:
  - GD с FreeType, JPEG, WebP, XPM и AVIF;
  - mysqli, pdo_mysql, pgsql, pdo_pgsql;
  - intl, exif, zip, sodium, opcache;
  - xml, dom, simplexml, xmlreader, xmlwriter, xsl;
  - mbstring, curl, openssl, zlib, json, hash, fileinfo, iconv, session, pcre;
  - bcmath, bz2, calendar, gettext, ldap, shmop, sockets, sysvmsg, sysvsem, sysvshm;
  - amqp, apcu, igbinary, imagick, memcached, msgpack, redis, ssh2.
- Управление Xdebug через переменные окружения:
  - `XDEBUG_ENABLED` — включение и выключение;
  - `XDEBUG_MODE` — режимы `debug`, `develop`, `coverage`, `profile`, `trace`, `gcstats`;
  - `XDEBUG_IDEKEY` — ключ IDE.
- Генерация конфигурации Xdebug при старте PHP-контейнера.
- Nginx с конфигурацией сайтов через `sites-available` и `sites-enabled`.
- Сайт `localhost` по умолчанию.
- Скрипт `docker/nginx/sites.sh` для управления символьными ссылками.
- Контейнер Redis 7 с AOF-персистентностью и healthcheck.
- Переменная `REDIS_PORT` для порта Redis на хосте.
- Скрипт проверки расширений `docker/php/check-extensions.php` и команда `make php-check`.
- Адаптивная тёмная стартовая страница со статусом окружения.
- Makefile-команды для сборки, запуска, логов, Redis, Nginx и PHP.

### Исправлено

- PHP-FPM теперь слушает `0.0.0.0:9000`, чтобы Nginx мог подключиться к контейнеру.
- Исправлена незакрытая секция `location = /favicon.png` в конфигурации Nginx.
- Учтены различия OPcache между PHP 8.2–8.4 и PHP 8.5.
- Убрана установка OPcache как отдельного расширения для PHP 8.5, где он встроен.

### Изменено

- Каталог `docker/php-fpm` заменён на `docker/php` с версиями PHP.
- Обновлена документация по запуску, расширениям, Xdebug, Nginx и Redis.
