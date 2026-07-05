<div class="pagination">
    <div class="rows-per-page">
        <span>Rows per page:</span>
        {{-- <select>
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="20">20</option>
        </select> --}}
    </div>
    <div class="page-info">
        <span>{!! $paginate->firstItem() !!} - {!! $paginate->lastItem() !!} of {!! number_format($paginate->total(), 0) !!}</span>
    </div>
    <div class="page-controls-tb">
        <button class="first-page btn {!! $paginate->currentPage() == 1 ? 'disabled' : '' !!}" s-click-link="{!! customUrl($paginate->url(1), request()->all()) !!}"><i class='bx bx-first-page'></i></button>
        <button class="prev-page btn {!! $paginate->currentPage() == 1 ? 'disabled' : '' !!}" s-click-link="{!! customUrl($paginate->previousPageUrl(), request()->all()) !!}"><i class='bx bx-chevron-left'></i></button>
        <button class="next-page btn {!! $paginate->currentPage() == $paginate->lastPage() ? 'disabled' : '' !!}" s-click-link="{!! customUrl($paginate->nextPageUrl(), request()->all()) !!}"><i class='bx bx-chevron-right'></i></button>
        <button class="last-page btn {!! $paginate->currentPage() == $paginate->lastPage() ? 'disabled' : '' !!}" s-click-link="{!! customUrl($paginate->url($paginate->lastPage()), request()->all()) !!}"><i class='bx bx-last-page'></i></button>
    </div>
</div>