<?php
/**
 * Проверка PHP-расширений, необходимых для работы 1С-Битрикс.
 *
 * Запуск:
 *   php /usr/local/bin/check-bitrix-extensions.php
 */

$requiredExtensions = [
    // Ядро Bitrix / официальные требования
    'gd' => 'обработка изображений и CAPTCHA',
    'mysqli' => 'работа с MySQL',
    'pdo_mysql' => 'работа с MySQL через PDO',
    'mbstring' => 'многобайтовые строки',
    'intl' => 'локализация и интернационализация',
    'exif' => 'метаданные изображений',
    'zip' => 'ZIP-архивы (Генератор документов)',
    'sodium' => 'современное шифрование',
    'xml' => 'XML (система обновлений)',
    'dom' => 'DOMDocument',
    'simplexml' => 'SimpleXML',
    'xmlreader' => 'XMLReader',
    'xmlwriter' => 'XMLWriter',
    'libxml' => 'libxml',
    'curl' => 'HTTP-запросы и обновления',
    'openssl' => 'SSL/TLS и шифрование',
    'zlib' => 'сжатие (система обновлений)',
    'json' => 'JSON',
    'hash' => 'хеширование',
    'fileinfo' => 'определение типов файлов',
    'iconv' => 'преобразование кодировок',
    'session' => 'сессии',
    'ctype' => 'проверка типов символов',
    'filter' => 'фильтрация данных',
    'tokenizer' => 'токенизация PHP-кода',
    'pcre' => 'регулярные выражения',
    'Zend OPcache' => 'OPcache',

    // Дополнительные расширения, используемые модулями Bitrix
    'bcmath' => 'точные вычисления',
    'bz2' => 'BZip2-архивы',
    'calendar' => 'календарные функции',
    'gettext' => 'локализация',
    'ldap' => 'интеграция AD/LDAP',
    'pgsql' => 'PostgreSQL',
    'pdo_pgsql' => 'PostgreSQL через PDO',
    'shmop' => 'разделяемая память',
    'sockets' => 'сокеты',
    'sysvmsg' => 'очереди System V',
    'sysvsem' => 'семафоры System V',
    'sysvshm' => 'разделяемая память System V',
    'xsl' => 'XSLT-преобразования',
    'amqp' => 'AMQP (сервер конвертации файлов)',
    'apcu' => 'APCu-кеш',
    'igbinary' => 'быстрая сериализация',
    'imagick' => 'ImageMagick',
    'memcached' => 'Memcached',
    'msgpack' => 'MessagePack',
    'redis' => 'Redis',
    'ssh2' => 'SSH2',
];

$requiredFunctions = [
    'imagecreate' => 'GD',
    'imagettftext' => 'FreeType в GD',
    'imagejpeg' => 'GD JPEG',
    'imagepng' => 'GD PNG',
    'imagewebp' => 'GD WebP',
    'mb_substr' => 'mbstring',
    'iconv' => 'iconv',
    'mysqli_connect' => 'mysqli',
    'xml_parser_create' => 'XML',
    'openssl_encrypt' => 'OpenSSL',
    'hash' => 'Hash',
    'json_encode' => 'JSON',
    'gzcompress' => 'Zlib',
    'preg_match' => 'PCRE',
    'curl_init' => 'cURL',
    'filter_var' => 'Filter',
    'session_start' => 'Session',
    'exif_read_data' => 'EXIF',
    'sodium_crypto_secretbox' => 'Sodium',
    'bcadd' => 'BCMath',
    'bzcompress' => 'BZip2',
    'cal_days_in_month' => 'Calendar',
    'gettext' => 'Gettext',
    'ldap_connect' => 'LDAP',
    'pg_connect' => 'PostgreSQL',
    'shmop_open' => 'SHMOP',
    'socket_create' => 'Sockets',
    'msg_get_queue' => 'SysV Message Queue',
    'sem_get' => 'SysV Semaphore',
    'shm_attach' => 'SysV Shared Memory',
    'apcu_fetch' => 'APCu',
    'igbinary_serialize' => 'Igbinary',
    'msgpack_pack' => 'MessagePack',
    'ssh2_connect' => 'SSH2',
];

$requiredClasses = [
    'DOMDocument' => 'DOM',
    'ZipArchive' => 'ZIP',
    'IntlDateFormatter' => 'Intl',
    'XSLTProcessor' => 'XSLT',
    'AMQPConnection' => 'AMQP',
    'Imagick' => 'Imagick',
    'Memcached' => 'Memcached',
    'Redis' => 'Redis',
];

$missing = [];

foreach ($requiredExtensions as $extension => $description) {
    if (!extension_loaded($extension)) {
        $missing[] = sprintf('extension %s (%s)', $extension, $description);
    }
}

foreach ($requiredFunctions as $function => $description) {
    if (!function_exists($function)) {
        $missing[] = sprintf('function %s() (%s)', $function, $description);
    }
}

foreach ($requiredClasses as $class => $description) {
    if (!class_exists($class)) {
        $missing[] = sprintf('class %s (%s)', $class, $description);
    }
}

$pdoDrivers = class_exists('PDO') ? PDO::getAvailableDrivers() : [];
foreach (['mysql' => 'MySQL', 'pgsql' => 'PostgreSQL'] as $driver => $description) {
    if (!in_array($driver, $pdoDrivers, true)) {
        $missing[] = sprintf('PDO driver %s (%s)', $driver, $description);
    }
}

echo 'PHP ' . PHP_VERSION . ' (' . PHP_SAPI . ')' . PHP_EOL;
echo 'Проверено расширений: ' . count($requiredExtensions) . PHP_EOL;

if ($missing !== []) {
    fwrite(STDERR, 'ОТСУТСТВУЮТ компоненты для 1С-Битрикс:' . PHP_EOL);
    foreach ($missing as $item) {
        fwrite(STDERR, ' - ' . $item . PHP_EOL);
    }
    exit(1);
}

echo 'Все необходимые для 1С-Битрикс расширения и функции установлены.' . PHP_EOL;
