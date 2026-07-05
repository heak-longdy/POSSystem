@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-sector-save', request('id')) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-sector-list', 1) !!}"></i>
                    {{ request('id') ? 'Update Sector' : 'Create Sector' }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                <div class="row-3">
                    <div class="form-row">
                        <label>Position</label>
                        <select name="position_id" id="position_id" x-init="fetchSelectPosition()">
                            <option value=""> Select Position</option>
                        </select>
                    </div>
                </div>
                <div class="row-3">
                    <div class="form-row">
                        <label>Title <span>*</span> </label>
                        <input type="text" name="title" value="{!! request('id') ? $data?->title : old('title') !!}" placeholder="Enter title ...">
                        @error('title')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                <div class="row-3">
                    <div class="form-row">
                        <label>@lang('adminGlobal.form.status.label')<span>*</span></label>
                        <select name="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (request('id') && $data->status == $key) || old('status') == $key ? 'selected' : '' !!}>{{ $item }}</option>
                            @endforeach
                        </select>
                        {{-- <div class="radio-input">
                            <label class="label">
                                <input type="radio" id="value-1" checked="" name="value-radio" value="value-1" />
                                <p class="text">Activate</p>
                            </label>
                            <label class="label">
                                <input type="radio" id="value-2" name="value-radio" value="value-2" />
                                <p class="text">Disable</p>
                            </label>
                        </div> --}}

                    </div>
                </div>
                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>Submit</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-sector-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
@stop

@section('script')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data("xComponent", () => ({
                baseImageUrl: "{{ asset('file_manager') }}",
                image: "",
                init() {
                    const data = @json($data ?? '');
                    this.image = data?.image ?? "";
                    $select2Data('#position_id', data?.position_id, data?.pos_title)
                },
                fetchSelectPosition() {
                    $(`#position_id`).select2({
                        placeholder: `Select Position`,
                        language: {
                            searching: () => {
                                return "Please enter a search term"; // Placeholder text for search input
                            }
                        },
                        ajax: {
                            url: '{{ route('admin-select-position') }}',
                            dataType: 'json',
                            type: "GET",
                            quietMillis: 50,
                            data: (param) => {
                                return {
                                    search: param.term
                                };
                            },
                            processResults: (data) => {
                                return {
                                    results: $.map(data.data, (item) => {
                                        return {
                                            text: item?.title ? item?.title : '',
                                            id: item.id
                                        }
                                    })
                                };
                            }
                        }
                    }).on('select2:open', (e) => {
                        $select2FocusInputSearch();
                    });
                },
            }));
        });
    </script>
@stop
