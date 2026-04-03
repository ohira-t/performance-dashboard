<tr>
    <td style="white-space: nowrap;">
        @if($level > 0)
            <span style="display: inline-block; width: {{ $level * 20 }}px;"></span>
            <i class="bi bi-arrow-return-right text-muted"></i>
        @endif
        <strong>{{ $category->name }}</strong>
    </td>
    <td style="white-space: nowrap; font-size: 0.875rem;">
        <span class="text-muted">{{ $category->getFullPath() }}</span>
    </td>
    <td style="white-space: nowrap;">
        <span class="badge" style="background-color: rgba(0, 122, 255, 0.1); color: var(--accent-color); font-size: 0.75rem; font-weight: 400;">
            {{ $category->children->count() }}件
        </span>
    </td>
    <td style="white-space: nowrap;">
        <span class="badge" style="background-color: rgba(52, 199, 89, 0.1); color: var(--success-color); font-size: 0.75rem; font-weight: 400;">
            {{ $category->metrics->count() }}件
        </span>
    </td>
    <td style="white-space: nowrap; font-size: 0.875rem;">
        {{ $category->sort_order }}
    </td>
    <td style="white-space: nowrap;">
        <div class="d-flex gap-1">
            @if(auth()->user()->hasPermission('master.categories.update'))
            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i> 編集
            </a>
            @endif
            @if(auth()->user()->hasPermission('master.categories.create'))
            <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}" class="btn btn-sm btn-outline-secondary" title="子カテゴリーを追加">
                <i class="bi bi-plus"></i>
            </a>
            @endif
            @if(auth()->user()->hasPermission('master.categories.delete'))
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display: inline;" onsubmit="return confirm('カテゴリー「{{ $category->name }}」を削除しますか？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i> 削除
                </button>
            </form>
            @endif
        </div>
    </td>
</tr>























