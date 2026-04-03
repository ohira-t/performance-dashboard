<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonthlyResultController;
use App\Http\Controllers\DataExportController;
use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');

if (app()->environment('local')) {
    Route::get('/dev-login', function () {
        $user = \App\Models\User::first();
        if ($user) {
            auth()->login($user);
            return redirect('/');
        }
        return 'ユーザーが見つかりません';
    });
}

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:dashboard.view');

    Route::prefix('monthly-results')->name('monthly-results.')->group(function () {
        Route::get('/', [MonthlyResultController::class, 'index'])
            ->name('index')
            ->middleware('permission:monthly_results.view');

        Route::post('/update', [MonthlyResultController::class, 'update'])
            ->name('update')
            ->middleware('permission:monthly_results.update');

        Route::post('/find-or-create', [MonthlyResultController::class, 'findOrCreate'])
            ->name('find-or-create')
            ->middleware('permission:monthly_results.update');

        Route::post('/upload-evidence', [MonthlyResultController::class, 'uploadEvidence'])
            ->name('upload-evidence')
            ->middleware('permission:monthly_results.update');

        Route::get('/evidence/{id}/download', [MonthlyResultController::class, 'downloadEvidence'])
            ->name('evidence.download')
            ->middleware('permission:monthly_results.view');

        Route::get('/{id}/details', [MonthlyResultController::class, 'getDetails'])
            ->name('details.get')
            ->middleware('permission:monthly_results.view');

        Route::post('/{id}/details', [MonthlyResultController::class, 'saveDetails'])
            ->name('details.save')
            ->middleware('permission:monthly_results.update');

        Route::get('/metric/{metricId}/chart-data', [MonthlyResultController::class, 'getMetricChartData'])
            ->name('metric.chart-data')
            ->middleware('permission:monthly_results.view');

        Route::get('/csv/download-simple', [MonthlyResultController::class, 'downloadCsvSimple'])
            ->name('csv.download-simple')
            ->middleware('permission:monthly_results.export');

        Route::get('/csv/download-detail', [MonthlyResultController::class, 'downloadCsvDetail'])
            ->name('csv.download-detail')
            ->middleware('permission:monthly_results.export');

        Route::post('/csv/import', [MonthlyResultController::class, 'importCsv'])
            ->name('csv.import')
            ->middleware('permission:monthly_results.update');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])
            ->name('activity-logs.index')
            ->middleware('permission:users.view');

        Route::prefix('users')->name('users.')->middleware('permission:users.view')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('create')->middleware('permission:users.create');
            Route::post('/', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('store')->middleware('permission:users.create');
            Route::get('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
            Route::put('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('update')->middleware('permission:users.update');
            Route::put('/{user}/roles', [\App\Http\Controllers\Admin\UserController::class, 'updateRoles'])->name('roles.update')->middleware('permission:users.manage_roles');
            Route::put('/{user}/permissions', [\App\Http\Controllers\Admin\UserController::class, 'updatePermissions'])->name('permissions.update')->middleware('permission:users.manage_roles');
            Route::delete('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('destroy')->middleware('permission:users.delete');
        });

        Route::middleware('permission:master.categories.view')->group(function () {
            Route::prefix('categories')->name('categories.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('create')->middleware('permission:master.categories.create');
                Route::post('/', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('store')->middleware('permission:master.categories.create');
                Route::get('/{category}/edit', [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('edit')->middleware('permission:master.categories.update');
                Route::put('/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('update')->middleware('permission:master.categories.update');
                Route::delete('/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('destroy')->middleware('permission:master.categories.delete');
            });
        });

        Route::middleware('permission:master.metrics.view')->group(function () {
            Route::prefix('metrics')->name('metrics.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\MetricController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\MetricController::class, 'create'])->name('create')->middleware('permission:master.metrics.create');
                Route::post('/', [\App\Http\Controllers\Admin\MetricController::class, 'store'])->name('store')->middleware('permission:master.metrics.create');
                Route::get('/{metric}/edit', [\App\Http\Controllers\Admin\MetricController::class, 'edit'])->name('edit')->middleware('permission:master.metrics.update');
                Route::put('/{metric}', [\App\Http\Controllers\Admin\MetricController::class, 'update'])->name('update')->middleware('permission:master.metrics.update');
                Route::delete('/{metric}', [\App\Http\Controllers\Admin\MetricController::class, 'destroy'])->name('destroy')->middleware('permission:master.metrics.delete');
            });
        });
    });

    Route::get('/storage/{path}', [StorageController::class, 'file'])
        ->where('path', '.*')
        ->name('storage.file');

    Route::prefix('data-export')->name('data-export.')->group(function () {
        Route::get('/', [DataExportController::class, 'index'])->name('index');
        Route::get('/all', [DataExportController::class, 'exportAll'])->name('all');
        Route::get('/categories', [DataExportController::class, 'exportCategories'])->name('categories');
        Route::get('/metrics', [DataExportController::class, 'exportMetrics'])->name('metrics');
        Route::get('/fiscal-years', [DataExportController::class, 'exportFiscalYears'])->name('fiscal-years');
        Route::get('/monthly-results', [DataExportController::class, 'exportMonthlyResults'])->name('monthly-results');
        Route::get('/users', [DataExportController::class, 'exportUsers'])->name('users');
    });
});
