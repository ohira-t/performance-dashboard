#!/bin/bash

# ========================================
# Performance Dashboard DBバックアップ
# ========================================
# crontab例: 0 3 * * * /path/to/backup-db.sh
#
# さくらインターネットの場合:
#   crontab -e
#   0 3 * * * cd /home/your-account/www/performance-dashboard && ./backup-db.sh

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
BACKUP_DIR="${SCRIPT_DIR}/storage/app/backups"
DAYS_TO_KEEP=30

# .envからDB設定を読み込み
if [ -f "${SCRIPT_DIR}/.env" ]; then
    export $(grep -E '^DB_(HOST|PORT|DATABASE|USERNAME|PASSWORD)=' "${SCRIPT_DIR}/.env" | xargs)
fi

if [ -z "$DB_DATABASE" ]; then
    echo "エラー: DB_DATABASE が設定されていません"
    exit 1
fi

mkdir -p "$BACKUP_DIR"

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
FILENAME="backup_${DB_DATABASE}_${TIMESTAMP}.sql.gz"

echo "バックアップ開始: ${DB_DATABASE}"

mysqldump \
    -h "${DB_HOST:-127.0.0.1}" \
    -P "${DB_PORT:-3306}" \
    -u "${DB_USERNAME}" \
    -p"${DB_PASSWORD}" \
    --single-transaction \
    --routines \
    --triggers \
    "${DB_DATABASE}" | gzip > "${BACKUP_DIR}/${FILENAME}"

echo "バックアップ完了: ${FILENAME} ($(du -h "${BACKUP_DIR}/${FILENAME}" | cut -f1))"

# 古いバックアップを削除
DELETED=$(find "$BACKUP_DIR" -name "backup_*.sql.gz" -mtime +${DAYS_TO_KEEP} -delete -print | wc -l)
if [ "$DELETED" -gt 0 ]; then
    echo "${DELETED}件の古いバックアップを削除しました（${DAYS_TO_KEEP}日以上前）"
fi
