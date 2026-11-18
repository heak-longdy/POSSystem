@extends('admin::shared.layout')

@section('title')
    | {{ __('customer.detail.title') }} - {{ $customer->name ?: ($customer->code ?: '#' . $customer->id) }}
@stop

@section('layout')
    @include('admin::shared.header', ['header_name' => __('customer.detail.title')])

    <div class="content-wrapper customer-detail-container" id="customerDetailApp" x-data="xCustomerDetail">
        <style>
            .customer-detail-container {
                padding: 24px 130px 50px;
                background: #f8fafc;
                min-height: calc(100vh - 70px);
                font-family: inherit;
                color: #0f172a;
            }

            .detail-top-nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 16px;
                margin-bottom: 22px;
            }

            .detail-breadcrumb {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 13.5px;
                color: #64748b;
                font-weight: 500;
            }

            .detail-breadcrumb a {
                color: #2563eb;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 5px;
                transition: color 0.15s ease;
            }

            .detail-breadcrumb a:hover {
                color: #1d4ed8;
                text-decoration: underline;
            }

            .detail-breadcrumb .sep {
                color: #cbd5e1;
            }

            .detail-header-actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .btn-action-back {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 8px 18px;
                font-size: 13.5px;
                font-weight: 600;
                background: #ffffff;
                color: #475569;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                cursor: pointer;
                text-decoration: none;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
                transition: all 0.15s ease;
            }

            .btn-action-back:hover {
                background: #f1f5f9;
                color: #1e293b;
                border-color: #94a3b8;
            }

            .btn-action-edit {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 8px 22px;
                font-size: 13.5px;
                font-weight: 600;
                background: #2563eb;
                color: #ffffff;
                border: 1px solid #2563eb;
                border-radius: 8px;
                cursor: pointer;
                text-decoration: none;
                box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
                transition: all 0.15s ease;
            }

            .btn-action-edit:hover {
                background: #1d4ed8;
                border-color: #1d4ed8;
                box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
                transform: translateY(-1px);
            }

            /* Customer Hero Profile Card */
            .customer-hero-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 16px;
                padding: 26px 30px;
                margin-bottom: 24px;
                box-shadow: 0 4px 18px -4px rgba(15, 23, 42, 0.04);
                position: relative;
                overflow: hidden;
            }

            .customer-hero-card::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #2563eb 0%, #3b82f6 50%, #60a5fa 100%);
            }

            .hero-profile-body {
                display: flex;
                align-items: center;
                gap: 24px;
                flex-wrap: wrap;
            }

            .hero-avatar-wrapper {
                position: relative;
                width: 88px;
                height: 88px;
                border-radius: 18px;
                overflow: hidden;
                border: 3px solid #ffffff;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
                background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                cursor: pointer;
            }

            .hero-avatar-wrapper img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.25s ease;
            }

            .hero-avatar-wrapper:hover img {
                transform: scale(1.08);
            }

            .hero-avatar-initials {
                font-size: 30px;
                font-weight: 800;
                color: #2563eb;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .hero-avatar-zoom-icon {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.45);
                display: flex;
                align-items: center;
                justify-content: center;
                color: #ffffff;
                font-size: 22px;
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            .hero-avatar-wrapper:hover .hero-avatar-zoom-icon {
                opacity: 1;
            }

            .hero-info-section {
                flex: 1;
                min-width: 280px;
            }

            .hero-title-row {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
                margin-bottom: 8px;
            }

            .hero-customer-name {
                font-size: 24px;
                font-weight: 800;
                color: #0f172a;
                margin: 0;
                line-height: 1.25;
            }

            .badge-code {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 10px;
                background: #f1f5f9;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                font-size: 13px;
                font-weight: 700;
                color: #334155;
                cursor: pointer;
                transition: all 0.15s ease;
                user-select: none;
            }

            .badge-code:hover {
                background: #e2e8f0;
                color: #0f172a;
            }

            .badge-code i {
                font-size: 15px;
                color: #64748b;
            }

            .badge-status {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12.5px;
                font-weight: 600;
            }

            .badge-status-active {
                background: #ecfdf5;
                border: 1px solid #a7f3d0;
                color: #047857;
            }

            .badge-status-active .status-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #059669;
                box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
            }

            .badge-status-disabled {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #b91c1c;
            }

            .badge-status-disabled .status-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #dc2626;
            }

            .badge-status-trashed {
                background: #f1f5f9;
                border: 1px solid #cbd5e1;
                color: #475569;
            }

            .badge-restriction {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12.5px;
                font-weight: 600;
            }

            .badge-restriction-yes {
                background: #fffbeb;
                border: 1px solid #fde68a;
                color: #b45309;
            }

            .badge-restriction-no {
                background: #eff6ff;
                border: 1px solid #bfdbfe;
                color: #1d4ed8;
            }

            /* Quick Meta Chips */
            .hero-meta-chips {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 16px;
                margin-top: 12px;
                font-size: 13.5px;
                color: #64748b;
            }

            .hero-meta-chip {
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .hero-meta-chip i {
                font-size: 16px;
                color: #94a3b8;
            }

            .hero-meta-chip a {
                color: #2563eb;
                text-decoration: none;
                font-weight: 600;
            }

            .hero-meta-chip a:hover {
                text-decoration: underline;
            }

            .copy-icon-btn {
                background: none;
                border: none;
                color: #94a3b8;
                cursor: pointer;
                padding: 2px 4px;
                display: inline-flex;
                align-items: center;
                font-size: 14px;
                border-radius: 4px;
                transition: all 0.15s ease;
            }

            .copy-icon-btn:hover {
                color: #2563eb;
                background: #eff6ff;
            }

            /* Content Grid Layout */
            .detail-grid-layout {
                display: grid;
                grid-template-columns: 1.15fr 0.85fr;
                gap: 24px;
            }

            @media (max-width: 992px) {
                .detail-grid-layout {
                    grid-template-columns: 1fr;
                }
            }

            /* Section Cards */
            .content-section-card {
                background: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                box-shadow: 0 2px 6px rgba(15, 23, 42, 0.02);
                margin-bottom: 24px;
                overflow: hidden;
            }

            .section-card-header {
                padding: 16px 22px;
                border-bottom: 1px solid #f1f5f9;
                background: #fafafa;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .section-card-title {
                margin: 0;
                font-size: 15px;
                font-weight: 700;
                color: #0f172a;
                display: flex;
                align-items: center;
                gap: 9px;
            }

            .section-card-title i {
                font-size: 19px;
                color: #2563eb;
            }

            .badge-count {
                font-size: 12px;
                font-weight: 600;
                padding: 2px 8px;
                background: #e2e8f0;
                color: #475569;
                border-radius: 12px;
            }

            .section-card-body {
                padding: 22px;
            }

            /* Modern Key-Value Fields List */
            .field-items-list {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .field-item-row {
                display: flex;
                align-items: flex-start;
                gap: 16px;
                padding-bottom: 14px;
                border-bottom: 1px solid #f1f5f9;
            }

            .field-item-row:last-child {
                border-bottom: none;
                padding-bottom: 0;
            }

            .field-item-icon {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                color: #64748b;
                flex-shrink: 0;
                margin-top: 2px;
            }

            .field-item-content {
                flex: 1;
            }

            .field-item-label {
                font-size: 11.5px;
                font-weight: 700;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 4px;
            }

            .field-item-value {
                font-size: 14.5px;
                font-weight: 600;
                color: #0f172a;
                line-height: 1.4;
            }

            /* Photos & Documents Section */
            .documents-gallery-grid {
                display: flex;
                flex-direction: column;
                gap: 18px;
            }

            .document-preview-card {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 14px;
                display: flex;
                align-items: center;
                gap: 16px;
                transition: all 0.15s ease;
            }

            .document-preview-card:hover {
                border-color: #cbd5e1;
                background: #ffffff;
                box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            }

            .document-thumb-box {
                position: relative;
                width: 100px;
                height: 100px;
                border-radius: 10px;
                overflow: hidden;
                border: 1px solid #cbd5e1;
                background: #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                cursor: pointer;
            }

            .document-thumb-box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.2s ease;
            }

            .document-thumb-box:hover img {
                transform: scale(1.06);
            }

            .document-zoom-overlay {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                color: #ffffff;
                font-size: 20px;
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            .document-thumb-box:hover .document-zoom-overlay {
                opacity: 1;
            }

            .document-empty-box {
                width: 100px;
                height: 100px;
                border-radius: 10px;
                border: 2px dashed #cbd5e1;
                background: #f8fafc;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 4px;
                color: #94a3b8;
                font-size: 11px;
                flex-shrink: 0;
                user-select: none;
            }

            .document-empty-box i {
                font-size: 26px;
                color: #cbd5e1;
            }

            .document-info-wrap {
                flex: 1;
            }

            .document-title {
                font-size: 14px;
                font-weight: 700;
                color: #0f172a;
                margin: 0 0 4px 0;
            }

            .document-subtitle {
                font-size: 12px;
                color: #64748b;
                margin: 0 0 8px 0;
            }

            .document-badge-status {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 2px 8px;
                border-radius: 6px;
                font-size: 11.5px;
                font-weight: 600;
            }

            .badge-uploaded {
                background: #ecfdf5;
                color: #047857;
                border: 1px solid #a7f3d0;
            }

            .badge-not-uploaded {
                background: #f1f5f9;
                color: #64748b;
                border: 1px solid #cbd5e1;
            }

            .gallery-tip-card {
                padding: 12px 16px;
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                border-radius: 10px;
                font-size: 12.5px;
                color: #166534;
                display: flex;
                align-items: center;
                gap: 10px;
                margin-top: 18px;
            }

            .gallery-tip-card i {
                font-size: 18px;
                color: #15803d;
                flex-shrink: 0;
            }

            /* Copied Toast Floating Alert */
            .toast-copied {
                position: fixed;
                bottom: 24px;
                right: 24px;
                background: #0f172a;
                color: #ffffff;
                padding: 10px 18px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 8px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
                z-index: 99999;
            }
        </style>

        {{-- Top Navigation & Action Buttons --}}
        <div class="detail-top-nav">
            <div class="detail-breadcrumb">
                <a href="{!! route('admin-' . $routeName . '-list', 1) !!}" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}">
                    <i data-feather="users" style="width: 15px; height: 15px;"></i>
                    <span>{{ __('customer.title') }}</span>
                </a>
                <span class="sep">/</span>
                <span>{{ __('customer.detail.title') }}</span>
                <span class="sep">/</span>
                <span style="font-weight: 700; color: #0f172a;">{{ $customer->name ?: ($customer->code ?: '#' . $customer->id) }}</span>
            </div>

            <div class="detail-header-actions">
                <a href="{!! route('admin-' . $routeName . '-list', 1) !!}" s-click-link="{!! route('admin-' . $routeName . '-list', 1) !!}" class="btn-action-back">
                    <i class='bx bx-arrow-back'></i>
                    <span>{{ __('customer.detail.action.back_to_list') }}</span>
                </a>
                <a href="{!! route('admin-' . $routeName . '-edit', $customer->id) !!}" s-click-link="{!! route('admin-' . $routeName . '-edit', $customer->id) !!}" class="btn-action-edit">
                    <i class='bx bx-edit-alt'></i>
                    <span>{{ __('customer.detail.action.edit') }}</span>
                </a>
            </div>
        </div>

        {{-- Customer Profile Hero Card --}}
        <div class="customer-hero-card">
            <div class="hero-profile-body">
                <div class="hero-avatar-wrapper"
                    @if ($customer->image_url) data-fancybox="customer-hero-avatar" data-src="{{ $customer->image_url }}" title="{{ __('customer.detail.click_to_zoom') }}" @endif>
                    @if ($customer->image_url)
                        <img src="{{ $customer->image_url }}" alt="{{ $customer->name }}">
                        <div class="hero-avatar-zoom-icon">
                            <i class='bx bx-search-alt'></i>
                        </div>
                    @else
                        <span class="hero-avatar-initials">{{ strtoupper(mb_substr($customer->name ?: 'C', 0, 2)) }}</span>
                    @endif
                </div>

                <div class="hero-info-section">
                    <div class="hero-title-row">
                        <h1 class="hero-customer-name">{{ $customer->name ?: __('order.walk_in_customer') }}</h1>

                        @if ($customer->code)
                            <div class="badge-code" @click="copyToClipboard('{{ $customer->code }}', 'code-hero')" title="{{ __('customer.detail.copy') }}">
                                <i class='bx bx-barcode'></i>
                                <span>{{ $customer->code }}</span>
                                <button type="button" class="copy-icon-btn">
                                    <i :class="copiedId === 'code-hero' ? 'bx bx-check text-success' : 'bx bx-copy'"></i>
                                </button>
                            </div>
                        @endif

                        @if ($customer->trashed())
                            <span class="badge-status badge-status-trashed">
                                <i data-feather="trash-2" style="width: 13px; height: 13px;"></i>
                                {{ __('global.tab.trash') }}
                            </span>
                        @elseif ($customer->status == 1)
                            <span class="badge-status badge-status-active">
                                <span class="status-dot"></span>
                                {{ __('global.form.status.active') }}
                            </span>
                        @else
                            <span class="badge-status badge-status-disabled">
                                <span class="status-dot"></span>
                                {{ __('global.form.status.disable') }}
                            </span>
                        @endif

                        @if ($customer->isFullPaymentRestricted())
                            <span class="badge-restriction badge-restriction-yes" title="{{ __('customer.form.payment_restriction.label') }}">
                                <i class='bx bx-lock-alt'></i>
                                {{ __('customer.form.payment_restriction.yes') }}
                            </span>
                        @else
                            <span class="badge-restriction badge-restriction-no" title="{{ __('customer.form.payment_restriction.label') }}">
                                <i class='bx bx-check-shield'></i>
                                {{ __('customer.form.payment_restriction.no') }}
                            </span>
                        @endif
                    </div>

                    <div class="hero-meta-chips">
                        @if ($customer->phone)
                            <span class="hero-meta-chip">
                                <i class='bx bx-phone'></i>
                                <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                                <button type="button" class="copy-icon-btn" @click="copyToClipboard('{{ $customer->phone }}', 'phone-hero')" title="{{ __('customer.detail.copy') }}">
                                    <i :class="copiedId === 'phone-hero' ? 'bx bx-check text-success' : 'bx bx-copy'"></i>
                                </button>
                            </span>
                        @endif

                        @if ($customer->address)
                            <span class="hero-meta-chip">
                                <i class='bx bx-map-pin'></i>
                                <span>{{ $customer->address }}</span>
                            </span>
                        @endif

                        @if ($customer->createdBy)
                            <span class="hero-meta-chip">
                                <i class='bx bx-user'></i>
                                <span>{{ __('customer.detail.created_by') }}: <strong>{{ $customer->createdBy->name }}</strong></span>
                            </span>
                        @endif

                        @if ($customer->created_at)
                            <span class="hero-meta-chip">
                                <i class='bx bx-calendar'></i>
                                <span>{{ $customer->created_at->format('d M Y, h:i A') }}</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- 2-Column Information & Documents Layout --}}
        <div class="detail-grid-layout">
            {{-- Column 1: Customer Profile Information & Audit --}}
            <div>
                {{-- Customer Profile & Contact Information --}}
                <div class="content-section-card">
                    <div class="section-card-header">
                        <h3 class="section-card-title">
                            <i class='bx bx-id-card'></i>
                            <span>{{ __('customer.detail.info_title') }}</span>
                        </h3>
                    </div>
                    <div class="section-card-body">
                        <div class="field-items-list">
                            {{-- Code --}}
                            <div class="field-item-row">
                                <div class="field-item-icon">
                                    <i class='bx bx-barcode'></i>
                                </div>
                                <div class="field-item-content">
                                    <div class="field-item-label">{{ __('customer.form.code.label') }}</div>
                                    <div class="field-item-value" style="display: flex; align-items: center; gap: 8px;">
                                        <span>{{ $customer->code ?: '---' }}</span>
                                        @if ($customer->code)
                                            <button type="button" class="copy-icon-btn" @click="copyToClipboard('{{ $customer->code }}', 'code-card')" title="{{ __('customer.detail.copy') }}">
                                                <i :class="copiedId === 'code-card' ? 'bx bx-check text-success' : 'bx bx-copy'"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Name --}}
                            <div class="field-item-row">
                                <div class="field-item-icon">
                                    <i class='bx bx-user'></i>
                                </div>
                                <div class="field-item-content">
                                    <div class="field-item-label">{{ __('customer.form.name.label') }}</div>
                                    <div class="field-item-value">{{ $customer->name ?: '---' }}</div>
                                </div>
                            </div>

                            {{-- Phone --}}
                            <div class="field-item-row">
                                <div class="field-item-icon">
                                    <i class='bx bx-phone'></i>
                                </div>
                                <div class="field-item-content">
                                    <div class="field-item-label">{{ __('customer.form.phone.label') }}</div>
                                    <div class="field-item-value" style="display: flex; align-items: center; gap: 8px;">
                                        @if ($customer->phone)
                                            <a href="tel:{{ $customer->phone }}" style="color: #2563eb; text-decoration: none;">{{ $customer->phone }}</a>
                                            <button type="button" class="copy-icon-btn" @click="copyToClipboard('{{ $customer->phone }}', 'phone-card')" title="{{ __('customer.detail.copy') }}">
                                                <i :class="copiedId === 'phone-card' ? 'bx bx-check text-success' : 'bx bx-copy'"></i>
                                            </button>
                                        @else
                                            <span>---</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Address --}}
                            <div class="field-item-row">
                                <div class="field-item-icon">
                                    <i class='bx bx-map-pin'></i>
                                </div>
                                <div class="field-item-content">
                                    <div class="field-item-label">{{ __('customer.form.address.label') }}</div>
                                    <div class="field-item-value">{{ $customer->address ?: '---' }}</div>
                                </div>
                            </div>

                            {{-- Payment Restriction --}}
                            <div class="field-item-row">
                                <div class="field-item-icon">
                                    <i class='bx bx-credit-card-front'></i>
                                </div>
                                <div class="field-item-content">
                                    <div class="field-item-label">{{ __('customer.form.payment_restriction.label') }}</div>
                                    <div class="field-item-value">
                                        @if ($customer->isFullPaymentRestricted())
                                            <span class="badge-restriction badge-restriction-yes">
                                                <i class='bx bx-lock-alt'></i>
                                                {{ __('customer.form.payment_restriction.yes') }}
                                            </span>
                                        @else
                                            <span class="badge-restriction badge-restriction-no">
                                                <i class='bx bx-check-shield'></i>
                                                {{ __('customer.form.payment_restriction.no') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="field-item-row">
                                <div class="field-item-icon">
                                    <i class='bx bx-check-shield'></i>
                                </div>
                                <div class="field-item-content">
                                    <div class="field-item-label">@lang('global.form.status.label')</div>
                                    <div class="field-item-value">
                                        @if ($customer->status == 1)
                                            <span class="badge-status badge-status-active">
                                                <span class="status-dot"></span>
                                                {{ __('global.form.status.active') }}
                                            </span>
                                        @else
                                            <span class="badge-status badge-status-disabled">
                                                <span class="status-dot"></span>
                                                {{ __('global.form.status.disable') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- System & Audit Metadata --}}
                <div class="content-section-card">
                    <div class="section-card-header">
                        <h3 class="section-card-title">
                            <i class='bx bx-shield-quarter'></i>
                            <span>{{ __('customer.detail.system_info') }}</span>
                        </h3>
                    </div>
                    <div class="section-card-body">
                        <div class="field-items-list">
                            @if ($customer->createdBy)
                                <div class="field-item-row">
                                    <div class="field-item-icon">
                                        <i class='bx bx-user-check'></i>
                                    </div>
                                    <div class="field-item-content">
                                        <div class="field-item-label">{{ __('customer.detail.created_by') }}</div>
                                        <div class="field-item-value">{{ $customer->createdBy->name }} ({{ $customer->createdBy->email }})</div>
                                    </div>
                                </div>
                            @endif

                            <div class="field-item-row">
                                <div class="field-item-icon">
                                    <i class='bx bx-calendar-plus'></i>
                                </div>
                                <div class="field-item-content">
                                    <div class="field-item-label">{{ __('customer.detail.created_at') }}</div>
                                    <div class="field-item-value">{{ $customer->created_at ? $customer->created_at->format('d M Y, h:i:s A') : '---' }}</div>
                                </div>
                            </div>

                            <div class="field-item-row">
                                <div class="field-item-icon">
                                    <i class='bx bx-time-five'></i>
                                </div>
                                <div class="field-item-content">
                                    <div class="field-item-label">{{ __('customer.detail.updated_at') }}</div>
                                    <div class="field-item-value">{{ $customer->updated_at ? $customer->updated_at->format('d M Y, h:i:s A') : '---' }}</div>
                                </div>
                            </div>

                            <div class="field-item-row">
                                <div class="field-item-icon">
                                    <i class='bx bx-hash'></i>
                                </div>
                                <div class="field-item-content">
                                    <div class="field-item-label">{{ __('customer.detail.database_id') }}</div>
                                    <div class="field-item-value">#{{ $customer->id }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Column 2: Attached Documents & Photos --}}
            <div>
                <div class="content-section-card">
                    <div class="section-card-header">
                        <h3 class="section-card-title">
                            <i class='bx bx-images'></i>
                            <span>{{ __('customer.detail.images_title') }}</span>
                        </h3>
                        <span class="badge-count">{{ __('customer.detail.slots_count', ['count' => 3]) }}</span>
                    </div>
                    <div class="section-card-body">
                        <div class="documents-gallery-grid">
                            {{-- Profile Photo Slot --}}
                            <div class="document-preview-card">
                                @if ($customer->image_url)
                                    <div class="document-thumb-box" data-fancybox="customer-gallery" data-src="{{ $customer->image_url }}" title="{{ __('customer.form.profile.label') }}">
                                        <img src="{{ $customer->image_url }}" alt="{{ __('customer.form.profile.label') }}">
                                        <div class="document-zoom-overlay">
                                            <i class='bx bx-search-alt-2'></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="document-empty-box">
                                        <i class='bx bx-image'></i>
                                        <span>{{ __('customer.detail.no_photo') }}</span>
                                    </div>
                                @endif

                                <div class="document-info-wrap">
                                    <h4 class="document-title">{{ __('customer.form.profile.label') }}</h4>
                                    <p class="document-subtitle">{{ __('customer.detail.profile_desc') }}</p>
                                    @if ($customer->image_url)
                                        <span class="document-badge-status badge-uploaded">
                                            <i class='bx bx-check'></i> {{ __('customer.detail.uploaded') }}
                                        </span>
                                    @else
                                        <span class="document-badge-status badge-not-uploaded">
                                            {{ __('customer.detail.no_image') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Image 1 Slot --}}
                            <div class="document-preview-card">
                                @if ($customer->image_1_url)
                                    <div class="document-thumb-box" data-fancybox="customer-gallery" data-src="{{ $customer->image_1_url }}" title="{{ __('customer.form.image_1.label') }}">
                                        <img src="{{ $customer->image_1_url }}" alt="{{ __('customer.form.image_1.label') }}">
                                        <div class="document-zoom-overlay">
                                            <i class='bx bx-search-alt-2'></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="document-empty-box">
                                        <i class='bx bx-id-card'></i>
                                        <span>{{ __('customer.detail.no_document') }}</span>
                                    </div>
                                @endif

                                <div class="document-info-wrap">
                                    <h4 class="document-title">{{ __('customer.form.image_1.label') }}</h4>
                                    <p class="document-subtitle">{{ __('customer.detail.image_1_desc') }}</p>
                                    @if ($customer->image_1_url)
                                        <span class="document-badge-status badge-uploaded">
                                            <i class='bx bx-check'></i> {{ __('customer.detail.uploaded') }}
                                        </span>
                                    @else
                                        <span class="document-badge-status badge-not-uploaded">
                                            {{ __('customer.detail.no_image') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Image 2 Slot --}}
                            <div class="document-preview-card">
                                @if ($customer->image_2_url)
                                    <div class="document-thumb-box" data-fancybox="customer-gallery" data-src="{{ $customer->image_2_url }}" title="{{ __('customer.form.image_2.label') }}">
                                        <img src="{{ $customer->image_2_url }}" alt="{{ __('customer.form.image_2.label') }}">
                                        <div class="document-zoom-overlay">
                                            <i class='bx bx-search-alt-2'></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="document-empty-box">
                                        <i class='bx bx-file'></i>
                                        <span>{{ __('customer.detail.no_document') }}</span>
                                    </div>
                                @endif

                                <div class="document-info-wrap">
                                    <h4 class="document-title">{{ __('customer.form.image_2.label') }}</h4>
                                    <p class="document-subtitle">{{ __('customer.detail.image_2_desc') }}</p>
                                    @if ($customer->image_2_url)
                                        <span class="document-badge-status badge-uploaded">
                                            <i class='bx bx-check'></i> {{ __('customer.detail.uploaded') }}
                                        </span>
                                    @else
                                        <span class="document-badge-status badge-not-uploaded">
                                            {{ __('customer.detail.no_image') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="gallery-tip-card">
                            <i class='bx bx-info-circle'></i>
                            <span>{{ __('customer.detail.gallery_help') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toast notification for clipboard copy --}}
        <template x-if="copiedId">
            <div class="toast-copied" x-transition>
                <i class='bx bx-check-circle' style="color: #4ade80; font-size: 18px;"></i>
                <span>{{ __('customer.detail.copied') }}</span>
            </div>
        </template>
    </div>
@stop

@section('script')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('xCustomerDetail', () => ({
                copiedId: null,

                init() {
                    if (window.feather) {
                        feather.replace();
                    }
                },

                copyToClipboard(text, id) {
                    if (!text) return;
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text);
                    } else {
                        const input = document.createElement('textarea');
                        input.value = text;
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        document.body.removeChild(input);
                    }
                    this.copiedId = id;
                    setTimeout(() => {
                        if (this.copiedId === id) {
                            this.copiedId = null;
                        }
                    }, 2000);
                }
            }));
        });
    </script>
@stop
