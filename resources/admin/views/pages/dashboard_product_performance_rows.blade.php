@forelse ($productPerformance ?? [] as $item)
    <tr>
        <td>
            <div class="product-cell">
                <div class="product-thumb {{ $item['thumb_color'] ?? 'blue' }}">
                    @if (!empty($item['image_url']))
                        <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] ?? '' }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center;">
                            {!! $item['vector_icon'] ?? '' !!}
                        </div>
                    @else
                        {!! $item['vector_icon'] ?? '' !!}
                    @endif
                </div>
                <div class="product-info">
                    <h5>
                        @if (!empty($item['product_id']) && Route::has('admin-product-edit'))
                            <a href="{{ route('admin-product-edit', ['id' => $item['product_id']]) }}" class="product-name-link">{{ $item['name'] ?? '' }}</a>
                        @else
                            {{ $item['name'] ?? '' }}
                        @endif
                    </h5>
                    <p>{{ $item['category'] ?? '' }}</p>
                </div>
            </div>
        </td>
        <td>{{ $item['progress'] ?? 0 }}%</td>
        <td>
            <span class="item-badge-pill {{ $item['badge_class'] ?? 'badge-teal' }}">@lang('dashboard.priority.' . ($item['priority'] ?? 'low'))</span>
        </td>
        <td>{{ $item['budget'] ?? '$0.00' }}</td>
        <td>
            <svg class="table-sparkline" viewBox="0 0 90 30" fill="none">
                <path d="{{ $item['sparkline_path'] ?? 'M2 15 Q 22 24, 45 12 T 88 16' }}" stroke="{{ $item['sparkline_color'] ?? '#CBD5E1' }}" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5">
            <div class="product-empty-state">
                <div class="empty-icon-circle">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </div>
                <p>@lang('dashboard.no_product_data')</p>
            </div>
        </td>
    </tr>
@endforelse
