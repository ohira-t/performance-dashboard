@extends('layouts.app')

@section('title', 'ユーザー追加')
@section('page-title', 'ユーザー追加')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus"></i> ユーザーの事前登録
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i>
                        <strong>事前登録について</strong><br>
                        メールアドレスと名前を入力してユーザーを事前登録します。登録されたユーザーのみがGoogleアカウントでログインできます。
                        役割は事前に割り当てることも、後から変更することもできます。
                    </div>

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">メールアドレス <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Google Workspaceのメールアドレスを入力してください（例: user@glug.co.jp）</small>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">名前 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">表示名を入力してください（ログイン時にGoogleアカウントの名前で自動更新されます）</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">役割</label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @foreach($roles as $role)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" id="role_{{ $role->id }}"
                                            {{ old('roles') && in_array($role->id, old('roles')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            <strong>{{ $role->display_name }}</strong>
                                            @if($role->description)
                                                <br><small class="text-muted">{{ $role->description }}</small>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">役割は後から変更することもできます</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> ユーザーを登録
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection























