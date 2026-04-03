<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ログイン - GLUG業績管理</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 193.89 193.89'><path fill='%231d1d1f' d='M182.42,51.23l-70.11,43.33c-1.1,.68-.62,2.38,.68,2.38h41.2c1.45,0,2.62,1.21,2.55,2.66-1.45,31.72-27.71,57.08-59.79,57.08-18.49,0-35.05-8.43-46.04-21.65-.8-.96-2.19-1.18-3.25-.52l-29.81,18.42c17.88,25.18,47.47,41.5,80.77,40.93,52.32-.89,94.8-43.72,95.27-96.04,.15-16.85-4.02-32.73-11.47-46.6Z'/><path fill='%23ffda01' d='M11.48,142.66l70.11-43.33c1.1-.68,.62-2.38-.68-2.38H39.71c-1.45,0-2.62-1.21-2.55-2.66,1.45-31.72,27.71-57.08,59.79-57.08,18.49,0,35.05,8.43,46.04,21.65,.8,.96,2.19,1.18,3.25,.52l29.81-18.42C158.16,15.77,128.57-.55,95.28,.01,42.95,.9,.47,43.73,0,96.06c-.15,16.85,4.02,32.73,11.47,46.6Z'/></svg>" type="image/svg+xml">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
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
        
        .card {
            background-color: var(--card-bg);
            border: 0.5px solid var(--border-color);
            border-radius: 0.375rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
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
        
        .alert {
            border-radius: 0.375rem;
            border: none;
        }
        
        .alert-danger {
            background-color: #fff5f5;
            color: #c53030;
            border-left: 4px solid #c53030;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body text-center" style="padding: 3rem 2rem;">
                    <div class="mb-4">
                        <svg viewBox="0 0 193.89 193.89" style="width: 64px; height: 64px;">
                            <path fill="#1d1d1f" d="M182.42,51.23l-70.11,43.33c-1.1,.68-.62,2.38,.68,2.38h41.2c1.45,0,2.62,1.21,2.55,2.66-1.45,31.72-27.71,57.08-59.79,57.08-18.49,0-35.05-8.43-46.04-21.65-.8-.96-2.19-1.18-3.25-.52l-29.81,18.42c17.88,25.18,47.47,41.5,80.77,40.93,52.32-.89,94.8-43.72,95.27-96.04,.15-16.85-4.02-32.73-11.47-46.6Z"/>
                            <path fill="#ffda01" d="M11.48,142.66l70.11-43.33c1.1-.68,.62-2.38-.68-2.38H39.71c-1.45,0-2.62-1.21-2.55-2.66,1.45-31.72,27.71-57.08,59.79-57.08,18.49,0,35.05,8.43,46.04,21.65,.8,.96,2.19,1.18,3.25,.52l29.81-18.42C158.16,15.77,128.57-.55,95.28,.01,42.95,.9,.47,43.73,0,96.06c-.15,16.85,4.02,32.73,11.47,46.6Z"/>
                        </svg>
                    </div>
                    <h2 class="mb-3" style="font-size: 1.5rem; font-weight: 500;">GLUG業績管理</h2>
                    <p class="text-muted mb-4" style="font-size: 0.875rem;">Googleアカウントでログインしてください</p>
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    <a href="{{ route('auth.google') }}" class="btn btn-primary w-100" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Googleでログイン
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

