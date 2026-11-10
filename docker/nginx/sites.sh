#!/bin/sh
# Управление сайтами Nginx через sites-available / sites-enabled.
#
# Использование:
#   ./docker/nginx/sites.sh              # связать все сайты из sites-available
#   ./docker/nginx/sites.sh sync         # то же самое
#   ./docker/nginx/sites.sh enable NAME  # включить один сайт
#   ./docker/nginx/sites.sh disable NAME # выключить один сайт
#   ./docker/nginx/sites.sh list         # показать сайты и их статус
set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
AVAILABLE_DIR="${SCRIPT_DIR}/sites-available"
ENABLED_DIR="${SCRIPT_DIR}/sites-enabled"

usage() {
    cat <<EOF
Использование: $(basename "$0") [команда] [имя-сайта]

Команды:
  sync              связать все файлы из sites-available (по умолчанию)
  enable NAME       включить сайт NAME
  disable NAME      выключить сайт NAME
  list              показать доступные сайты и их статус
EOF
}

require_available() {
    if [ ! -f "${AVAILABLE_DIR}/$1" ]; then
        echo "Сайт '$1' не найден в ${AVAILABLE_DIR}" >&2
        exit 1
    fi
}

link_site() {
    name=$1
    ln -sfn "../sites-available/${name}" "${ENABLED_DIR}/${name}"
}

sync_sites() {
    mkdir -p "${ENABLED_DIR}"

    # Удаляем старые ссылки, чтобы не оставалось выключенных сайтов
    for path in "${ENABLED_DIR}"/*; do
        [ -L "${path}" ] || [ -e "${path}" ] || continue
        rm -f "${path}"
    done

    for site in "${AVAILABLE_DIR}"/*; do
        [ -f "${site}" ] || continue
        name=$(basename "${site}")
        link_site "${name}"
        echo "enabled: ${name}"
    done
}

enable_site() {
    require_available "$1"
    mkdir -p "${ENABLED_DIR}"
    link_site "$1"
    echo "enabled: $1"
}

disable_site() {
    rm -f "${ENABLED_DIR}/$1"
    echo "disabled: $1"
}

list_sites() {
    for site in "${AVAILABLE_DIR}"/*; do
        [ -f "${site}" ] || continue
        name=$(basename "${site}")
        if [ -L "${ENABLED_DIR}/${name}" ]; then
            echo "enabled   ${name}"
        else
            echo "disabled  ${name}"
        fi
    done
}

case "${1:-sync}" in
    sync|link)
        sync_sites
        ;;
    enable)
        [ $# -ge 2 ] || { usage; exit 1; }
        enable_site "$2"
        ;;
    disable)
        [ $# -ge 2 ] || { usage; exit 1; }
        disable_site "$2"
        ;;
    list)
        list_sites
        ;;
    -h|--help|help)
        usage
        ;;
    *)
        usage
        exit 1
        ;;
esac
