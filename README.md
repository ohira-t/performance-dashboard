# Performance Dashboard（業績管理ダッシュボード）

会社の業績データを記録・可視化するためのWebアプリケーションです。  
月次実績の入力、KPIダッシュボード、CSV入出力、データエクスポートなどを提供します。

## 主な機能

- **ダッシュボード** — KPIカード、期間サマリー、セグメント別テーブル、前月比・前期比の自動算出
- **月次実績管理** — 月別データの入力・編集、証跡ファイルのアップロード、チャート表示
- **CSV入出力** — 簡易形式・詳細形式でのCSVダウンロード、CSVインポート
- **データエクスポート** — カテゴリ、指標、年度、月次実績、ユーザー情報の一括エクスポート
- **管理画面** — ユーザー管理（ロール・権限）、カテゴリ・指標マスタ管理、アクティビティログ閲覧
- **Google OAuth認証** — Google Workspaceドメインによるアクセス制限対応

## 技術スタック

| 区分 | 技術 |
|------|------|
| バックエンド | PHP 8.3+ / Laravel 13 |
| フロントエンド | Vite / Tailwind CSS v4 / Axios / Chart.js |
| データベース | MySQL（開発時はSQLiteも可） |
| 認証 | Google OAuth（Laravel Socialite） |
| ログ | spatie/laravel-activitylog |

## セットアップ

### 必要な環境

- PHP 8.3以上
- Composer
- Node.js / npm
- MySQL（または SQLite）

### インストール手順

```bash
# リポジトリをクローン
git clone https://github.com/ohira-t/performance-dashboard.git
cd performance-dashboard

# 依存パッケージのインストール
composer install
npm install

# 環境設定ファイルを作成
cp .env.example .env
php artisan key:generate
```

### データベース設定

`.env` にデータベース接続情報を設定します。

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=performance_dashboard
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# マイグレーション & シーダー実行
php artisan migrate
php artisan db:seed
```

### Google OAuth設定（任意）

Google Cloud Consoleで OAuth 2.0 クライアントIDを作成し、`.env` に追加します。

```dotenv
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://your-domain/auth/google/callback
GOOGLE_HOSTED_DOMAIN=your-workspace-domain.com
```

ローカル開発では `/dev-login` にアクセスすることで、Google認証なしでログインできます。

### 起動

```bash
# 開発サーバー起動（サーバー + Vite + キュー + ログ同時起動）
composer dev

# または個別に起動
php artisan serve
npm run dev
```

http://localhost:8000 にアクセスしてください。

## ディレクトリ構成（主要部分）

```
app/
├── Helpers/           # DashboardHelper, StorageHelper, SecurityHelper
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php      # ダッシュボード
│   │   ├── MonthlyResultController.php  # 月次実績
│   │   ├── DataExportController.php     # データエクスポート
│   │   └── Admin/                       # 管理画面
│   └── Middleware/
│       └── CheckPermission.php          # 権限チェック
├── Models/
│   ├── Category.php        # カテゴリ
│   ├── Metric.php          # 指標
│   ├── FiscalYear.php      # 年度
│   ├── MonthlyResult.php   # 月次実績
│   ├── EvidenceDetail.php  # 証跡
│   ├── User.php            # ユーザー
│   ├── Role.php            # ロール
│   └── Permission.php      # 権限
database/
├── migrations/        # テーブル定義
└── seeders/           # 初期データ（ロール・権限、年度マスタ、管理者ユーザー）
resources/views/
├── dashboard.blade.php           # ダッシュボード画面
├── monthly-results/index.blade.php  # 月次実績画面
├── admin/                        # 管理画面
└── layouts/app.blade.php         # 共通レイアウト
```

## シーダー

| シーダー | 内容 |
|----------|------|
| RolePermissionSeeder | ロール・権限の初期定義 |
| AdminUserSeeder | 管理者ユーザーの作成（`ADMIN_EMAIL` 環境変数を使用） |
| FiscalYearSeeder | 年度マスタの初期投入 |

管理者ユーザーを作成する場合は、`.env` に `ADMIN_EMAIL` を設定してから `db:seed` を実行してください。

## ライセンス

MIT License
