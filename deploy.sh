#!/bin/bash

# ========================================
# Performance Dashboard デプロイスクリプト
# ========================================

set -e

echo "デプロイを開始します..."

# 1. 最新コードを取得
echo "[1/6] 最新コードを取得中..."
git pull origin main

# 2. Composerの依存関係をインストール
echo "[2/6] Composerパッケージをインストール中..."
composer install --optimize-autoloader --no-dev

# 3. マイグレーションを実行
echo "[3/6] データベースマイグレーションを実行中..."
php artisan migrate --force

# 4. キャッシュをクリアして再生成
echo "[4/6] キャッシュをクリア・最適化中..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. ストレージリンクを確認
echo "[5/6] ストレージリンクを確認中..."
php artisan storage:link 2>/dev/null || true

# 6. パーミッションを設定
echo "[6/6] パーミッションを設定中..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo ""
echo "デプロイが完了しました"
echo "  動作確認: ブラウザでサイトにアクセス"
echo "  ログ確認: tail -f storage/logs/laravel.log"
