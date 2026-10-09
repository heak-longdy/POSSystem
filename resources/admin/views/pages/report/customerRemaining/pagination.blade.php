@if ($paginator->hasPages())
    <div class="paginationLayout2" style="padding: 12px 18px; border-top: 1px solid #e2e8f0; background: #fafbfc;">
        @include('admin::components.pagination', ['paginate' => $paginator])
    </div>
@endif
