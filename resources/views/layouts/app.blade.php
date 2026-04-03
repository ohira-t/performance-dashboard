<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GLUG業績管理')</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 193.89 193.89'><path fill='%231d1d1f' d='M182.42,51.23l-70.11,43.33c-1.1,.68-.62,2.38,.68,2.38h41.2c1.45,0,2.62,1.21,2.55,2.66-1.45,31.72-27.71,57.08-59.79,57.08-18.49,0-35.05-8.43-46.04-21.65-.8-.96-2.19-1.18-3.25-.52l-29.81,18.42c17.88,25.18,47.47,41.5,80.77,40.93,52.32-.89,94.8-43.72,95.27-96.04,.15-16.85-4.02-32.73-11.47-46.6Z'/><path fill='%23ffda01' d='M11.48,142.66l70.11-43.33c1.1-.68,.62-2.38-.68-2.38H39.71c-1.45,0-2.62-1.21-2.55-2.66,1.45-31.72,27.71-57.08,59.79-57.08,18.49,0,35.05,8.43,46.04,21.65,.8,.96,2.19,1.18,3.25,.52l29.81-18.42C158.16,15.77,128.57-.55,95.28,.01,42.95,.9,.47,43.73,0,96.06c-.15,16.85,4.02,32.73,11.47,46.6Z'/></svg>" type="image/svg+xml">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Choices.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    
    @stack('styles')
    
    <style>
        :root {
            --primary-color: #1d1d1f;
            --secondary-color: #86868b;
            --accent-color: #0071E3;
            --accent-hover: #0051d5;
            --bg-color: #f5f5f7;
            --card-bg: #ffffff;
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --border-color: #d2d2d7;
            --success-color: #24C761;
            --warning-color: #FF9F0A;
            --info-color: #0071E3;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        body {
            background-color: #f5f5f7;
            color: #1d1d1f;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Hiragino Sans", "Hiragino Kaku Gothic ProN", "Noto Sans JP", Meiryo, sans-serif;
            line-height: 1.5;
            font-size: 14px;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            letter-spacing: -0.01em;
        }
        
        /* ナビゲーションバー */
        .navbar {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.05);
            padding: 0.75rem 0;
            border-bottom: 0.5px solid rgba(0, 0, 0, 0.08);
        }
        
        .navbar .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
            padding-left: 3rem;
            padding-right: 3rem;
        }
        
        @media (max-width: 1200px) {
            .navbar .container-fluid {
                padding-left: 2.5rem;
                padding-right: 2.5rem;
            }
        }
        
        @media (max-width: 992px) {
            .navbar .container-fluid {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .navbar .container-fluid {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .navbar .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
        
        .navbar-brand {
            font-weight: 500;
            font-size: 1.125rem;
            color: var(--text-primary) !important;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .navbar-brand i {
            font-size: 1.25rem;
        }
        
        .navbar-brand:hover {
            color: var(--accent-color) !important;
        }
        
        .nav-link {
            color: var(--text-secondary) !important;
            font-weight: 400;
            font-size: 0.875rem;
            padding: 0.5rem 1rem !important;
            border-radius: 0.375rem;
            margin: 0 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            transition: all 0.2s ease;
        }
        
        .nav-link i {
            font-size: 1rem;
        }
        
        .nav-link:hover {
            color: var(--text-primary) !important;
            background-color: rgba(0, 0, 0, 0.04);
        }
        
        .nav-link.active {
            color: var(--accent-color) !important;
            background-color: rgba(0, 122, 255, 0.08);
            font-weight: 500;
        }
        
        /* カード */
        .card {
            background-color: var(--card-bg);
            border: 0.5px solid var(--border-color);
            border-radius: 0.25rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }
        
        .card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .card-header {
            background: rgba(248, 248, 248, 0.8);
            color: var(--text-primary);
            border-bottom: 0.5px solid var(--border-color);
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-header i {
            font-size: 1.125rem;
        }
        
        .card-header.text-white {
            background: linear-gradient(135deg, #0071E3 0%, #0051d5 100%);
            color: #ffffff;
            border-bottom: 0.5px solid rgba(255, 255, 255, 0.1);
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .card-body h5 {
            font-size: 1.125rem;
            font-weight: 500;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        /* アラート */
        .alert {
            border-radius: 0.375rem;
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .alert-success {
            background-color: #f0fff4;
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .alert-danger {
            background-color: #fff5f5;
            color: #c53030;
            border-left: 4px solid #c53030;
        }
        
        .alert-warning {
            background-color: #fffaf0;
            color: var(--warning-color);
            border-left: 4px solid var(--warning-color);
        }
        
        .alert-info {
            background-color: #ebf8ff;
            color: var(--info-color);
            border-left: 4px solid var(--info-color);
        }
        
        /* テーブル */
        .table {
            color: var(--text-primary);
            font-size: 0.875rem;
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #fafafa;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.8125rem;
            text-transform: none;
            letter-spacing: -0.01em;
            border-bottom: 0.5px solid var(--border-color);
            padding: 0.75rem 1rem;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            font-size: 0.875rem;
            font-weight: 400;
        }
        
        .table tbody tr {
            border-bottom: 0.5px solid var(--border-color);
            transition: background-color 0.15s ease;
        }
        
        .table tbody tr:hover {
            background-color: #fafafa;
        }
        
        .table-responsive {
            border-radius: 0.375rem;
            border: 0.5px solid var(--border-color);
        }
        
        /* バッジ */
        .badge {
            padding: 0.25rem 0.625rem;
            font-weight: 400;
            font-size: 0.75rem;
            border-radius: 0.25rem;
            letter-spacing: -0.01em;
        }
        
        .badge.bg-primary {
            background-color: rgba(0, 122, 255, 0.1) !important;
            color: var(--accent-color) !important;
        }
        
        .badge.bg-secondary {
            background-color: rgba(134, 134, 139, 0.1) !important;
            color: var(--secondary-color) !important;
        }
        
        .badge.bg-warning {
            background-color: rgba(255, 149, 0, 0.1) !important;
            color: var(--warning-color) !important;
        }
        
        .badge.bg-info {
            background-color: rgba(0, 122, 255, 0.1) !important;
            color: var(--info-color) !important;
        }
        
        .badge.bg-light {
            background-color: #f5f5f7 !important;
            color: var(--text-primary) !important;
            font-weight: 400;
        }
        
        /* ボタン */
        .btn {
            font-weight: 400;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border: 0.5px solid transparent;
        }
        
        .btn i {
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            color: #ffffff;
            border-color: var(--accent-color);
        }
        
        .btn-primary:hover {
            background-color: var(--accent-hover);
            border-color: var(--accent-hover);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            color: #ffffff;
        }
        
        .btn-success:hover {
            background-color: #30b350;
            border-color: #30b350;
        }
        
        .btn-outline-secondary {
            border: 0.5px solid var(--border-color);
            color: var(--text-secondary);
            background-color: transparent;
        }
        
        .btn-outline-secondary:hover {
            background-color: #fafafa;
            border-color: var(--secondary-color);
            color: var(--text-primary);
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }
        
        /* 入力グリッド */
        .input-grid {
            font-size: 0.875rem;
        }
        
        .input-grid th {
            position: sticky;
            top: 0;
            background-color: #fafafa;
            z-index: 10;
            font-weight: 500;
        }
        
        .input-grid th:first-child {
            position: sticky;
            left: 0;
            z-index: 11;
            background-color: #fafafa;
        }
        
        .input-grid td:first-child {
            position: sticky;
            left: 0;
            z-index: 10;
            background-color: var(--card-bg);
        }
        
        .input-grid tbody tr:hover {
            background-color: #fafafa;
        }
        
        .file-upload-btn {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .value-input, .comment-input {
            border: 1px solid var(--border-color);
            border-radius: 0.25rem;
            padding: 0.5rem;
        }
        
        .value-input:focus, .comment-input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
            outline: none;
        }
        
        .value-input {
            width: 120px;
            font-size: 0.875rem;
        }
        
        .comment-input {
            width: 200px;
            font-size: 0.875rem;
        }
        
        /* フォームコントロール */
        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            color: var(--text-primary);
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
        }
        
        /* プレースホルダー */
        .form-control::placeholder,
        .form-select::placeholder,
        input::placeholder,
        textarea::placeholder {
            color: #c7c7cc !important;
            opacity: 1 !important;
        }
        
        /* ページネーション */
        .pagination {
            margin-bottom: 0;
            gap: 0.25rem;
        }
        
        .pagination .page-link {
            color: var(--text-primary);
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            min-width: 38px;
            text-align: center;
            transition: all 0.2s ease;
        }
        
        .pagination .page-link:hover {
            background-color: var(--bg-color);
            border-color: var(--accent-color);
            color: var(--accent-color);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: #ffffff;
            font-weight: 500;
        }
        
        .pagination .page-item.disabled .page-link {
            color: var(--text-secondary);
            background-color: #ffffff;
            border-color: var(--border-color);
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            border-radius: 0.375rem;
        }
        
        /* テキスト */
        h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        
        h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            line-height: 1.3;
        }
        
        h3 {
            font-size: 1.25rem;
            font-weight: 500;
            color: var(--text-primary);
            letter-spacing: -0.01em;
        }
        
        h4 {
            font-size: 1.125rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        h5 {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        h6 {
            font-size: 0.9375rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .text-muted {
            color: var(--text-secondary) !important;
            font-size: 0.8125rem;
        }
        
        /* サイドバー */
        .sidebar-wrapper {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
        }
        
        .sidebar {
            width: 260px;
            height: 100%;
            background-color: #ffffff;
            border-right: 0.5px solid var(--border-color);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, width 0.3s ease;
            overflow: hidden;
        }
        
        .sidebar.collapsed {
            width: 64px;
            overflow: visible;
        }
        
        .sidebar-header {
            padding: 1rem;
            border-bottom: 0.5px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 64px;
            position: relative;
            z-index: 10;
        }
        
        .sidebar.collapsed .sidebar-header {
            overflow: visible;
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            flex: 1;
        }
        
        .sidebar-brand:hover {
            color: var(--accent-color);
        }
        
        .sidebar-brand i {
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        
        .sidebar-brand-logo {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }
        
        .sidebar-brand-text {
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .sidebar-brand-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .sidebar-toggle-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
            position: relative;
            z-index: 1001;
        }
        
        .sidebar-toggle-btn:hover {
            background-color: rgba(0, 0, 0, 0.04);
            color: var(--text-primary);
        }
        
        .sidebar.collapsed .sidebar-toggle-btn {
            position: absolute;
            right: -32px;
            top: 1rem;
            background-color: #ffffff;
            border: 0.5px solid var(--border-color);
            border-left: none;
            border-radius: 0 0.25rem 0.25rem 0;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
            width: 32px;
            height: 32px;
            z-index: 1002;
        }
        
        .sidebar.collapsed .sidebar-toggle-btn:hover {
            background-color: #fafafa;
        }
        
        .sidebar.collapsed .sidebar-toggle-btn i {
            transform: rotate(180deg);
        }
        
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.5rem 0;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu-item {
            margin: 0.125rem 0.5rem;
        }
        
        .sidebar-menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 400;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        
        .sidebar-menu-link:hover {
            background-color: rgba(0, 0, 0, 0.04);
            color: var(--text-primary);
        }
        
        .sidebar-menu-link.active {
            background-color: rgba(0, 122, 255, 0.1);
            color: var(--accent-color);
            font-weight: 500;
        }
        
        .sidebar-menu-link i {
            font-size: 1.125rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .sidebar-menu-link .sidebar-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        .sidebar-menu-link.active .sidebar-icon {
            stroke: var(--accent-color);
        }
        
        .sidebar-menu-text {
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .sidebar-menu-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .sidebar.collapsed .sidebar-menu-link {
            position: relative;
        }
        
        .sidebar.collapsed .sidebar-menu-link[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 8px);
            top: 50%;
            transform: translateY(-50%);
            background: var(--text-primary);
            color: #fff;
            padding: 0.25rem 0.625rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 1100;
            pointer-events: none;
        }
        
        .sidebar-menu-label {
            display: block;
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .sidebar-menu-label {
            opacity: 0;
            height: 0;
            padding: 0;
            overflow: hidden;
        }
        
        .sidebar-menu-divider {
            height: 1px;
            background-color: var(--border-color);
            margin: 0.75rem 1rem;
            transition: opacity 0.3s ease;
        }
        
        .sidebar.collapsed .sidebar-menu-divider {
            opacity: 0;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .sidebar-overlay.show {
            display: block;
        }
        
        /* メインコンテンツエリア */
        .main-content-wrapper {
            margin-left: 260px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-wrapper.collapsed ~ .main-content-wrapper {
            margin-left: 64px;
        }
        
        .top-navbar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.05);
            padding: 0.75rem 1.5rem;
            border-bottom: 0.5px solid rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
            min-height: 64px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .sidebar-toggle-btn-mobile {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            font-size: 1.25rem;
            transition: all 0.2s ease;
        }
        
        .sidebar-toggle-btn-mobile:hover {
            background-color: rgba(0, 0, 0, 0.04);
        }
        
        .top-navbar-title {
            font-size: 1.125rem;
            font-weight: 500;
            color: var(--text-primary);
            flex: 1;
        }
        
        .top-navbar-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .top-navbar a.text-center:hover {
            color: var(--accent-color) !important;
        }
        
        /* クイックメニューリンクのスタイル */
        .quick-menu-link {
            color: var(--text-secondary) !important;
            transition: color 0.2s ease;
        }
        
        .quick-menu-link:hover {
            color: var(--accent-color) !important;
        }
        
        .quick-menu-link.active {
            color: var(--accent-color) !important;
        }
        
        /* メインコンテナ */
        main {
            flex: 1;
            padding: 1.5rem 2em;
            width: 100%;
            box-sizing: border-box;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar.collapsed {
                width: 260px;
            }
            
            .main-content-wrapper {
                margin-left: 0;
            }
            
            .sidebar-toggle-btn-mobile {
                display: block;
            }
            
            .sidebar-toggle-btn {
                display: none;
            }
            
            main {
                padding: 1.5rem 2em;
            }
        }
        
        @media (max-width: 768px) {
            main {
                padding: 1rem 2em;
            }
            
            h1 { font-size: 1.5rem; }
            h2 { font-size: 1.25rem; }
            h3 { font-size: 1.125rem; }
            h4 { font-size: 1rem; }
            
            .card-header {
                padding: 0.875rem 1rem;
                font-size: 0.875rem;
            }
            
            .card-body {
                padding: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            main {
                padding: 1rem 1em;
            }
        }
    </style>
</head>
<body>
    <!-- サイドバー -->
    <div class="sidebar-wrapper">
        <aside id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="sidebar-brand">
                    <svg class="sidebar-brand-logo" viewBox="0 0 193.89 193.89">
                        <g>
                            <path fill="currentColor" d="M182.42,51.23l-70.11,43.33c-1.1,.68-.62,2.38,.68,2.38h41.2c1.45,0,2.62,1.21,2.55,2.66-1.45,31.72-27.71,57.08-59.79,57.08-18.49,0-35.05-8.43-46.04-21.65-.8-.96-2.19-1.18-3.25-.52l-29.81,18.42c17.88,25.18,47.47,41.5,80.77,40.93,52.32-.89,94.8-43.72,95.27-96.04,.15-16.85-4.02-32.73-11.47-46.6Z"/>
                            <path fill="#ffda01" d="M11.48,142.66l70.11-43.33c1.1-.68,.62-2.38-.68-2.38H39.71c-1.45,0-2.62-1.21-2.55-2.66,1.45-31.72,27.71-57.08,59.79-57.08,18.49,0,35.05,8.43,46.04,21.65,.8,.96,2.19,1.18,3.25,.52l29.81-18.42C158.16,15.77,128.57-.55,95.28,.01,42.95,.9,.47,43.73,0,96.06c-.15,16.85,4.02,32.73,11.47,46.6Z"/>
                        </g>
                    </svg>
                    <span class="sidebar-brand-text">GLUG業績ダッシュボード</span>
                </a>
                <button class="sidebar-toggle-btn" id="sidebarToggle" type="button">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    @auth
                        @if(auth()->user()->hasPermission('dashboard.view'))
                        <li class="sidebar-menu-item">
                            <a href="{{ route('dashboard') }}" class="sidebar-menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-tooltip="ダッシュボード">
                                <svg class="sidebar-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="10" width="4" height="8" rx="1"/>
                                    <path d="M9 14l4-6 4 2"/>
                                    <path d="M17 10V6h-4"/>
                                </svg>
                                <span class="sidebar-menu-text">ダッシュボード</span>
                            </a>
                        </li>
                        @endif
                        
                        @if(auth()->user()->hasPermission('monthly_results.view'))
                        <li class="sidebar-menu-item">
                            <a href="{{ route('monthly-results.index') }}" class="sidebar-menu-link {{ request()->routeIs('monthly-results.*') ? 'active' : '' }}" data-tooltip="月次実績入力">
                                <svg class="sidebar-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.5 3.5l2 2L7 15H5v-2L14.5 3.5z"/>
                                    <path d="M12 6l2 2"/>
                                    <path d="M3 18h14"/>
                                </svg>
                                <span class="sidebar-menu-text">月次実績入力</span>
                            </a>
                        </li>
                        @endif
                    @endauth
                    <li class="sidebar-menu-divider"></li>
                    <li class="sidebar-menu-item">
                        <span class="sidebar-menu-label">マスタ管理</span>
                    </li>
                    @if(auth()->user()->hasPermission('master.categories.view'))
                    <li class="sidebar-menu-item">
                        <a href="{{ route('admin.categories.index') }}" class="sidebar-menu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" data-tooltip="カテゴリー管理">
                            <svg class="sidebar-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                <path d="M7 11h6"/>
                                <path d="M7 15h4"/>
                            </svg>
                            <span class="sidebar-menu-text">カテゴリー管理</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasPermission('master.metrics.view'))
                    <li class="sidebar-menu-item">
                        <a href="{{ route('admin.metrics.index') }}" class="sidebar-menu-link {{ request()->routeIs('admin.metrics.*') ? 'active' : '' }}" data-tooltip="指標マスタ管理">
                            <svg class="sidebar-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="6" cy="10" r="3"/>
                                <path d="M6 10v.01"/>
                                <path d="M11 7h6"/>
                                <path d="M11 10h6"/>
                                <path d="M11 13h4"/>
                            </svg>
                            <span class="sidebar-menu-text">指標マスタ管理</span>
                        </a>
                    </li>
                    @endif
                    @if(auth()->user()->hasPermission('users.view'))
                    <li class="sidebar-menu-item">
                        <a href="{{ route('admin.users.index') }}" class="sidebar-menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" data-tooltip="ユーザー管理">
                            <svg class="sidebar-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="6" cy="7" r="3"/>
                                <path d="M1 17c0-2.8 2.2-5 5-5s5 2.2 5 5"/>
                                <circle cx="14" cy="7" r="3"/>
                                <path d="M9 17c0-2.8 2.2-5 5-5s5 2.2 5 5"/>
                            </svg>
                            <span class="sidebar-menu-text">ユーザー管理</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('admin.activity-logs.index') }}" class="sidebar-menu-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}" data-tooltip="操作ログ">
                            <svg class="sidebar-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 4h14"/>
                                <path d="M3 8h10"/>
                                <path d="M3 12h14"/>
                                <path d="M3 16h8"/>
                            </svg>
                            <span class="sidebar-menu-text">操作ログ</span>
                        </a>
                    </li>
                    @endif
                    <li class="sidebar-menu-divider"></li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('data-export.index') }}" class="sidebar-menu-link {{ request()->routeIs('data-export.*') ? 'active' : '' }}" data-tooltip="データエクスポート">
                            <svg class="sidebar-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M13 2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7"/>
                                <path d="M13 2l5 5v3"/>
                                <path d="M13 7V2"/>
                                <path d="M8 11h4"/>
                                <path d="M16 10l3 3-3 3"/>
                                <path d="M11 13h8"/>
                            </svg>
                            <span class="sidebar-menu-text">データエクスポート</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
    </div>

    <!-- メインコンテンツエリア -->
    <div class="main-content-wrapper">
        <header class="top-navbar">
            <button class="sidebar-toggle-btn-mobile" id="sidebarToggleMobile" type="button">
                <i class="bi bi-list"></i>
            </button>
            <div class="top-navbar-title">
                @yield('page-title', 'GLUG業績管理')
            </div>
            
            <div class="top-navbar-actions">
                @auth
                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none p-0" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem;">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="rounded-circle" style="width: 32px; height: 32px;">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background-color: var(--accent-color); color: white; font-size: 0.875rem;">
                                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                            <span style="font-size: 0.875rem;">{{ auth()->user()->name }}</span>
                            <i class="bi bi-chevron-down" style="font-size: 0.75rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuButton" style="min-width: 200px; margin-top: 0.5rem;">
                            <li>
                                <div class="dropdown-item-text">
                                    <div style="font-size: 0.875rem; font-weight: 500;">{{ auth()->user()->name }}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ auth()->user()->email }}</div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item" style="font-size: 0.875rem;">
                                        <i class="bi bi-box-arrow-right"></i> ログアウト
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">ログイン</a>
                @endauth
            </div>
        </header>

        <main>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
        </main>
    </div>

    <!-- ローディングオーバーレイ -->
    <div id="loadingOverlay" style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(2px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    ">
        <div style="text-align: center;">
            <div style="width: 32px; height: 32px; border: 3px solid var(--border-color); border-top-color: var(--accent-color); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 0.75rem;"></div>
            <div style="font-size: 0.8125rem; color: var(--text-secondary);">読み込み中...</div>
        </div>
    </div>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <!-- 一番上に戻るボタン -->
    <button id="scrollToTopBtn" type="button" title="一番上に戻る" style="
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: var(--accent-color);
        color: #ffffff;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 122, 255, 0.3);
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        transition: all 0.3s ease;
    ">
        <i class="bi bi-chevron-up" style="font-size: 1.25rem;"></i>
    </button>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js はダッシュボード等で個別に読み込み -->
    <!-- Choices.js -->
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <!-- Axios (AJAX用) -->
    <script src="https://cdn.jsdelivr.net/npm/axios@1.7.0/dist/axios.min.js"></script>
    
    <script>
        // CSRFトークンをAxiosのデフォルトヘッダーに設定
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // ページ遷移を伴うフォーム送信・selectのonchangeでローディング表示
        (function() {
            const overlay = document.getElementById('loadingOverlay');
            document.querySelectorAll('select[onchange*="submit"]').forEach(function(sel) {
                sel.addEventListener('change', function() {
                    if (overlay) overlay.style.display = 'flex';
                });
            });
            document.querySelectorAll('form[method="GET"]').forEach(function(form) {
                form.addEventListener('submit', function() {
                    if (overlay) overlay.style.display = 'flex';
                });
            });
        })();
        
        // サイドバーの開閉制御
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarToggleMobile = document.getElementById('sidebarToggleMobile');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const sidebarWrapper = document.querySelector('.sidebar-wrapper');
            
            // デスクトップ: サイドバーの折りたたみ/展開
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    sidebarWrapper.classList.toggle('collapsed');
                    // 状態をlocalStorageに保存
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
            }
            
            // モバイル: サイドバーの表示/非表示
            if (sidebarToggleMobile) {
                sidebarToggleMobile.addEventListener('click', function() {
                    sidebar.classList.add('show');
                    sidebarOverlay.classList.add('show');
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }
            
            // 保存された状態を復元
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed && window.innerWidth > 992) {
                sidebar.classList.add('collapsed');
                sidebarWrapper.classList.add('collapsed');
            }
            
            // 一番上に戻るボタン
            const scrollToTopBtn = document.getElementById('scrollToTopBtn');
            if (scrollToTopBtn) {
                // スクロール位置に応じてボタンの表示/非表示を切り替え
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 300) {
                        scrollToTopBtn.style.display = 'flex';
                    } else {
                        scrollToTopBtn.style.display = 'none';
                    }
                });
                
                // ボタンクリックで一番上にスクロール
                scrollToTopBtn.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
                
                // ホバーエフェクト
                scrollToTopBtn.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1)';
                    this.style.boxShadow = '0 4px 12px rgba(0, 122, 255, 0.4)';
                });
                
                scrollToTopBtn.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.boxShadow = '0 2px 8px rgba(0, 122, 255, 0.3)';
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>

