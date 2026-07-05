<div class="table">
    @if ($shops->count() > 0)
        <div class="table-wrapper">
            <div class="table-header">
                <div class="row table-row-5">
                    <span>Nº</span>
                </div>
                <div class="row table-row-10">
                    <span>Profile</span>
                </div>
                <div class="row table-row-15">
                    <span>Name</span>
                </div>
                <div class="row table-row-15">
                    <span>Phone</span>
                </div>
                <div class="row table-row-10">
                    <span>Total Amount</span>
                </div>
                <div class="row table-row-30">
                    <span>Transfer $</span>
                </div>
                <div class="row table-row-10">
                    <span>Net Earning $</span>
                </div>
            </div>
            <div class="table-body">
                @foreach ($shops as $index => $item)
                    <div class="column">
                        <div class="row table-row-5">
                            <span>{!! $shops->currentPage() * $shops->perPage() - $shops->perPage() + ($index + 1) !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <div class="thumbnail" data-fancybox data-src="{{ asset('file_manager' . $item->image)  }}">
                                <img src="{!! $item->image != null ? asset('file_manager' . $item->image) : asset('images/logo/default.png') !!}"
                                    onerror="(this).src='{{ asset('images/logo/default.png') }}'" alt="">
                            </div>
                        </div>
                        <div class="row table-row-15">
                            <span>{!! isset($item->name) ? $item->name : '--' !!}</span>
                        </div>
                        <div class="row table-row-15">
                            <span>{!! isset($item->phone) ? $item->phone : '--' !!}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{{ $item->total_amount }}</span>
                        </div>
                        <div class="row table-row-30">
                            <span>{{ $item->total_amount - $item->total_commission }}</span>
                        </div>
                        <div class="row table-row-10">
                            <span>{{ $item->total_commission }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="table-footer">
                @include('admin::components.pagination', ['paginate' => $shops])
            </div>
        </div>
    @else
        @component('admin::components.empty', [
            'name' => __('Barber is empty'),
            'msg' => __('You can create a new Barber by clicking the button below.'),
            'permission' => 'barber-create',
           // 'url' => route('admin-barber-create'),
           // 'button' => __('Create New Barber'),
            ])
        @endcomponent
    @endif
</div>
