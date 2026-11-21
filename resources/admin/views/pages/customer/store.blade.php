@extends('admin::shared.layout')
@section('layout')
    @include('admin::shared.header', ['header_name' => '', 'customClass' => 'headerInForm'])
    @include('admin::pages.customer.partials.map-assets')
    <div class="form-admin" x-data="xComponent">
        <div class="form-bg"></div>
        <form id="form" class="form-wrapper" action="{!! route('admin-'.$routeName.'-save', $id ?: (request('id') ?: ($data?->id ?? ''))) !!}" method="POST" enctype="multipart/form-data">
            <div class="form-header">
                <h3>
                    <i data-feather="arrow-left" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}"></i>
                    {{ ($id || request('id') || isset($data)) ? __('customer.form.title.update') : __('customer.form.title.create') }}
                </h3>
            </div>
            {{ csrf_field() }}
            <div class="form-body">
                {{-- Code & Name --}}
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('customer.form.code.label') }}</label>
                        <input type="text" name="code" value="{{ old('code', $data?->code ?? '') }}" placeholder="{{ __('customer.form.code.placeholder') }}">
                        <i class='bx bx-barcode'></i>
                        @error('code')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row iconInput">
                        <label>{{ __('customer.form.name.label') }} <span>*</span> </label>
                        <input type="text" name="name" value="{{ old('name', $data?->name ?? '') }}" placeholder="{{ __('customer.form.name.placeholder') }}">
                        <i class='bx bx-font-family'></i>
                        @error('name')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Phone & Status --}}
                <div class="row-2">
                    <div class="form-row iconInput">
                        <label>{{ __('customer.form.phone.label') }} <span>*</span> </label>
                        <input type="text" name="phone" value="{{ old('phone', $data?->phone ?? '') }}" placeholder="{{ __('customer.form.phone.placeholder') }}">
                        <i class='bx bx-phone'></i>
                        @error('phone')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row">
                        <label>@lang('global.form.status.label') <span>*</span></label>
                        <select name="status" id="status">
                            @foreach (config('dummy.status') as $key => $item)
                                <option value="{{ $key }}" {!! (string) old('status', $data?->status ?? 1) === (string) $key ? 'selected' : '' !!}>
                                    {{ $key == 1 ? __('global.form.status.active') : __('global.form.status.disable') }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                {{-- Payment Restriction --}}
                <div class="row-2">
                    <div class="form-row">
                        <label>{{ __('customer.form.payment_restriction.label') }} <span>*</span></label>
                        <select name="payment_restriction" id="payment_restriction">
                            <option value="N" {!! old('payment_restriction', $data?->payment_restriction ?? 'N') === 'N' ? 'selected' : '' !!}>
                                {{ __('customer.form.payment_restriction.no') }}
                            </option>
                            <option value="Y" {!! old('payment_restriction', $data?->payment_restriction ?? 'N') === 'Y' ? 'selected' : '' !!}>
                                {{ __('customer.form.payment_restriction.yes') }}
                            </option>
                        </select>
                        @error('payment_restriction')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                    <div class="form-row"></div>
                </div>

                {{-- Address --}}
                <div class="form-row">
                    <label>{{ __('customer.form.address.label') }}</label>
                    <textarea name="address" rows="3" placeholder="{{ __('customer.form.address.placeholder') }}">{{ old('address', $data?->address ?? '') }}</textarea>
                    @error('address')
                        <label class="error">{{ $message }}</label>
                    @enderror
                </div>

                {{-- Map Location Selection --}}
                <div class="customer-location-wrapper" style="margin-bottom: 22px;">
                    <div class="row-2">
                        <div class="form-row iconInput">
                            <label>{{ __('customer.form.location.latitude') }}</label>
                            <input type="text" name="latitude" x-model="latitude" placeholder="{{ __('customer.form.location.latitude_placeholder') }}" readonly autocomplete="off">
                            <i class='bx bx-map-pin'></i>
                            @error('latitude')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                        <div class="form-row iconInput">
                            <label>{{ __('customer.form.location.longitude') }}</label>
                            <input type="text" name="longitude" x-model="longitude" placeholder="{{ __('customer.form.location.longitude_placeholder') }}" readonly autocomplete="off">
                            <i class='bx bx-map-pin'></i>
                            @error('longitude')
                                <label class="error">{{ $message }}</label>
                            @enderror
                        </div>
                    </div>

                    {{-- Interactive Map Card --}}
                    <div class="customer-map-card" :class="{ 'is-fullscreen': isFullScreen }" @keydown.escape.window="if (isFullScreen) toggleFullScreen()">
                        <div class="map-toolbar">
                            <div class="map-search-group">
                                <svg class="search-svg-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchLocation()" placeholder="{{ __('customer.form.location.search_placeholder') }}" class="map-search-field">
                            </div>
                            <div class="map-btn-group">
                                <button type="button" class="btn-search-primary" @click="searchLocation()" :disabled="isSearching">
                                    <span x-show="!isSearching">{{ __('customer.form.location.search_btn') }}</span>
                                    <span x-show="isSearching" style="display: none;">{{ __('customer.form.location.searching') }}</span>
                                </button>
                                <button type="button" class="btn-locate-outline" @click="locateMe()" :disabled="isLocating" title="{{ __('customer.form.location.locate_me') }}">
                                    <svg class="nav-arrow-icon" :class="{'spin-anim': isLocating}" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                                    </svg>
                                    <span x-show="!isLocating">{{ __('customer.form.location.locate_me') }}</span>
                                    <span x-show="isLocating" style="display: none;">{{ __('customer.form.location.locating') }}</span>
                                </button>
                                <button type="button" class="btn-fullscreen-outline" @click="toggleFullScreen()" :title="isFullScreen ? '{{ __('customer.form.location.exit_fullscreen') }}' : '{{ __('customer.form.location.fullscreen') }}'">
                                    <template x-if="!isFullScreen">
                                        <span class="btn-fs-content">
                                            <svg class="fs-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                                            </svg>
                                            <span>{{ __('customer.form.location.fullscreen') }}</span>
                                        </span>
                                    </template>
                                    <template x-if="isFullScreen">
                                        <span class="btn-fs-content">
                                            <svg class="fs-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
                                            </svg>
                                            <span>{{ __('customer.form.location.exit_fullscreen') }}</span>
                                        </span>
                                    </template>
                                </button>
                                <button type="button" class="btn-clear-pill" @click="clearLocation()" x-show="latitude || longitude" style="display: none;" title="{{ __('customer.form.location.clear') }}">
                                    <i class='bx bx-trash'></i>
                                    <span>{{ __('customer.form.location.clear') }}</span>
                                </button>
                                <template x-if="googleMapsUrl()">
                                    <a :href="googleMapsUrl()" target="_blank" rel="noopener noreferrer" class="btn-gmap-pill" title="{{ __('customer.form.location.open_google_maps') }}">
                                        <i class='bx bx-link-external'></i>
                                        <span>{{ __('customer.form.location.open_google_maps') }}</span>
                                    </a>
                                </template>
                            </div>
                        </div>

                        {{-- Map Canvas --}}
                        <div class="map-canvas-wrapper">
                            <div x-ref="mapContainer" class="customer-map-canvas"></div>
                            <div x-show="searchNotice" x-cloak class="map-notice-pill" x-text="searchNotice"></div>
                        </div>

                        <div class="map-footer-bar">
                            <div class="map-hint-text">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                                <span>{{ __('customer.form.location.hint') }}</span>
                            </div>
                            <div x-show="isFullScreen" style="display: none;" class="map-esc-pill">
                                <span>ESC</span> to exit fullscreen
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    .customer-location-wrapper {
                        margin-bottom: 24px;
                    }

                    .customer-location-wrapper input[readonly] {
                        background-color: #f8fafc !important;
                        color: #475569 !important;
                        cursor: default !important;
                        border-color: #e2e8f0 !important;
                        user-select: all;
                    }

                    .customer-location-wrapper input[readonly]:focus {
                        border-color: #cbd5e1 !important;
                        box-shadow: none !important;
                    }

                    .customer-map-card {
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 8px;
                        box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05), 0 1px 3px rgba(15, 23, 42, 0.02);
                        padding: 14px;
                        margin-top: 10px;
                        transition: all 0.2s ease;
                    }

                    .customer-map-card:hover {
                        border-color: #cbd5e1;
                        box-shadow: 0 6px 20px -2px rgba(15, 23, 42, 0.07), 0 2px 4px rgba(15, 23, 42, 0.02);
                    }

                    .map-toolbar {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 10px;
                        margin-bottom: 12px;
                        flex-wrap: wrap;
                    }

                    .map-search-group {
                        position: relative;
                        flex: 1;
                        min-width: 260px;
                        display: flex;
                        align-items: center;
                    }

                    .search-svg-icon {
                        position: absolute;
                        left: 12px;
                        color: #94a3b8;
                        pointer-events: none;
                    }

                    .map-search-field {
                        width: 100%;
                        height: 38px;
                        padding: 0 14px 0 36px;
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 7px;
                        font-size: 13px;
                        color: #1e293b;
                        outline: none;
                        transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
                    }

                    .map-search-field::placeholder {
                        color: #94a3b8;
                        font-weight: 400;
                    }

                    .map-search-field:focus {
                        border-color: #3b82f6;
                        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                        background: #ffffff;
                    }

                    .map-btn-group {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        flex-wrap: wrap;
                    }

                    .btn-search-primary {
                        height: 38px;
                        padding: 0 18px;
                        background: #2563eb;
                        color: #ffffff;
                        border: 1px solid #2563eb;
                        border-radius: 7px;
                        font-size: 13px;
                        font-weight: 600;
                        cursor: pointer;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2);
                        transition: all 0.15s ease;
                    }

                    .btn-search-primary:hover {
                        background: #1d4ed8;
                        border-color: #1d4ed8;
                        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
                    }

                    .btn-locate-outline {
                        height: 38px !important;
                        padding: 0 14px !important;
                        background: #ffffff !important;
                        color: #334155 !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 7px !important;
                        font-size: 13px !important;
                        font-weight: 500 !important;
                        cursor: pointer !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 7px !important;
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
                        transition: all 0.15s ease !important;
                    }

                    .btn-locate-outline:hover {
                        background: #f8fafc !important;
                        border-color: #cbd5e1 !important;
                        color: #0f172a !important;
                    }

                    .nav-arrow-icon {
                        color: #64748b;
                        transition: transform 0.2s ease;
                    }

                    .btn-locate-outline:hover .nav-arrow-icon {
                        color: #2563eb;
                    }

                    .btn-clear-pill {
                        height: 38px !important;
                        padding: 0 12px !important;
                        background: #fef2f2 !important;
                        color: #dc2626 !important;
                        border: 1px solid #fee2e2 !important;
                        border-radius: 7px !important;
                        font-size: 12.5px !important;
                        font-weight: 500 !important;
                        cursor: pointer !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 5px !important;
                        transition: all 0.15s ease !important;
                    }

                    .btn-clear-pill:hover {
                        background: #fee2e2 !important;
                        border-color: #fca5a5 !important;
                        color: #dc2626 !important;
                    }

                    .btn-gmap-pill {
                        height: 38px !important;
                        padding: 0 12px !important;
                        background: #eff6ff !important;
                        color: #2563eb !important;
                        border: 1px solid #dbeafe !important;
                        border-radius: 7px !important;
                        font-size: 12.5px !important;
                        font-weight: 500 !important;
                        text-decoration: none !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 5px !important;
                        transition: all 0.15s ease !important;
                    }

                    .btn-gmap-pill:hover {
                        background: #dbeafe !important;
                        border-color: #bfdbfe !important;
                        color: #1d4ed8 !important;
                    }

                    .btn-fullscreen-outline {
                        height: 38px !important;
                        padding: 0 14px !important;
                        background: #ffffff !important;
                        color: #334155 !important;
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 7px !important;
                        font-size: 13px !important;
                        font-weight: 500 !important;
                        cursor: pointer !important;
                        display: inline-flex !important;
                        align-items: center !important;
                        gap: 6px !important;
                        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
                        transition: all 0.15s ease !important;
                    }

                    .btn-fullscreen-outline:hover {
                        background: #f8fafc !important;
                        border-color: #cbd5e1 !important;
                        color: #0f172a !important;
                    }

                    .btn-fullscreen-outline .fs-icon {
                        color: #64748b;
                        transition: transform 0.2s ease, color 0.15s ease;
                    }

                    .btn-fullscreen-outline:hover .fs-icon {
                        color: #2563eb;
                    }

                    .btn-fs-content {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                    }

                    .map-canvas-wrapper {
                        position: relative;
                        border-radius: 6px;
                        overflow: hidden;
                        border: 1px solid #e2e8f0;
                    }

                    .customer-map-canvas {
                        height: 360px;
                        width: 100%;
                        background: #f8fafc;
                    }

                    /* Modern Compact Zoom Controls */
                    .customer-map-canvas .leaflet-control-zoom {
                        border: 1px solid #e2e8f0 !important;
                        border-radius: 8px !important;
                        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08) !important;
                        overflow: hidden;
                        margin-top: 12px !important;
                        margin-left: 12px !important;
                    }

                    .customer-map-canvas .leaflet-control-zoom a {
                        width: 28px !important;
                        height: 28px !important;
                        line-height: 28px !important;
                        font-size: 14px !important;
                        color: #334155 !important;
                        background: #ffffff !important;
                        transition: background 0.15s ease, color 0.15s ease;
                        border-bottom: 1px solid #f1f5f9 !important;
                    }

                    .customer-map-canvas .leaflet-control-zoom a:last-child {
                        border-bottom: none !important;
                    }

                    .customer-map-canvas .leaflet-control-zoom a:hover {
                        background: #f8fafc !important;
                        color: #0f172a !important;
                    }

                    /* Leaflet Attribution styling */
                    .customer-map-canvas .leaflet-control-attribution {
                        background: rgba(255, 255, 255, 0.85) !important;
                        backdrop-filter: blur(4px);
                        font-size: 10px !important;
                        color: #64748b !important;
                        padding: 2px 8px !important;
                        border-top-left-radius: 4px;
                        border: none !important;
                        box-shadow: none !important;
                    }

                    .customer-map-canvas .leaflet-control-attribution a {
                        color: #475569 !important;
                        text-decoration: none;
                    }

                    .customer-map-canvas .leaflet-control-attribution a:hover {
                        text-decoration: underline;
                    }

                    /* Custom Modern Pin Marker */
                    .custom-modern-map-pin {
                        background: transparent !important;
                        border: none !important;
                    }

                    .pin-marker-wrapper {
                        position: relative;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 32px;
                        height: 40px;
                        cursor: grab;
                    }

                    .pin-marker-wrapper:active {
                        cursor: grabbing;
                    }

                    .pin-marker-pulse {
                        position: absolute;
                        bottom: -2px;
                        width: 14px;
                        height: 6px;
                        border-radius: 50%;
                        background: rgba(15, 23, 42, 0.25);
                        filter: blur(1px);
                    }

                    .pin-marker-body {
                        transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
                        filter: drop-shadow(0 4px 6px rgba(15, 23, 42, 0.3));
                    }

                    .pin-marker-wrapper:hover .pin-marker-body {
                        transform: translateY(-3px) scale(1.05);
                    }

                    .map-notice-pill {
                        position: absolute;
                        top: 12px;
                        left: 50%;
                        transform: translateX(-50%);
                        z-index: 1000;
                        background: rgba(15, 23, 42, 0.88);
                        color: #ffffff;
                        padding: 6px 16px;
                        border-radius: 20px;
                        font-size: 12px;
                        font-weight: 500;
                        pointer-events: none;
                        backdrop-filter: blur(6px);
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    }

                    .map-footer-bar {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-top: 10px;
                        padding: 0 2px;
                    }

                    .map-hint-text {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        font-size: 12px;
                        color: #64748b;
                    }

                    .map-hint-text svg {
                        color: #94a3b8;
                    }

                    .spin-anim {
                        animation: spin 1s linear infinite;
                    }

                    @keyframes spin {
                        100% { transform: rotate(360deg); }
                    }
                    /* Fullscreen Card Overlay Mode */
                    .customer-map-card.is-fullscreen {
                        position: fixed !important;
                        top: 0 !important;
                        left: 0 !important;
                        right: 0 !important;
                        bottom: 0 !important;
                        width: 100vw !important;
                        height: 100vh !important;
                        max-width: 100vw !important;
                        max-height: 100vh !important;
                        margin: 0 !important;
                        padding: 16px 20px !important;
                        z-index: 999999 !important;
                        background: #ffffff !important;
                        border-radius: 0 !important;
                        border: none !important;
                        box-shadow: none !important;
                        display: flex !important;
                        flex-direction: column !important;
                        box-sizing: border-box !important;
                    }

                    .customer-map-card.is-fullscreen .map-toolbar {
                        margin-bottom: 12px;
                        flex-shrink: 0;
                    }

                    .customer-map-card.is-fullscreen .map-canvas-wrapper {
                        flex: 1 1 auto;
                        min-height: 0;
                        display: flex;
                        flex-direction: column;
                        height: auto;
                        border-radius: 8px;
                    }

                    .customer-map-card.is-fullscreen .customer-map-canvas {
                        flex: 1 1 auto;
                        height: 100% !important;
                        min-height: 0;
                        width: 100%;
                    }

                    .customer-map-card.is-fullscreen .map-footer-bar {
                        flex-shrink: 0;
                        margin-top: 10px;
                    }

                    .customer-map-card.is-fullscreen .btn-fullscreen-outline {
                        background: #eff6ff !important;
                        border-color: #bfdbfe !important;
                        color: #1d4ed8 !important;
                        font-weight: 600 !important;
                    }

                    .customer-map-card.is-fullscreen .btn-fullscreen-outline .fs-icon {
                        color: #2563eb !important;
                    }

                    .map-esc-pill {
                        font-size: 11.5px;
                        color: #64748b;
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                    }

                    .map-esc-pill span {
                        display: inline-block;
                        padding: 2px 6px;
                        font-size: 10.5px;
                        font-weight: 700;
                        background: #f1f5f9;
                        color: #334155;
                        border: 1px solid #cbd5e1;
                        border-radius: 4px;
                        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
                        line-height: 1.2;
                    }

                    .leaflet-control-attribution.leaflet-control{
                        opacity: 0;
                    }
                </style>

                {{-- Customer Images (Profile, Image 1, Image 2) --}}
                <div class="row-3">
                    {{-- Profile Image --}}
                    <div class="form-row">
                        <label>{{ __('customer.form.profile.label') }}</label>
                        <div class="form-select-photo image" @click="selectImage('profile')">
                            <div class="select-photo" :class='{ active: profile }'>
                                <div class="icon">
                                    <i class='bx bx-cloud-upload'></i>
                                </div>
                                <div class="title">
                                    <p>@lang('global.form.image.placeholder')</p>
                                </div>
                            </div>
                            <template x-if="profile">
                                <div class="image-view active" style="position: relative;">
                                    <img :src="getImageSrc(profile)" alt="">
                                    <button type="button" @click.stop="profile = ''" title="Remove" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: #fff; width: 26px; height: 26px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;min-width:26px;padding:0;">
                                        <i class='bx bx-x' style="font-size: 18px;"></i>
                                    </button>
                                </div>
                            </template>
                            <input type="hidden" x-model="profile" name="profile" autocomplete="off" role="presentation">
                        </div>
                        @error('profile')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    {{-- Image 1 --}}
                    <div class="form-row">
                        <label>{{ __('customer.form.image_1.label') }}</label>
                        <div class="form-select-photo image" @click="selectImage('image_1')">
                            <div class="select-photo" :class='{ active: image1 }'>
                                <div class="icon">
                                    <i class='bx bx-cloud-upload'></i>
                                </div>
                                <div class="title">
                                    <p>@lang('global.form.image.placeholder')</p>
                                </div>
                            </div>
                            <template x-if="image1">
                                <div class="image-view active" style="position: relative;">
                                    <img :src="getImageSrc(image1)" alt="">
                                    <button type="button" @click.stop="image1 = ''" title="Remove" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: #fff; width: 26px; height: 26px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;min-width:26px;padding:0;">
                                        <i class='bx bx-x' style="font-size: 18px;"></i>
                                    </button>
                                </div>
                            </template>
                            <input type="hidden" x-model="image1" name="image_1" autocomplete="off" role="presentation">
                        </div>
                        @error('image_1')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>

                    {{-- Image 2 --}}
                    <div class="form-row">
                        <label>{{ __('customer.form.image_2.label') }}</label>
                        <div class="form-select-photo image" @click="selectImage('image_2')">
                            <div class="select-photo" :class='{ active: image2 }'>
                                <div class="icon">
                                    <i class='bx bx-cloud-upload'></i>
                                </div>
                                <div class="title">
                                    <p>@lang('global.form.image.placeholder')</p>
                                </div>
                            </div>
                            <template x-if="image2">
                                <div class="image-view active" style="position: relative;">
                                    <img :src="getImageSrc(image2)" alt="">
                                    <button type="button" @click.stop="image2 = ''" title="Remove" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: #fff; width: 26px; height: 26px; border-radius: 50%; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 10;min-width:26px;padding:0;">
                                        <i class='bx bx-x' style="font-size: 18px;"></i>
                                    </button>
                                </div>
                            </template>
                            <input type="hidden" x-model="image2" name="image_2" autocomplete="off" role="presentation">
                        </div>
                        @error('image_2')
                            <label class="error">{{ $message }}</label>
                        @enderror
                    </div>
                </div>

                <div class="form-button">
                    <button type="submit" color="primary">
                        <i data-feather="save"></i>
                        <span>{{ ($id || request('id') || isset($data)) ? __('global.button.update') : __('global.button.submit') }}</span>
                    </button>
                    <button type="submit" name="save_opt" value="save_new" color="success">
                        <i data-feather="save"></i>
                        <span>{{ __('global.button.save_new') }}</span>
                    </button>
                    <button color="danger" type="button" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
                        <i data-feather="x"></i>
                        <span>{{ __('global.button.cancel') }}</span>
                    </button>
                </div>
            </div>
            <div class="form-footer"></div>
        </form>
    </div>
    @include('admin::file-manager.popup')
@stop

@section('script')
    <script>
        function initCustomerStoreComponent() {
            Alpine.data("xComponent", () => ({
                baseImageUrl: "{{ asset('file_manager') }}",
                profile: "",
                image1: "",
                image2: "",
                latitude: "",
                longitude: "",
                searchQuery: "",
                isSearching: false,
                isLocating: false,
                isFullScreen: false,
                searchNotice: "",
                noticeTimeout: null,
                map: null,
                marker: null,
                defaultCenter: [11.5564, 104.9282], // Phnom Penh
                defaultZoom: 13,
                focusedZoom: 16,

                init() {
                    const data = @json($data ?? '');
                    this.profile = data?.profile ?? `{{ old('profile') }}`;
                    this.image1 = data?.image_1 ?? `{{ old('image_1') }}`;
                    this.image2 = data?.image_2 ?? `{{ old('image_2') }}`;

                    const rawLat = (data?.latitude !== undefined && data?.latitude !== null && data?.latitude !== '') 
                        ? data.latitude 
                        : `{{ old('latitude') }}`;
                    const rawLng = (data?.longitude !== undefined && data?.longitude !== null && data?.longitude !== '') 
                        ? data.longitude 
                        : `{{ old('longitude') }}`;

                    this.latitude = (rawLat && !isNaN(parseFloat(rawLat))) ? parseFloat(rawLat).toFixed(7) : '';
                    this.longitude = (rawLng && !isNaN(parseFloat(rawLng))) ? parseFloat(rawLng).toFixed(7) : '';

                    if ($(this.$root).find('#status').length) {
                        $(this.$root).find('#status').select2({ width: '100%' });
                    }
                    if ($(this.$root).find('#payment_restriction').length) {
                        $(this.$root).find('#payment_restriction').select2({ width: '100%' });
                    }

                    this.$nextTick(() => {
                        this.initMap();
                    });
                },

                hasValidCoords() {
                    const lat = parseFloat(this.latitude);
                    const lng = parseFloat(this.longitude);
                    return !isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
                },

                toggleFullScreen() {
                    this.isFullScreen = !this.isFullScreen;
                    if (this.isFullScreen) {
                        document.body.style.overflow = 'hidden';
                    } else {
                        document.body.style.overflow = '';
                    }
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (this.map) this.map.invalidateSize();
                        }, 80);
                        setTimeout(() => {
                            if (this.map) this.map.invalidateSize();
                        }, 250);
                    });
                },

                initMap() {
                    const container = this.$refs.mapContainer;
                    if (!container) return;

                    if (typeof window.L === 'undefined') {
                        setTimeout(() => this.initMap(), 100);
                        return;
                    }

                    if (this.map) {
                        try { this.map.remove(); } catch (e) {}
                        this.map = null;
                        this.marker = null;
                    }

                    const hasCoords = this.hasValidCoords();
                    const initialCenter = hasCoords 
                        ? [parseFloat(this.latitude), parseFloat(this.longitude)]
                        : this.defaultCenter;
                    const initialZoom = hasCoords ? this.focusedZoom : this.defaultZoom;

                    this.map = L.map(container, {
                        center: initialCenter,
                        zoom: initialZoom,
                        zoomControl: true,
                        scrollWheelZoom: 'center'
                    });

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    if (hasCoords) {
                        this.createOrUpdateMarker(parseFloat(this.latitude), parseFloat(this.longitude), false);
                    }

                    this.map.on('click', (e) => {
                        this.setCoordinates(e.latlng.lat, e.latlng.lng, false);
                    });

                    // Handle MDI workspace tab activation & window resize
                    const onTabActivated = (e) => {
                        if (this.$root && this.$root.closest('.workspace-tab-pane')?.id === 'tab-pane-' + e?.detail?.key) {
                            setTimeout(() => {
                                if (this.map) this.map.invalidateSize();
                            }, 150);
                        }
                    };
                    window.addEventListener('workspace:tab-activated', onTabActivated);
                    window.addEventListener('resize', () => {
                        if (this.map) this.map.invalidateSize();
                    });

                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 300);
                },

                createOrUpdateMarker(lat, lng, center = false) {
                    const parsedLat = parseFloat(lat);
                    const parsedLng = parseFloat(lng);
                    if (isNaN(parsedLat) || isNaN(parsedLng)) return;

                    const customPinIcon = L.divIcon({
                        className: 'custom-modern-map-pin',
                        html: `
                            <div class="pin-marker-wrapper">
                                <div class="pin-marker-pulse"></div>
                                <div class="pin-marker-body">
                                    <svg width="32" height="40" viewBox="0 0 32 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M16 0C7.16344 0 0 7.16344 0 16C0 27.2 14.5455 39.2727 15.1727 39.7818C15.4208 39.9836 15.7058 40.0936 16 40.0936C16.2942 40.0936 16.5792 39.9836 16.8273 39.7818C17.4545 39.2727 32 27.2 32 16C32 7.16344 24.8366 0 16 0Z" fill="#2563EB"/>
                                        <circle cx="16" cy="15" r="6" fill="#FFFFFF"/>
                                    </svg>
                                </div>
                            </div>
                        `,
                        iconSize: [32, 40],
                        iconAnchor: [16, 40],
                        popupAnchor: [0, -36]
                    });

                    if (this.marker) {
                        this.marker.setLatLng([parsedLat, parsedLng]);
                    } else if (this.map) {
                        this.marker = L.marker([parsedLat, parsedLng], {
                            icon: customPinIcon,
                            draggable: true,
                            autoPan: true
                        }).addTo(this.map);

                        this.marker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.setCoordinates(pos.lat, pos.lng, false);
                        });
                    }

                    if (this.marker) {
                        const popupContent = `
                            <div style="font-size: 12px; font-weight: 500; line-height: 1.5; color: #0f172a; padding: 2px;">
                                <div style="font-weight: 700; color: #2563eb; margin-bottom: 2px;">📍 {{ __('customer.form.location.pin_placed') }}</div>
                                <div>${parsedLat.toFixed(7)}, ${parsedLng.toFixed(7)}</div>
                            </div>
                        `;
                        this.marker.bindPopup(popupContent);
                    }

                    if (center && this.map) {
                        this.map.setView([parsedLat, parsedLng], this.focusedZoom);
                    }
                },

                setCoordinates(lat, lng, center = false) {
                    const parsedLat = parseFloat(lat);
                    const parsedLng = parseFloat(lng);
                    if (isNaN(parsedLat) || isNaN(parsedLng)) return;

                    this.latitude = parsedLat.toFixed(7);
                    this.longitude = parsedLng.toFixed(7);

                    this.createOrUpdateMarker(parsedLat, parsedLng, center);
                },

                onCoordinateManualInput() {
                    const latStr = (this.latitude || '').toString().trim();
                    const lngStr = (this.longitude || '').toString().trim();

                    if (!latStr && !lngStr) {
                        if (this.marker && this.map) {
                            this.map.removeLayer(this.marker);
                            this.marker = null;
                        }
                        return;
                    }

                    const lat = parseFloat(latStr);
                    const lng = parseFloat(lngStr);

                    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                        this.createOrUpdateMarker(lat, lng, true);
                    }
                },

                clearLocation() {
                    this.latitude = '';
                    this.longitude = '';
                    this.searchQuery = '';
                    if (this.marker && this.map) {
                        this.map.removeLayer(this.marker);
                        this.marker = null;
                    }
                    if (this.map) {
                        this.map.setView(this.defaultCenter, this.defaultZoom);
                    }
                    this.showNotice('{{ __('customer.form.location.clear') }}');
                },

                locateMe() {
                    if (!navigator.geolocation) {
                        this.showNotice('{{ __('customer.form.location.geo_not_supported') }}');
                        return;
                    }
                    this.isLocating = true;
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.isLocating = false;
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            this.setCoordinates(lat, lng, true);
                            this.showNotice('{{ __('customer.form.location.pin_placed') }}');
                        },
                        (error) => {
                            this.isLocating = false;
                            this.showNotice('{{ __('customer.form.location.geo_permission_denied') }}');
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                },

                async searchLocation() {
                    const query = (this.searchQuery || '').trim();
                    if (!query) return;

                    // Support direct coordinates format: "11.5564, 104.9282" or "11.5564 104.9282"
                    const coordMatch = query.match(/^(-?\d+(\.\d+)?)[,\s]+(-?\d+(\.\d+)?)$/);
                    if (coordMatch) {
                        const lat = parseFloat(coordMatch[1]);
                        const lng = parseFloat(coordMatch[3]);
                        if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                            this.setCoordinates(lat, lng, true);
                            this.showNotice('{{ __('customer.form.location.pin_placed') }}');
                            return;
                        }
                    }

                    this.isSearching = true;
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lng = parseFloat(data[0].lon);
                            this.setCoordinates(lat, lng, true);
                            const label = data[0].display_name ? data[0].display_name.split(',')[0] : '{{ __('customer.form.location.pin_placed') }}';
                            this.showNotice(label);
                        } else {
                            this.showNotice('{{ __('customer.form.location.search_no_results') }}');
                        }
                    } catch (err) {
                        this.showNotice('{{ __('customer.form.location.search_no_results') }}');
                    } finally {
                        this.isSearching = false;
                    }
                },

                googleMapsUrl() {
                    if (this.hasValidCoords()) {
                        return `https://www.google.com/maps?q=${this.latitude},${this.longitude}`;
                    }
                    return null;
                },

                showNotice(msg) {
                    this.searchNotice = msg;
                    if (this.noticeTimeout) clearTimeout(this.noticeTimeout);
                    this.noticeTimeout = setTimeout(() => {
                        this.searchNotice = '';
                    }, 3500);
                },

                getImageSrc(path) {
                    if (!path) return '';
                    if (path.startsWith('http://') || path.startsWith('https://')) {
                        return path;
                    }
                    return path.startsWith('/') 
                        ? this.baseImageUrl + path 
                        : this.baseImageUrl + '/' + path;
                },
                profileSrc() {
                    return this.getImageSrc(this.profile);
                },
                selectImage(field = 'profile') {
                    fileManager({
                        multiple: false,
                        afterClose: (data, basePath) => {
                            if (data && data.length > 0) {
                                if (field === 'image_1') {
                                    this.image1 = data[0].path;
                                } else if (field === 'image_2') {
                                    this.image2 = data[0].path;
                                } else {
                                    this.profile = data[0].path;
                                }
                            }
                        }
                    });
                }
            }));
        }

        if (window.Alpine) {
            initCustomerStoreComponent();
        } else {
            document.addEventListener('alpine:init', initCustomerStoreComponent);
        }
    </script>
@stop
