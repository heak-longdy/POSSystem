<div class="pagination">
    <div class="pagination-left">
        <span>@lang('table.paginate.showing') {!! $paginate->firstItem() !!} - {!! $paginate->lastItem() !!}
            @lang('table.paginate.of')
            {!! number_format($paginate->total(), 0) !!}</span>
    </div>
    <div class="pagination-right">
        <div class="pagination-wrapper">
            <a href="{!! customUrl($paginate->previousPageUrl(), request()->all()) !!}" class="pagination-item left {!! $paginate->currentPage() == 1 ? 'disabled' : '' !!}">
                {{-- <i class='bx bx-chevron-left'></i> --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"><path d="M560-253.85 333.85-480 560-706.15 602.15-664l-184 184 184 184L560-253.85Z"/></svg>
            </a>

            @if ($paginate->lastPage() > 10)

                @if ($paginate->currentPage() >= 4)
                    <a href="{!! customUrl($paginate->currentPage() != 1 ? url()->current() . '?page=1' : null, request()->all()) !!}" class="pagination-item {!! $paginate->currentPage() == 1 ? 'active' : '' !!}">
                        <span>1</span>
                    </a>
                    <div class="pagination-item disabled">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                    </div>
                @else
                    @for ($i = 1; $i <= ($paginate->total() > 4 ? 4 : $paginate->lastPage()); $i++)
                        <a class="pagination-item {!! $paginate->currentPage() == $i ? 'active' : '' !!}" href="{!! customUrl($paginate->currentPage() != $i ? url()->current() . '?page=' . $i : null, request()->all()) !!}">
                            <span>{!! $i !!}</span>
                        </a>
                    @endfor
                @endif

                @if ($paginate->currentPage() >= 4 && $paginate->currentPage() < $paginate->lastPage() - 2)
                    @for ($i = $paginate->currentPage() - 1; $i <= $paginate->currentPage() + 1; $i++)
                        <a class="pagination-item {!! $paginate->currentPage() == $i ? 'active' : '' !!}" href="{!! customUrl($paginate->currentPage() != $i ? url()->current() . '?page=' . $i : null, request()->all()) !!}">
                            <span>{!! $i !!}</span>
                        </a>
                    @endfor
                @endif

                @if ($paginate->currentPage() < $paginate->lastPage() - 2)
                    <div class="pagination-item disabled">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" ><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM6 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                    </div>
                    <a class="pagination-item {!! $paginate->currentPage() == $paginate->lastPage() ? 'active' : '' !!}" href="{!! customUrl($paginate->currentPage() != $paginate->lastPage() ? url()->current() . '?page=' . $paginate->lastPage() : null, request()->all()) !!}">
                        <span>{!! $paginate->lastPage() !!}</span>
                    </a>
                @else
                    @for ($i = $paginate->lastPage() - 3; $i <= $paginate->lastPage(); $i++)
                        <a class="pagination-item {!! $paginate->currentPage() == $i ? 'active' : '' !!}" href="{!! customUrl($paginate->currentPage() != $i ? url()->current() . '?page=' . $i : null, request()->all()) !!}">
                            <span>{!! $i !!}</span>
                        </a>
                    @endfor
                @endif

            @else
                @for ($i = 1; $i <= $paginate->lastPage(); $i++)
                    <a class="pagination-item {!! $paginate->currentPage() == $i ? 'active' : '' !!}" href="{!! customUrl($paginate->currentPage() != $i ? url()->current() . '?page=' . $i : null, request()->all()) !!}">
                        <span>{!! $i !!}</span>
                    </a>
                @endfor
            @endif

            <a href="{!! customUrl($paginate->nextPageUrl(), request()->all()) !!}" class="pagination-item right {!! $paginate->currentPage() == $paginate->lastPage() ? 'disabled' : '' !!}" href="">
                {{-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"><path d="M536.92-480.62 342.77-675.15l32.61-32.62 226.77 227.15-226.77 226.77-32.61-32.61 194.15-194.16Z"/></svg> --}}
                {{-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960"><path d="M536.92-480.62 342.77-675.15l32.61-32.62 226.77 227.15-226.77 226.77-32.61-32.61 194.15-194.16Z"/></svg> --}}
                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 -960 960 960"><path d="M530-481 332-679l43-43 241 241-241 241-43-43 198-198Z"/></svg>
            </a>
        </div>
    </div>
</div>