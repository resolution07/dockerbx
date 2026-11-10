<?php

declare(strict_types=1);

function e(string|int|float|null $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function portIsOpen(string $host, int $port, float $timeout = 0.25): bool
{
    $connection = @fsockopen($host, $port, $errorCode, $errorMessage, $timeout);
    if ($connection === false) {
        return false;
    }

    fclose($connection);
    return true;
}

$phpVersion = PHP_VERSION;
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Nginx';
$nginxVersion = preg_match('~nginx/([0-9.]+)~i', $serverSoftware, $matches) ? $matches[1] : 'Nginx';
$mysqlOnline = portIsOpen('mysql', 3306);
$redisOnline = portIsOpen('redis', 6379);
$redisVersion = '7.x';

if ($redisOnline && class_exists('Redis')) {
    try {
        $redis = new Redis();
        $redis->connect('redis', 6379, 0.25);
        $serverInfo = $redis->info('server');
        if (is_array($serverInfo) && isset($serverInfo['redis_version'])) {
            $redisVersion = (string)$serverInfo['redis_version'];
        }
        $redis->close();
    } catch (Throwable) {
        $redisOnline = false;
    }
}

$xdebugLoaded = extension_loaded('xdebug');
$xdebugMode = $xdebugLoaded ? (string)(ini_get('xdebug.mode') ?: 'off') : 'off';
$opcacheEnabled = false;
if (function_exists('opcache_get_status')) {
    $opcacheStatus = @opcache_get_status(false);
    $opcacheEnabled = is_array($opcacheStatus) && ($opcacheStatus['opcache_enabled'] ?? false);
}

$bitrixExtensions = [
    'gd',
    'mysqli',
    'pdo_mysql',
    'mbstring',
    'intl',
    'exif',
    'zip',
    'sodium',
    'xml',
    'dom',
    'simplexml',
    'xmlreader',
    'xmlwriter',
    'libxml',
    'curl',
    'openssl',
    'zlib',
    'json',
    'hash',
    'fileinfo',
    'iconv',
    'session',
    'ctype',
    'filter',
    'tokenizer',
    'pcre',
    'Zend OPcache',
    'bcmath',
    'bz2',
    'calendar',
    'gettext',
    'ldap',
    'pgsql',
    'pdo_pgsql',
    'shmop',
    'sockets',
    'sysvmsg',
    'sysvsem',
    'sysvshm',
    'xsl',
    'amqp',
    'apcu',
    'igbinary',
    'imagick',
    'memcached',
    'msgpack',
    'redis',
    'ssh2',
];
$missingExtensions = array_values(
    array_filter(
        $bitrixExtensions,
        static fn(string $extension): bool => !extension_loaded($extension)
    )
);
$loadedExtensionsCount = count($bitrixExtensions) - count($missingExtensions);
$bitrixReady = $missingExtensions === [];

$statusItems = [
    [
        'icon' => 'code',
        'label' => 'PHP',
        'value' => $phpVersion,
        'meta' => strtoupper(PHP_SAPI),
        'online' => true,
    ],
    [
        'icon' => 'server',
        'label' => 'Nginx',
        'value' => $nginxVersion,
        'meta' => 'HTTP / FastCGI',
        'online' => true,
    ],
    [
        'icon' => 'database',
        'label' => 'MySQL',
        'value' => '8.0',
        'meta' => $mysqlOnline ? 'порт 3306 доступен' : 'контейнер недоступен',
        'online' => $mysqlOnline,
    ],
    [
        'icon' => 'layers',
        'label' => 'Redis',
        'value' => $redisVersion,
        'meta' => $redisOnline ? 'кеш и сессии' : 'контейнер недоступен',
        'online' => $redisOnline,
    ],
    [
        'icon' => 'bug',
        'label' => 'Xdebug',
        'value' => $xdebugLoaded ? 'включён' : 'выключен',
        'meta' => $xdebugLoaded ? $xdebugMode : 'XDEBUG_ENABLED=false',
        'online' => $xdebugLoaded,
    ],
    [
        'icon' => 'bolt',
        'label' => 'OPcache',
        'value' => $opcacheEnabled ? 'активен' : 'выключен',
        'meta' => $opcacheEnabled ? 'ускорение PHP' : 'проверьте настройки',
        'online' => $opcacheEnabled,
    ],
    [
        'icon' => 'package',
        'label' => 'Composer',
        'value' => '2.8',
        'meta' => 'управление зависимостями',
        'online' => true,
    ],
    [
        'icon' => 'shield',
        'label' => 'Bitrix-ready',
        'value' => $loadedExtensionsCount . ' / ' . count($bitrixExtensions),
        'meta' => $bitrixReady ? 'все расширения на месте' : implode(', ', array_slice($missingExtensions, 0, 3)),
        'online' => $bitrixReady,
    ],
];

$icons = [
    'code' => '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
    'server' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="6" rx="2"/><rect x="3" y="14" width="18" height="6" rx="2"/><line x1="7" y1="7" x2="7.01" y2="7"/><line x1="7" y1="17" x2="7.01" y2="17"/></svg>',
    'database' => '<svg viewBox="0 0 24 24" aria-hidden="true"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.66 3.58 3 8 3s8-1.34 8-3V5"/><path d="M4 11v6c0 1.66 3.58 3 8 3s8-1.34 8-3v-6"/></svg>',
    'layers' => '<svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="12 2 22 8 12 14 2 8 12 2"/><polyline points="2 12 12 18 22 12"/><polyline points="2 16 12 22 22 16"/></svg>',
    'bug' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3l1.5 2.5M16 3l-1.5 2.5"/><rect x="6" y="6" width="12" height="12" rx="6"/><path d="M3 10h3M18 10h3M3 14h3M18 14h3M12 18v3"/></svg>',
    'bolt' => '<svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
    'package' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5v8l9 5 9-5V8z"/><polyline points="3 8 12 13 21 8"/><line x1="12" y1="13" x2="12" y2="22"/></svg>',
    'shield' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>',
    'terminal' => '<svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>',
    'php' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 8h3.5a2.5 2.5 0 0 1 0 5H7V8zm0 5v3M14 8v8M14 11h3a2 2 0 0 1 0 4h-3"/></svg>',
    'nginx' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V4l16 16V4"/></svg>',
    'redis' => '<svg viewBox="0 0 24 24" aria-hidden="true"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.66 3.58 3 8 3s8-1.34 8-3V5"/><path d="M4 11v6c0 1.66 3.58 3 8 3s8-1.34 8-3v-6"/></svg>',
    'github' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>',
    'arrow' => '<svg viewBox="0 0 24 24" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
    'copy' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>',
];

$features = [
    [
        'icon' => 'php',
        'title' => '4 версии PHP',
        'text' => 'PHP 8.2, 8.3, 8.4 и 8.5 в отдельных директориях. Версия выбирается одной переменной PHP_VERSION в .env.',
        'chips' => ['php82', 'php83', 'php84', 'php85'],
    ],
    [
        'icon' => 'bug',
        'title' => 'Xdebug под контролем',
        'text' => 'Включение и режимы отладки задаются через XDEBUG_ENABLED и XDEBUG_MODE. Конфигурация собирается при старте контейнера.',
        'chips' => ['debug', 'profile', 'coverage'],
    ],
    [
        'icon' => 'nginx',
        'title' => 'Nginx sites',
        'text' => 'Привычная схема sites-available / sites-enabled. Скрипт sites.sh помогает включать и выключать сайты.',
        'chips' => ['sites-available', 'sites-enabled'],
    ],
    [
        'icon' => 'redis',
        'title' => 'Redis из коробки',
        'text' => 'Отдельный контейнер Redis 7 с AOF-персистентностью. Подходит для кеша и хранения сессий Битрикса.',
        'chips' => ['кеш', 'сессии', 'AOF'],
    ],
    [
        'icon' => 'shield',
        'title' => 'Bitrix-ready',
        'text' => 'Все обязательные расширения PHP и популярные интеграции: GD, Intl, MySQL, ZIP, LDAP, Redis, Memcached, Imagick и другие.',
        'chips' => ['48 расширений', 'php-check'],
    ],
    [
        'icon' => 'package',
        'title' => 'Composer 2.8',
        'text' => 'Composer уже внутри PHP-контейнера. Зависимости ставятся привычной командой без дополнительной настройки.',
        'chips' => ['composer install', 'composer update'],
    ],
];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <meta name="theme-color" content="#07090d">
    <meta name="generator" content="dockerbx 1.0.0">
    <meta name="description"
          content="dockerbx — Docker-окружение для разработки на 1С-Битрикс: PHP 8.2–8.5, Nginx, MySQL, Redis, Xdebug и Composer.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="dockerbx — Docker для 1С-Битрикс">
    <meta property="og:description"
          content="PHP 8.2–8.5, Nginx, MySQL, Redis, Xdebug, Composer и полный набор расширений для Bitrix Framework.">
    <link rel="icon"
          href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23070b10'/%3E%3Cpath d='M20 24 12 32l8 8M44 24l8 8-8 8M36 18l-8 28' stroke='%2322d3ee' stroke-width='4' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">
    <title>dockerbx — Docker для 1С-Битрикс</title>
    <style>
        :root {
            --bg: #07090d;
            --bg-soft: #0a0e14;
            --panel: rgba(15, 20, 29, 0.78);
            --panel-solid: #0e131b;
            --panel-hover: #121924;
            --line: rgba(148, 163, 184, 0.14);
            --line-strong: rgba(148, 163, 184, 0.24);
            --text: #e8edf5;
            --muted: #8e9aae;
            --muted-strong: #b6c0d0;
            --accent: #22d3ee;
            --accent-soft: rgba(34, 211, 238, 0.12);
            --accent-2: #818cf8;
            --success: #34d399;
            --success-soft: rgba(52, 211, 153, 0.12);
            --danger: #fb7185;
            --danger-soft: rgba(251, 113, 133, 0.12);
            --warning: #fbbf24;
            --radius-sm: 12px;
            --radius: 18px;
            --radius-lg: 26px;
            --shadow: 0 24px 80px rgba(0, 0, 0, 0.38);
            --font: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --mono: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: var(--font);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -2;
            background: radial-gradient(circle at 12% 0%, rgba(34, 211, 238, 0.13), transparent 34rem),
            radial-gradient(circle at 88% 8%, rgba(129, 140, 248, 0.14), transparent 32rem),
            radial-gradient(circle at 50% 100%, rgba(34, 211, 238, 0.05), transparent 42rem);
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            opacity: 0.35;
            background-image: linear-gradient(rgba(148, 163, 184, 0.045) 1px, transparent 1px),
            linear-gradient(90deg, rgba(148, 163, 184, 0.045) 1px, transparent 1px);
            background-size: 64px 64px;
            -webkit-mask-image: linear-gradient(to bottom, black, transparent 78%);
            mask-image: linear-gradient(to bottom, black, transparent 78%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        a {
            -webkit-tap-highlight-color: transparent;
        }

        ::selection {
            background: rgba(34, 211, 238, 0.28);
            color: #fff;
        }

        :focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 3px;
        }

        .container {
            width: min(1120px, calc(100% - 48px));
            margin: 0 auto;
        }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .site-header::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            border-bottom: 1px solid var(--line);
            background: rgba(7, 9, 13, 0.76);
        }

        .site-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            min-height: 82px;
            padding: 18px 0;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border: 1px solid rgba(34, 211, 238, 0.34);
            border-radius: 13px;
            background: linear-gradient(145deg, rgba(34, 211, 238, 0.16), rgba(129, 140, 248, 0.12));
            color: var(--accent);
            font-family: var(--mono);
            font-size: 14px;
            font-weight: 700;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .brand-text strong {
            font-size: 17px;
            letter-spacing: -0.02em;
        }

        .brand-text span {
            margin-top: 3px;
            color: var(--muted);
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .site-nav {
            display: flex;
            align-items: center;
            gap: 26px;
            color: var(--muted-strong);
            font-size: 14px;
        }

        .site-nav > a:not(.btn) {
            transition: color 160ms ease;
        }

        .site-nav > a:not(.btn):hover {
            color: var(--text);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 42px;
            padding: 0 17px;
            border: 1px solid transparent;
            border-radius: 11px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            line-height: 1;
            cursor: pointer;
            transition: transform 160ms ease, border-color 160ms ease, background 160ms ease, color 160ms ease;
        }

        .btn svg {
            width: 17px;
            height: 17px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            border-color: rgba(34, 211, 238, 0.42);
            background: linear-gradient(135deg, #22d3ee, #38bdf8);
            color: #031016;
            box-shadow: 0 8px 32px rgba(34, 211, 238, 0.22);
        }

        .btn-primary:hover {
            box-shadow: 0 12px 38px rgba(34, 211, 238, 0.28);
        }

        .btn-ghost {
            border-color: var(--line-strong);
            background: rgba(255, 255, 255, 0.025);
            color: var(--muted-strong);
        }

        .btn-ghost:hover {
            border-color: rgba(34, 211, 238, 0.4);
            background: rgba(34, 211, 238, 0.06);
            color: var(--text);
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(300px, 0.65fr);
            gap: 56px;
            align-items: center;
            padding: 84px 0 80px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 7px 12px;
            border: 1px solid rgba(34, 211, 238, 0.2);
            border-radius: 999px;
            background: rgba(34, 211, 238, 0.055);
            color: #a5f3fc;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.1), 0 0 14px rgba(52, 211, 153, 0.7);
            animation: pulse 2.6s ease-in-out infinite;
        }

        .hero h1 {
            max-width: 13ch;
            margin: 22px 0 20px;
            font-size: clamp(40px, 6vw, 72px);
            font-weight: 750;
            line-height: 0.98;
            letter-spacing: -0.055em;
        }

        .grad {
            background: linear-gradient(110deg, #67e8f9 0%, #38bdf8 44%, #a5b4fc 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-text {
            max-width: 640px;
            margin: 0;
            color: var(--muted-strong);
            font-size: clamp(16px, 1.7vw, 18px);
            line-height: 1.7;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .hero-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-top: 26px;
            color: var(--muted);
            font-size: 13px;
        }

        .hero-meta a {
            color: #a5f3fc;
            font-weight: 600;
        }

        .hero-meta a:hover {
            color: #cffafe;
        }

        .sep {
            color: rgba(148, 163, 184, 0.4);
        }

        .author-card {
            position: relative;
            overflow: hidden;
            padding: 28px;
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            background: linear-gradient(160deg, rgba(20, 27, 38, 0.95), rgba(10, 14, 20, 0.88));
            box-shadow: var(--shadow);
        }

        .author-card::before {
            content: "";
            position: absolute;
            top: -70px;
            right: -70px;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: rgba(34, 211, 238, 0.1);
            filter: blur(24px);
        }

        .author-top {
            position: relative;
            display: flex;
            align-items: center;
            gap: 18px;
            padding-top: 2px;
        }

        .avatar {
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            width: 64px;
            height: 64px;
            border: 1px solid rgba(34, 211, 238, 0.3);
            border-radius: 19px;
            background: linear-gradient(145deg, rgba(34, 211, 238, 0.2), rgba(129, 140, 248, 0.2));
            color: #ecfeff;
            font-family: var(--mono);
            font-size: 21px;
            font-weight: 700;
            letter-spacing: -0.08em;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .avatar span {
            color: var(--accent);
        }

        .author-card h2 {
            margin: 0;
            font-size: 20px;
            letter-spacing: -0.025em;
        }

        .author-card p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .author-links {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--line);
        }

        .author-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 13px;
            border: 1px solid transparent;
            border-radius: 11px;
            color: var(--muted-strong);
            font-size: 13px;
            transition: background 160ms ease, border-color 160ms ease, color 160ms ease;
        }

        .author-link:hover {
            border-color: var(--line-strong);
            background: rgba(255, 255, 255, 0.025);
            color: var(--text);
        }

        .author-link svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .section {
            padding: 54px 0;
        }

        .section-head {
            max-width: 680px;
            margin-bottom: 26px;
        }

        .eyebrow {
            margin: 0 0 10px;
            color: var(--accent);
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .section-head h2 {
            margin: 0;
            font-size: clamp(28px, 3.4vw, 42px);
            line-height: 1.08;
            letter-spacing: -0.045em;
        }

        .section-head p:last-child {
            margin: 13px 0 0;
            color: var(--muted);
            font-size: 15px;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .status-card {
            position: relative;
            overflow: hidden;
            min-height: 142px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--panel);
            transition: transform 180ms ease, border-color 180ms ease, background 180ms ease;
        }

        .status-card:hover {
            transform: translateY(-2px);
            border-color: var(--line-strong);
            background: var(--panel-hover);
        }

        .status-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .status-icon {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.025);
            color: var(--muted-strong);
        }

        .status-icon svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.7;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .status-dot {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--muted);
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .status-dot::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 10px rgba(52, 211, 153, 0.65);
        }

        .status-dot.offline::before {
            background: var(--danger);
            box-shadow: 0 0 10px rgba(251, 113, 133, 0.5);
        }

        .status-card h3 {
            margin: 17px 0 2px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .status-value {
            display: block;
            overflow: hidden;
            color: var(--text);
            font-family: var(--mono);
            font-size: 19px;
            font-weight: 600;
            letter-spacing: -0.03em;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .status-meta {
            display: block;
            overflow: hidden;
            margin-top: 5px;
            color: var(--muted);
            font-size: 12px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .health-strip {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 12px;
            padding: 14px 16px;
            border: 1px solid rgba(52, 211, 153, 0.2);
            border-radius: 14px;
            background: var(--success-soft);
            color: #a7f3d0;
            font-size: 13px;
        }

        .health-strip.warning {
            border-color: rgba(251, 191, 36, 0.24);
            background: rgba(251, 191, 36, 0.08);
            color: #fde68a;
        }

        .health-strip svg {
            flex: 0 0 auto;
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .health-strip code {
            padding: 2px 6px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            background: rgba(0, 0, 0, 0.16);
            font-family: var(--mono);
            font-size: 12px;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .feature-card {
            padding: 24px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: linear-gradient(160deg, rgba(15, 20, 29, 0.82), rgba(10, 14, 20, 0.62));
            transition: transform 180ms ease, border-color 180ms ease, background 180ms ease;
        }

        .feature-card:hover {
            transform: translateY(-2px);
            border-color: rgba(34, 211, 238, 0.24);
            background: linear-gradient(160deg, rgba(18, 24, 34, 0.9), rgba(10, 14, 20, 0.75));
        }

        .feature-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border: 1px solid rgba(34, 211, 238, 0.17);
            border-radius: 12px;
            background: var(--accent-soft);
            color: var(--accent);
        }

        .feature-icon svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.7;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .feature-card h3 {
            margin: 18px 0 8px;
            font-size: 17px;
            letter-spacing: -0.02em;
        }

        .feature-card p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.65;
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 17px;
        }

        .chip {
            padding: 4px 8px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.025);
            color: var(--muted-strong);
            font-family: var(--mono);
            font-size: 10px;
            white-space: nowrap;
        }

        .quickstart {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
            gap: 16px;
            align-items: stretch;
        }

        .terminal-card {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            background: #080b10;
            box-shadow: var(--shadow);
        }

        .terminal-top {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.02);
        }

        .terminal-dots {
            display: flex;
            gap: 6px;
        }

        .terminal-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(148, 163, 184, 0.28);
        }

        .terminal-dots span:first-child {
            background: rgba(251, 113, 133, 0.7);
        }

        .terminal-dots span:nth-child(2) {
            background: rgba(251, 191, 36, 0.7);
        }

        .terminal-dots span:nth-child(3) {
            background: rgba(52, 211, 153, 0.7);
        }

        .terminal-title {
            flex: 1;
            color: var(--muted);
            font-family: var(--mono);
            font-size: 11px;
        }

        .copy-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 8px;
            border: 1px solid transparent;
            border-radius: 7px;
            background: transparent;
            color: var(--muted);
            font-family: var(--mono);
            font-size: 10px;
            cursor: pointer;
            transition: border-color 160ms ease, background 160ms ease, color 160ms ease;
        }

        .copy-btn:hover {
            border-color: var(--line);
            background: rgba(255, 255, 255, 0.035);
            color: var(--text);
        }

        .copy-btn svg {
            width: 13px;
            height: 13px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .terminal-card pre {
            margin: 0;
            padding: 22px;
            overflow-x: auto;
            color: #cbd5e1;
            font-family: var(--mono);
            font-size: 13px;
            line-height: 1.85;
            tab-size: 4;
        }

        .terminal-card .prompt {
            color: var(--accent);
        }

        .terminal-card .comment {
            color: #64748b;
        }

        .quick-panel {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .quick-item {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 17px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--panel);
        }

        .quick-item .status-icon {
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
        }

        .quick-item h3 {
            margin: 0;
            font-size: 14px;
        }

        .quick-item p {
            margin: 3px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.55;
        }

        .quick-item code {
            color: #a5f3fc;
            font-family: var(--mono);
            font-size: 11px;
        }

        .site-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 34px;
            padding: 28px 0 40px;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 12px;
        }

        .footer-left {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .footer-left strong {
            color: var(--muted-strong);
            font-weight: 600;
        }

        .footer-links {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .footer-links a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--muted-strong);
            transition: color 160ms ease;
        }

        .footer-links a:hover {
            color: var(--accent);
        }

        .footer-links svg {
            width: 15px;
            height: 15px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.45;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        @media (max-width: 940px) {
            .hero {
                grid-template-columns: 1fr;
                gap: 38px;
                padding: 62px 0 54px;
            }

            .hero h1 {
                max-width: 15ch;
            }

            .author-card {
                max-width: 560px;
            }

            .status-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .features {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .quickstart {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .container {
                width: min(100% - 32px, 1120px);
            }

            .site-nav > a:not(.btn) {
                display: none;
            }

            .site-header-inner {
                min-height: 72px;
                padding: 14px 0;
            }

            .brand-text span {
                display: none;
            }

            .section {
                padding: 38px 0;
            }

            .hero {
                padding-top: 48px;
            }

            .status-grid,
            .features {
                grid-template-columns: 1fr;
            }

            .status-card {
                min-height: 126px;
            }

            .site-footer {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 420px) {
            .hero h1 {
                font-size: 38px;
            }

            .hero-actions .btn {
                width: 100%;
            }

            .author-card {
                padding: 22px;
            }

            .terminal-card pre {
                padding: 18px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<header class="site-header">
    <div class="container site-header-inner">
        <a class="brand" href="/" aria-label="dockerbx — на главную">
            <span class="brand-mark">&lt;/&gt;</span>
            <span class="brand-text">
                <strong>dockerbx</strong>
                <span>Bitrix dev environment · v1.0.0</span>
            </span>
        </a>

        <nav class="site-nav" aria-label="Основная навигация">
            <a href="#status">Статус</a>
            <a href="#stack">Возможности</a>
            <a href="#quickstart">Быстрый старт</a>
            <a class="btn btn-ghost" href="https://github.com/resolution07" target="_blank" rel="noopener noreferrer">
                <?= $icons['github'] ?>
                GitHub
            </a>
        </nav>
    </div>
</header>

<main>
    <section class="hero container">
        <div class="hero-copy">
            <span class="badge"><span class="dot"></span> PHP 8.2–8.5 · Nginx · MySQL · Redis</span>
            <h1>Docker-среда для <span class="grad">1С-Битрикс</span>, которая просто работает.</h1>
            <p class="hero-text">
                Готовая сборка для разработки: несколько версий PHP, полный набор расширений,
                Xdebug с управлением через окружение, Nginx с sites-available, MySQL и Redis.
            </p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="https://github.com/resolution07" target="_blank"
                   rel="noopener noreferrer">
                    <?= $icons['github'] ?>
                    Открыть GitHub
                </a>
                <a class="btn btn-ghost" href="#status">
                    Проверить окружение
                    <?= $icons['arrow'] ?>
                </a>
            </div>
            <div class="hero-meta">
                <span>Автор сборки</span>
                <a href="https://github.com/resolution07" target="_blank" rel="noopener noreferrer">@resolution07</a>
                <span class="sep">·</span>
                <span>Dev-friendly by default</span>
            </div>
        </div>

        <aside class="author-card" aria-label="Автор сборки">
            <div class="author-top">
                <div class="avatar">R<span>07</span></div>
                <div>
                    <h2>resolution07</h2>
                    <p>Автор и maintainer dockerbx</p>
                </div>
            </div>
            <div class="author-links">
                <a class="author-link" href="https://github.com/resolution07" target="_blank" rel="noopener noreferrer">
                    <span>github.com/resolution07</span>
                    <?= $icons['github'] ?>
                </a>
                <a class="author-link" href="https://dev.1c-bitrix.ru/" target="_blank" rel="noopener noreferrer">
                    <span>Документация Bitrix Framework</span>
                    <?= $icons['arrow'] ?>
                </a>
            </div>
        </aside>
    </section>

    <section class="section container" id="status">
        <div class="section-head">
            <p class="eyebrow">Live status</p>
            <h2>Окружение прямо сейчас</h2>
            <p>Данные получены из работающего PHP-контейнера. Если сервис недоступен, карточка подсветится красным.</p>
        </div>

        <div class="status-grid">
            <?php
            foreach ($statusItems as $item): ?>
                <article class="status-card">
                    <div class="status-top">
                        <span class="status-icon"><?= $icons[$item['icon']] ?></span>
                        <span class="status-dot <?= $item['online'] ? '' : 'offline' ?>">
                                <?= $item['online'] ? 'online' : 'offline' ?>
                            </span>
                    </div>
                    <h3><?= e($item['label']) ?></h3>
                    <span class="status-value"><?= e($item['value']) ?></span>
                    <span class="status-meta"><?= e($item['meta']) ?></span>
                </article>
            <?php
            endforeach; ?>
        </div>

        <div class="health-strip <?= $bitrixReady ? '' : 'warning' ?>">
            <?= $icons['shield'] ?>
            <?php
            if ($bitrixReady): ?>
                <span>Все <?= count(
                        $bitrixExtensions
                    ) ?> расширений, необходимых для 1С-Битрикс, загружены. Проверка: <code>make php-check</code></span>
            <?php
            else: ?>
                <span>Не хватает расширений: <?= e(
                        implode(', ', $missingExtensions)
                    ) ?>. Запустите <code>make php-check</code> для подробностей.</span>
            <?php
            endif; ?>
        </div>
    </section>

    <section class="section container" id="stack">
        <div class="section-head">
            <p class="eyebrow">Что внутри</p>
            <h2>Всё для комфортной разработки</h2>
            <p>Ничего лишнего для старта и достаточно возможностей для реальных проектов на Bitrix Framework.</p>
        </div>

        <div class="features">
            <?php
            foreach ($features as $feature): ?>
                <article class="feature-card">
                    <span class="feature-icon"><?= $icons[$feature['icon']] ?></span>
                    <h3><?= e($feature['title']) ?></h3>
                    <p><?= e($feature['text']) ?></p>
                    <div class="chips">
                        <?php
                        foreach ($feature['chips'] as $chip): ?>
                            <span class="chip"><?= e($chip) ?></span>
                        <?php
                        endforeach; ?>
                    </div>
                </article>
            <?php
            endforeach; ?>
        </div>
    </section>

    <section class="section container" id="quickstart">
        <div class="section-head">
            <p class="eyebrow">Quick start</p>
            <h2>Запуск за пару минут</h2>
            <p>Скопируйте окружение, выберите версию PHP и поднимите контейнеры.</p>
        </div>

        <div class="quickstart">
            <div class="terminal-card">
                <div class="terminal-top">
                    <span class="terminal-dots" aria-hidden="true"><span></span><span></span><span></span></span>
                    <span class="terminal-title">bash — dockerbx</span>
                    <button class="copy-btn" type="button" data-copy-target="quickstart-commands"
                            aria-label="Скопировать команды">
                        <?= $icons['copy'] ?>
                        <span>copy</span>
                    </button>
                </div>
                <pre><code id="quickstart-commands"><span class="comment"># 1. Подготовить окружение</span>
<span class="prompt">$</span> cp .env.example .env

<span class="comment"># 2. Выбрать версию PHP: php82 | php83 | php84 | php85</span>
<span class="prompt">$</span> nano .env

<span class="comment"># 3. Собрать и запустить</span>
<span class="prompt">$</span> make build-up

<span class="comment"># 4. Проверить расширения Bitrix</span>
<span class="prompt">$</span> make php-check</code></pre>
            </div>

            <div class="quick-panel">
                <div class="quick-item">
                    <span class="status-icon"><?= $icons['terminal'] ?></span>
                    <div>
                        <h3>PHP-контейнер</h3>
                        <p>Вход в окружение: <code>make php-bash</code></p>
                    </div>
                </div>
                <div class="quick-item">
                    <span class="status-icon"><?= $icons['redis'] ?></span>
                    <div>
                        <h3>Redis</h3>
                        <p>Хост <code>redis</code>, порт <code>6379</code>. Консоль: <code>make redis-cli</code></p>
                    </div>
                </div>
                <div class="quick-item">
                    <span class="status-icon"><?= $icons['server'] ?></span>
                    <div>
                        <h3>Nginx</h3>
                        <p>Сайты в <code>docker/nginx/sites-available</code>. Ссылки: <code>make sites-link</code></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer container">
    <div class="footer-left">
        <strong>dockerbx v1.0.0</strong>
        <span class="sep">·</span>
        <span>сделано для разработки на 1С-Битрикс</span>
    </div>
    <div class="footer-links">
        <a href="https://github.com/resolution07" target="_blank" rel="noopener noreferrer">
            <?= $icons['github'] ?>
            @resolution07
        </a>
        <span>© <?= date('Y') ?></span>
    </div>
</footer>

<script>
    document.querySelectorAll('[data-copy-target]').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = document.getElementById(button.dataset.copyTarget);
            if (!target) return;

            const label = button.querySelector('span');
            const originalLabel = label ? label.textContent : '';

            try {
                await navigator.clipboard.writeText(target.innerText);
                if (label) label.textContent = 'copied';
            } catch {
                if (label) label.textContent = 'error';
            }

            window.setTimeout(() => {
                if (label) label.textContent = originalLabel;
            }, 1600);
        });
    });
</script>
</body>
</html>
