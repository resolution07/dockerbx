# Docker-окружение для разработки на Bitrix

[![PHP 8.2-8.5](https://img.shields.io/badge/PHP-8.2--8.5-777BB4?logo=php)](https://php.net/)
[![MySQL 8.0](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)](https://mysql.com/)
[![Nginx 1.22](https://img.shields.io/badge/Nginx-1.22-269539?logo=nginx)](https://nginx.org/)
[![Release](https://img.shields.io/badge/release-1.0.0-22d3ee)](https://github.com/resolution07/dockerbx/releases/tag/v1.0.0)

Полностью изолированная Docker-среда для разработки проектов на **1С-Битрикс** с поддержкой нескольких версий PHP, Nginx и MySQL 8.0.

**Текущая версия:** 1.0.0 · [История изменений](CHANGELOG.md)

## 📦 Состав окружения

- **PHP 8.2 / 8.3 / 8.4 / 8.5 FPM** (на базе Alpine Linux)
- **Nginx 1.22** (на базе Alpine Linux)
- **MySQL 8.0**
- **Redis 7** для кеширования и хранения сессий
- **Полный набор PHP-расширений для 1С-Битрикс** (GD, Intl, MySQL/PostgreSQL, ZIP, LDAP, Redis, Memcached, Imagick и др.)
- **Xdebug 3** с управлением через переменные окружения
- **Composer 2.8** для управления зависимостями
- Автоматическая настройка прав доступа
- Инструменты мониторинга (логи, healthchecks)

## 🚀 Быстрый старт

1. Клонируйте репозиторий:

```bash
   git clone https://github.com/resolution07/dockerbx83.git
   cd dockerbx83
   ```

2. Скопируйте `.env.example` в `.env` и при необходимости укажите версию PHP:

```bash
   cp .env.example .env
   ```

3. Соберите и запустите контейнеры:

```bash
   make build-up
   ```

4. После запуска проект будет доступен по адресу:

```
http://localhost
```

Стартовая страница показывает текущую версию PHP, статус MySQL/Redis, состояние Xdebug и OPcache, а также количество загруженных расширений для 1С-Битрикс.

## ⚙️ Переменные окружения (`.env`)

```ini
# Версия PHP: php82 | php83 | php84 | php85
PHP_VERSION=php85

# Включение/выключение Xdebug
XDEBUG_ENABLED=true

# Режимы Xdebug через запятую:
# off, develop, debug, coverage, profile, trace, gcstats
XDEBUG_MODE=debug,develop

# Идентификатор IDE для отладки
XDEBUG_IDEKEY=PHPSTORM

# Порт Redis на хосте (внутри Docker-сети — redis:6379)
REDIS_PORT=6379

DB_USERNAME=bitrix
DB_PASSWORD=FB0pr297)R{Env@
DB_DATABASE=bitrix_db
```

После изменения `PHP_VERSION` или переменных Xdebug пересоберите/перезапустите PHP-контейнер:

```bash
make build-up
# или
docker compose up -d --build php-fpm
```

### Версии PHP

Конфигурация каждой версии лежит в отдельной директории:

```
docker/php/
├── docker-entrypoint.sh   # генерация конфигурации Xdebug при старте
├── php82/
│   ├── Dockerfile
│   ├── php.ini
│   ├── www.conf
│   └── conf.d/
├── php83/
├── php84/
└── php85/
```

Активная версия выбирается переменной `PHP_VERSION` (например, `php85`).

### Xdebug

- `XDEBUG_ENABLED=false` — расширение Xdebug не загружается.
- `XDEBUG_ENABLED=true` — Xdebug загружается с режимами из `XDEBUG_MODE`.
- Режимы можно комбинировать: `debug,develop`, `coverage,profile`, `debug,trace` и т.д.
- Для IDE используется ключ из `XDEBUG_IDEKEY`, по умолчанию `PHPSTORM`.
- Клиент отладки подключается к `host.docker.internal:9003`.

Конфигурация Xdebug генерируется скриптом `docker/php/docker-entrypoint.sh` при каждом старте контейнера, поэтому изменения `.env` применяются после перезапуска.

### PHP-расширения для 1С-Битрикс

Во все версии PHP устанавливаются обязательные для Битрикс расширения и дополнительные расширения, используемые его модулями:

- ядро: `gd` (FreeType, JPEG, WebP, XPM, AVIF), `mbstring`, `mysqli`, `pdo_mysql`, `intl`, `exif`, `zip`, `sodium`, `opcache`, `xml`, `dom`, `simplexml`, `xmlreader`, `xmlwriter`, `curl`, `openssl`, `zlib`, `json`, `hash`, `fileinfo`, `iconv`, `session`, `pcre`;
- модули: `bcmath`, `bz2`, `calendar`, `gettext`, `ldap`, `pgsql`, `pdo_pgsql`, `shmop`, `sockets`, `sysvmsg`, `sysvsem`, `sysvshm`, `xsl`;
- интеграции и кеширование: `amqp`, `apcu`, `igbinary`, `imagick`, `memcached`, `msgpack`, `redis`, `ssh2`.

Проверить соответствие окружения требованиям Битрикс можно командой:

```bash
make php-check
```

Скрипт проверяет расширения, функции, PDO-драйверы и классы, необходимые для работы 1С-Битрикс, и завершается с ошибкой, если чего-то не хватает.

## 🔴 Redis

В состав окружения добавлен Redis 7:

- имя хоста внутри Docker-сети: `redis`;
- порт внутри Docker-сети: `6379`;
- порт на хосте задаётся переменной `REDIS_PORT` (по умолчанию `6379`);
- данные сохраняются в Docker-томе `redis_data`;
- включён AOF (`appendonly yes`) и периодические snapshot-ы.

Проверка:

```bash
docker compose exec redis redis-cli ping
# PONG

docker compose exec php-fpm php -r \
  '$r = new Redis(); $r->connect("redis", 6379); echo $r->ping(), PHP_EOL;'
```

Для подключения Битрикса к Redis используйте хост `redis` и порт `6379` в настройках кеширования/сессий (`/bitrix/.settings.php` или административный интерфейс).

## 🌐 Сайты Nginx

Сайты хранятся в `docker/nginx/sites-available`, а подключаются через символьные ссылки в `docker/nginx/sites-enabled`:

```
docker/nginx/
├── nginx.conf
├── sites-available/
│   └── localhost.conf
├── sites-enabled/
│   └── localhost.conf -> ../sites-available/localhost.conf
└── sites.sh
```

По умолчанию включён сайт `localhost` (порт 80, `root /var/www/html`).

Управление ссылками:

```bash
# связать все сайты из sites-available
make sites-link
# или
./docker/nginx/sites.sh

# включить/выключить конкретный сайт
./docker/nginx/sites.sh enable example.conf
./docker/nginx/sites.sh disable example.conf

# показать статус сайтов
make sites-list
```

После изменения списка сайтов перезагрузите Nginx:

```bash
docker compose restart nginx
```

## 🛠 Управление окружением

### 🚀 Основные команды

- `make build` — сборка Docker-образов без запуска
- `make up` — запуск контейнеров в фоновом режиме
- `make build-up` — сборка + запуск (рекомендуется для первого запуска)
- `make down` — остановка всех контейнеров
- `make reload` — перезапуск контейнеров

### 🐞 Отладка и логи

- `make php-bash` — вход в PHP-контейнер (интерактивный bash)
- `make php-check` — проверка PHP-расширений для 1С-Битрикс
- `docker compose logs -f nginx` — просмотр логов Nginx в реальном времени
- `docker compose logs -f php-fpm` — мониторинг PHP-FPM логов
- `make remove-logs` — очистка всех логов (требует sudo)

### ⚙️ Управление сервисами

- `docker compose restart nginx` — перезапуск конкретного сервиса
- `docker compose ps` — проверка статуса контейнеров
- `docker compose exec redis redis-cli` — консоль Redis
- `docker compose exec php-fpm composer update` — обновление зависимостей
- `docker compose exec php-fpm php -v` — проверка активной версии PHP

### 📝 Логи

Логи доступны в соответствующих директориях:

- `Nginx`: `docker/nginx/log/`
- `PHP-FPM`: `docker/php/php<VERSION>/log/`

### 📌 Особенности

- Оптимизированные Alpine-образы
- Автоматическая установка прав для Битрикс
- Готовые health-чеки для мониторинга
- Поддержка `.env`-файлов для конфигурации
- Переключение версии PHP без изменения `docker-compose.yml`
