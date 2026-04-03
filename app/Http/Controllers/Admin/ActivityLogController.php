<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * 操作ログ一覧を表示
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer'])
            ->latest();

        // フィルター: ユーザー
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        // フィルター: 操作種別
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // フィルター: 期間（開始）
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // フィルター: 期間（終了）
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // フィルター: キーワード検索
        if ($request->filled('keyword')) {
            $query->where('description', 'like', '%' . $request->keyword . '%');
        }

        $activities = $query->paginate(50)->withQueryString();

        // ユーザー一覧（フィルター用）
        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.activity-logs.index', compact('activities', 'users'));
    }
}




















