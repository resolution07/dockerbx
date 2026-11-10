#!/bin/sh
set -eu

XDEBUG_INI_DIR="/usr/local/etc/php/conf.d-xdebug"
XDEBUG_INI="${XDEBUG_INI_DIR}/xdebug.ini"

is_true() {
    case "$(printf '%s' "${1:-}" | tr '[:upper:]' '[:lower:]')" in
        1|true|yes|on) return 0 ;;
        *) return 1 ;;
    esac
}

XDEBUG_ENABLED="${XDEBUG_ENABLED:-false}"
XDEBUG_MODE="${XDEBUG_MODE:-debug,develop}"

mkdir -p "${XDEBUG_INI_DIR}"

if is_true "${XDEBUG_ENABLED}"; then
    cat > "${XDEBUG_INI}" <<EOF
; Сгенерировано docker-entrypoint.sh
zend_extension=xdebug.so
xdebug.mode=${XDEBUG_MODE}
xdebug.output_dir=/var/www/html
xdebug.profiler_output_name=cachegrind.out.%p
xdebug.discover_client_host=1
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
xdebug.start_with_request=yes
xdebug.idekey="${XDEBUG_IDEKEY:-PHPSTORM}"
EOF
    echo "[docker-entrypoint] Xdebug enabled (mode=${XDEBUG_MODE})"
else
    rm -f "${XDEBUG_INI}"
    echo "[docker-entrypoint] Xdebug disabled"
fi

exec docker-php-entrypoint "$@"
