<div class="header-tab-wrapper">
    <div class="menu-row">
        <div class="tabs">
            @foreach ($data as $item)
                <a href="{{ url($item['url']) }}" class="{!! Request::is($item['url']) ? 'tabActive' : '' !!}">
                    <i class='bx bx-data'></i>
                    {{ $item['name'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
