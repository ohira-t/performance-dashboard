#!/bin/bash

# ========================================
# Performance Dashboard デプロイスクリプト
# ========================================
# さくらインターネット: bash deploy.sh
# ローカル開発環境:     bash deploy.sh --local

set -e

if [ "$1" = "--local" ]; then
    PHP=php
    COMPOSER=composer
else
    PHP=/usr/local/php/8.3/bin/php
    COMPOSER="$PHP $HOME/bin/composer"
fi

echo "デプロイを開始します..."

echo "[1/6] 最新コードを取得中..."
git pull origin main

echo "[2/6] Composerパッケージをインストール中..."
$COMPOSER install --optimize-autoloader --no-dev --no-interaction

echo "[3/6] データベースマイグレーションを実行中..."
$PHP artisan migrate --force

echo "[4/6] キャッシュをクリア・最適化中..."
$PHP artisan config:clear
$PHP artisan route:clear
$PHP artisan view:clear
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

echo "[5/6] ストレージリンクを確認中..."
$PHP artisan storage:link 2>/dev/null || true

echo "[6/6] パーミッションを設定中..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo ""
echo "デプロイが完了しました"
echo "  URL: https://glug-system.sakura.ne.jp/pd/index.php"
echo "  ログ: tail -f storage/logs/laravel.log"
