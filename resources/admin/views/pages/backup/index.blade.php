@extends('admin::shared.layout')

@section('layout')
<style>
    .backup-dashboard {
        padding: 20px;
        background-color: #f8fafc;
        min-height: 100%;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Metric Cards */
    .metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }
    .metric-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 18px 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }
    .metric-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .metric-icon-blue   { background: #eff6ff; color: #2563eb; }
    .metric-icon-indigo { background: #eef2ff; color: #4f46e5; }
    .metric-icon-emerald{ background: #ecfdf5; color: #059669; }
    .metric-icon-purple { background: #faf5ff; color: #9333ea; }
    .metric-icon-sky    { background: #f0f9ff; color: #0284c7; }

    .metric-info {
        display: flex;
        flex-direction: column;
    }
    .metric-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .metric-value {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin-top: 2px;
    }
    .metric-sub {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 2px;
    }

    /* Disk Usage Section */
    .disk-usage-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 20px 22px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .disk-usage-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .disk-usage-title-wrap h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .disk-usage-title-wrap p {
        margin: 2px 0 0 0;
        font-size: 12px;
        color: #64748b;
    }

    .health-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .health-badge.health-ok {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .health-badge.health-warn {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .health-badge.health-danger {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .disk-stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }
    @media (max-width: 680px) {
        .disk-stats-row {
            grid-template-columns: 1fr;
        }
    }
    .disk-stat-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .disk-stat-box i {
        font-size: 26px;
        color: #2563eb;
    }
    .disk-stat-box .stat-meta {
        display: flex;
        flex-direction: column;
    }
    .disk-stat-box .stat-label {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .disk-stat-box .stat-val {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin-top: 1px;
    }
    .disk-stat-box .stat-sub {
        font-size: 11.5px;
        color: #94a3b8;
    }

    .disk-progress-track {
        height: 14px;
        background: #e2e8f0;
        border-radius: 7px;
        overflow: hidden;
        display: flex;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.06);
    }
    .disk-segment {
        height: 100%;
        transition: width 0.4s ease;
    }
    .seg-db      { background: #2563eb; }
    .seg-uploads { background: #9333ea; }
    .seg-backups { background: #f59e0b; }
    .seg-other   { background: #64748b; }
    .seg-free    { background: #e2e8f0; }

    .disk-legend-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        padding-top: 4px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #475569;
    }
    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 3px;
        flex-shrink: 0;
    }

    /* Main Container & Tabs */
    .backup-content-card {
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .backup-tabs-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        flex-wrap: wrap;
        gap: 10px;
    }
    .backup-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        overflow-x: auto;
    }
    .backup-tab-btn {
        padding: 16px 16px !important;
        font-size: 13px !important;
        font-weight: 600 !important; 
        color: #64748b !important;
        background: none !important;
        border: none !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        white-space: nowrap !important;
        transition: all 0.2s ease !important;
    }
    .backup-tab-btn:hover {
        color: #2563eb !important;
    }
    .backup-tab-btn.active {
        color: #2563eb !important;
    }
    .backup-tab-btn .badge-pill {
        background: #f1f5f9 !important;
        color: #475569 !important;
        padding: 2px 8px !important;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        line-height: normal !important;
    }
    .backup-tab-btn.active .badge-pill {
        background: #dbeafe;
        color: #1e40af;
    }

    .backup-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
    }
    .btn-action-primary {
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 9px 16px;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 5px rgba(37, 99, 235, 0.25);
        transition: background 0.15s ease;
    }
    .btn-action-primary:hover {
        background: #1d4ed8;
    }
    .btn-action-outline {
        background: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 8px 14px;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
    }
    .btn-action-outline:hover {
        background: #f8fafc;
        border-color: #94a3b8;
        color: #1e293b;
    }

    /* Tab Panes */
    .backup-tab-pane {
        padding: 20px;
    }

    /* Table Styles */
    .backup-table-wrapper {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }
    .backup-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }
    .backup-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .backup-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }
    .backup-table tr:last-child td {
        border-bottom: none;
    }
    .backup-table tr:hover td {
        background-color: #f8fafc;
    }

    /* Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 600;
    }
    .status-badge.badge-full {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .status-badge.badge-db {
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }
    .status-badge.badge-cloud-ok {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .status-badge.badge-cloud-none {
        background: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .status-badge.badge-cloud-failed {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    /* Table Action Icons */
    .table-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .action-icon-btn {
        width: 32px !important;
        height: 32px !important;
        min-width:0 !important;
        border-radius: 6px !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #475569 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 16px !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
    }
    .action-icon-btn:hover {
        background: #f1f5f9 !important;
        color: #1e293b !important;
        border-color: #cbd5e1 !important;
    }
    .action-icon-btn.btn-restore:hover {
        background: #eff6ff !important;
        color: #2563eb !important;
        border-color: #93c5fd !important;
    }
    .action-icon-btn.btn-cloud:hover {
        background: #f0fdf4 !important;
        color: #16a34a !important;
        border-color: #86efac !important;
    }
    .action-icon-btn.btn-delete:hover {
        background: #fef2f2 !important;
        color: #dc2626 !important;
        border-color: #fca5a5 !important;
    }

    /* Restore Zone */
    .restore-zone-wrapper {
        display: flex;
        flex-direction: column;
        gap: 20px;
        max-width: 850px;
        margin: 0 auto;
    }
    .safety-alert-banner {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-left: 4px solid #f59e0b;
        border-radius: 8px;
        padding: 16px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .safety-alert-banner i {
        font-size: 24px;
        color: #d97706;
        margin-top: 2px;
    }
    .safety-alert-content h4 {
        margin: 0 0 4px 0;
        font-size: 14px;
        color: #92400e;
        font-weight: 700;
    }
    .safety-alert-content p {
        margin: 0;
        font-size: 12.5px;
        color: #b45309;
        line-height: 1.5;
    }

    .dropzone-box {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .dropzone-box:hover, .dropzone-box.dragover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .dropzone-icon {
        font-size: 48px;
        color: #94a3b8;
        margin-bottom: 12px;
    }
    .dropzone-box:hover .dropzone-icon {
        color: #2563eb;
    }
    .dropzone-title {
        font-size: 15px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }
    .dropzone-desc {
        font-size: 12.5px;
        color: #64748b;
        max-width: 450px;
        margin: 0 auto 16px auto;
    }

    .selected-file-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 16px;
    }
    .selected-file-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .selected-file-info i {
        font-size: 28px;
        color: #2563eb;
    }

    /* Settings Form */
    .settings-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        max-width: 900px;
    }
    @media (max-width: 768px) {
        .settings-form-grid {
            grid-template-columns: 1fr;
        }
    }
    .form-group-full {
        grid-column: 1 / -1;
    }
    .form-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-field label {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
    }
    .form-field input, .form-field select, .form-field textarea {
        height: 43px;
        border-radius: 7px;
        border: 1px solid #cbd5e1;
        padding: 0 14px;
        font-size: 12.5px;
        color: #334155;
        background: #ffffff;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .form-field textarea {
        height: 80px;
        padding: 10px 14px;
    }
    .form-field input:focus, .form-field select:focus, .form-field textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12);
    }
    .form-field small {
        font-size: 11px;
        color: #64748b;
    }

    /* Modal Overlay */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(2px);
        z-index: 9990;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .modal-box {
        background: #ffffff;
        border-radius: 12px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        animation: modalScaleIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalScaleIn {
        from { transform: scale(0.95); opacity: 0; }
        to   { transform: scale(1); opacity: 1; }
    }
    .modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-header h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .modal-header .btn-close {
        background: none;
        border: none;
        font-size: 20px;
        color: #94a3b8;
        cursor: pointer;
        padding: 4px;
        line-height: 1;
    }
    .modal-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .modal-footer {
        padding: 14px 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Scope Radio Cards */
    .scope-selector {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .scope-card {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 14px;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        transition: all 0.15s ease;
    }
    .scope-card:hover {
        border-color: #93c5fd;
        background: #f8fafc;
    }
    .scope-card.selected {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .scope-card input[type="radio"] {
        margin-top: 3px;
    }
    .scope-card-content h5 {
        margin: 0 0 2px 0;
        font-size: 13.5px;
        font-weight: 600;
        color: #1e293b;
    }
    .scope-card-content p {
        margin: 0;
        font-size: 12px;
        color: #64748b;
        line-height: 1.4;
    }

    /* Loading Full Size Layout */
    .loadingFullSizeLayout {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(4px);
        z-index: 99999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #ffffff;
    }
    .spinner-circle {
        width: 60px;
        height: 60px;
        border: 4px solid rgba(255, 255, 255, 0.25);
        border-top-color: #38bdf8;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-bottom: 20px;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

@include('admin::shared.header', ['header_name' => __('backup.title'), 'customClass' => 'headerInForm'])

<div class="backup-dashboard" x-data="backupApp()" x-cloak>
    {{-- Metric Cards Top Row --}}
    <div class="metric-grid">
        <div class="metric-card">
            <div class="metric-icon-wrap metric-icon-blue">
                <i class='bx bx-archive'></i>
            </div>
            <div class="metric-info">
                <span class="metric-label">{{ __('backup.metrics.total_backups') }}</span>
                <span class="metric-value">{{ $metrics['backup_count'] }}</span>
                <span class="metric-sub">{{ $metrics['formatted_backup_size'] }} {{ __('backup.metrics.backup_storage') }}</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon-wrap metric-icon-indigo">
                <i class='bx bx-data'></i>
            </div>
            <div class="metric-info">
                <span class="metric-label">{{ __('backup.metrics.database_size') }}</span>
                <span class="metric-value">{{ $metrics['database_size'] }}</span>
                <span class="metric-sub">{{ config('database.default') }} / {{ config('database.connections.' . config('database.default') . '.database') }}</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon-wrap metric-icon-purple">
                <i class='bx bx-images'></i>
            </div>
            <div class="metric-info">
                <span class="metric-label">{{ __('backup.metrics.uploads_size') }}</span>
                <span class="metric-value">{{ $metrics['uploads_size'] }}</span>
                <span class="metric-sub">{{ $metrics['uploads_count'] }} {{ __('backup.metrics.uploads_count') }}</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon-wrap {{ $metrics['cloud_configured'] ? 'metric-icon-emerald' : 'metric-icon-sky' }}">
                <i class='bx {{ $metrics['cloud_configured'] ? 'bx-cloud-check' : 'bx-cloud' }}'></i>
            </div>
            <div class="metric-info">
                <span class="metric-label">{{ __('backup.metrics.cloud_status') }}</span>
                <span class="metric-value" style="font-size: 15px; font-weight: 600;">
                    @if($metrics['cloud_configured'])
                        <span style="color: #059669; display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                            {{ __('backup.metrics.cloud_connected') }}
                        </span>
                    @else
                        <span style="color: #64748b;">
                            {{ __('backup.metrics.cloud_unconfigured') }}
                        </span>
                    @endif
                </span>
                <span class="metric-sub">{{ $setting->s3_bucket ? 'Bucket: ' . $setting->s3_bucket : __('backup.cloud_table.not_configured') }}</span>
            </div>
        </div>
    </div>

    {{-- Disk Usage Monitoring Section --}}
    <div class="disk-usage-card">
        <div class="disk-usage-header">
            <div class="disk-usage-title-wrap">
                <h4>
                    <i class='bx {{ $metrics["is_local_machine"] ? "bx-laptop" : "bx-hdd" }}' style="color: #2563eb;"></i>
                    <span>{{ $metrics['is_local_machine'] ? __('backup.disk_usage.local_title') : __('backup.disk_usage.server_title') }}</span>
                    <span style="font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 12px; background: {{ $metrics['is_local_machine'] ? '#eff6ff' : '#f1f5f9' }}; color: {{ $metrics['is_local_machine'] ? '#1d4ed8' : '#334155' }}; border: 1px solid {{ $metrics['is_local_machine'] ? '#bfdbfe' : '#cbd5e1' }}; display: inline-flex; align-items: center; gap: 4px;">
                        <i class='bx {{ $metrics["is_local_machine"] ? "bx-desktop" : "bx-server" }}'></i>
                        {{ $metrics['environment_name'] }}: {{ $metrics['host_name'] }} ({{ $metrics['os_family'] }})
                    </span>
                </h4>
                <p>{{ $metrics['is_local_machine'] ? __('backup.disk_usage.local_subtitle') : __('backup.disk_usage.server_subtitle') }}</p>
            </div>
            <div>
                @if($metrics['disk_status'] === 'healthy')
                    <span class="health-badge health-ok">
                        <i class='bx bx-check-circle'></i>
                        {{ __('backup.disk_usage.status_healthy') }} ({{ $metrics['disk_free_percentage'] }}% {{ __('backup.disk_usage.free_share') }})
                    </span>
                @elseif($metrics['disk_status'] === 'warning')
                    <span class="health-badge health-warn">
                        <i class='bx bx-error'></i>
                        {{ __('backup.disk_usage.status_warning') }} ({{ $metrics['disk_used_percentage'] }}% {{ __('backup.disk_usage.used_space') }})
                    </span>
                @else
                    <span class="health-badge health-danger">
                        <i class='bx bx-error-circle'></i>
                        {{ __('backup.disk_usage.status_critical') }} ({{ $metrics['disk_used_percentage'] }}% {{ __('backup.disk_usage.used_space') }})
                    </span>
                @endif
            </div>
        </div>

        {{-- 3 Key Stat Highlight Boxes --}}
        <div class="disk-stats-row">
            <div class="disk-stat-box">
                <i class='bx bx-pie-chart-alt-2' style="color: #4f46e5;"></i>
                <div class="stat-meta">
                    <span class="stat-label">{{ __('backup.disk_usage.total_capacity') }}</span>
                    <span class="stat-val">{{ $metrics['disk_total_formatted'] }}</span>
                    <span class="stat-sub">{{ $metrics['is_local_machine'] ? __('backup.disk_usage.local_capacity_sub') : __('backup.disk_usage.server_capacity_sub') }}</span>
                </div>
            </div>

            <div class="disk-stat-box">
                <i class='bx bx-coin-stack' style="color: #2563eb;"></i>
                <div class="stat-meta">
                    <span class="stat-label">{{ __('backup.disk_usage.used_space') }}</span>
                    <span class="stat-val">{{ $metrics['disk_used_formatted'] }} <span style="font-size: 13px; font-weight: 500; color: #64748b;">({{ $metrics['disk_used_percentage'] }}%)</span></span>
                    <span class="stat-sub">{{ $metrics['is_local_machine'] ? __('backup.disk_usage.local_used_sub') : __('backup.disk_usage.server_used_sub') }}</span>
                </div>
            </div>

            <div class="disk-stat-box">
                <i class='bx bx-folder-check' style="color: #059669;"></i>
                <div class="stat-meta">
                    <span class="stat-label">{{ __('backup.disk_usage.available_space') }}</span>
                    <span class="stat-val">{{ $metrics['disk_free_formatted'] }} <span style="font-size: 13px; font-weight: 500; color: #059669;">({{ $metrics['disk_free_percentage'] }}%)</span></span>
                    <span class="stat-sub">{{ $metrics['is_local_machine'] ? __('backup.disk_usage.local_free_sub') : __('backup.disk_usage.server_free_sub') }}</span>
                </div>
            </div>
        </div>

        {{-- Multi-Segment Usage Bar --}}
        <div style="display: flex; flex-direction: column; gap: 6px;">
            <div class="disk-progress-track" title="Used: {{ $metrics['disk_used_formatted'] }} ({{ $metrics['disk_used_percentage'] }}%) | Free: {{ $metrics['disk_free_formatted'] }} ({{ $metrics['disk_free_percentage'] }}%)">
                {{-- DB --}}
                @if($metrics['disk_db_percent'] > 0)
                    <div class="disk-segment seg-db" style="width: {{ max(0.5, $metrics['disk_db_percent']) }}%;" title="Database: {{ $metrics['database_size'] }} ({{ $metrics['disk_db_percent'] }}%)"></div>
                @endif
                {{-- Uploads --}}
                @if($metrics['disk_uploads_percent'] > 0)
                    <div class="disk-segment seg-uploads" style="width: {{ max(0.5, $metrics['disk_uploads_percent']) }}%;" title="Uploads: {{ $metrics['uploads_size'] }} ({{ $metrics['disk_uploads_percent'] }}%)"></div>
                @endif
                {{-- Backups --}}
                @if($metrics['disk_backups_percent'] > 0)
                    <div class="disk-segment seg-backups" style="width: {{ max(0.5, $metrics['disk_backups_percent']) }}%;" title="Backups: {{ $metrics['formatted_backup_size'] }} ({{ $metrics['disk_backups_percent'] }}%)"></div>
                @endif
                {{-- Other --}}
                @if($metrics['disk_other_percent'] > 0)
                    <div class="disk-segment seg-other" style="width: {{ max(0.5, $metrics['disk_other_percent']) }}%;" title="Other System Files: {{ $metrics['disk_other_formatted'] }} ({{ $metrics['disk_other_percent'] }}%)"></div>
                @endif
                {{-- Free space fill --}}
                <div class="disk-segment seg-free" style="flex: 1;" title="Free Space: {{ $metrics['disk_free_formatted'] }} ({{ $metrics['disk_free_percentage'] }}%)"></div>
            </div>
        </div>

        {{-- Legend Breakdown Grid --}}
        <div class="disk-legend-grid">
            <div class="legend-item">
                <span class="legend-dot seg-db"></span>
                <span><strong>{{ $metrics['database_size'] }}</strong> ({{ $metrics['disk_db_percent'] }}%) - {{ __('backup.disk_usage.database_share') }}</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot seg-uploads"></span>
                <span><strong>{{ $metrics['uploads_size'] }}</strong> ({{ $metrics['disk_uploads_percent'] }}%) - {{ __('backup.disk_usage.uploads_share') }}</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot seg-backups"></span>
                <span><strong>{{ $metrics['formatted_backup_size'] }}</strong> ({{ $metrics['disk_backups_percent'] }}%) - {{ __('backup.disk_usage.backups_share') }}</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot seg-other"></span>
                <span><strong>{{ $metrics['disk_other_formatted'] }}</strong> ({{ $metrics['disk_other_percent'] }}%) - {{ __('backup.disk_usage.other_share') }}</span>
            </div>
            <div class="legend-item">
                <span class="legend-dot seg-free" style="border: 1px solid #cbd5e1;"></span>
                <span><strong>{{ $metrics['disk_free_formatted'] }}</strong> ({{ $metrics['disk_free_percentage'] }}%) - {{ __('backup.disk_usage.free_share') }}</span>
            </div>
        </div>

        @if(!$metrics['is_local_machine'])
            <div style="margin-top: 10px; padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 12px; color: #166534; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class='bx bx-check-shield' style="font-size: 18px; color: #16a34a; flex-shrink: 0;"></i>
                    <span>
                        <strong>Hosting Plan Storage:</strong> Dynamically calibrated to your <strong>{{ $metrics['disk_total_formatted'] }}</strong> hosting plan limit on server <code>{{ $metrics['host_name'] }}</code>. Measuring actual storage consumed by your POS application files and database.
                    </span>
                </div>
                <span style="font-weight: 600; color: #15803d; font-size: 11.5px;">
                    {{ $metrics['disk_free_formatted'] }} Remaining
                </span>
            </div>
        @endif
    </div>

    {{-- Main Tabs Card --}}
    <div class="backup-content-card">
        <div class="backup-tabs-header">
            <div class="backup-tabs">
                <button type="button" class="backup-tab-btn" :class="{ 'active': activeTab === 'local' }" @click="activeTab = 'local'">
                    <i class='bx bx-server'></i>
                    {{ __('backup.tabs.local_backups') }}
                    <span class="badge-pill">{{ count($localBackups) }}</span>
                </button>
                <button type="button" class="backup-tab-btn" :class="{ 'active': activeTab === 'cloud' }" @click="activeTab = 'cloud'">
                    <i class='bx bx-cloud'></i>
                    {{ __('backup.tabs.cloud_backups') }}
                    <span class="badge-pill">{{ count($cloudBackups) }}</span>
                </button>
                <button type="button" class="backup-tab-btn" :class="{ 'active': activeTab === 'restore' }" @click="activeTab = 'restore'">
                    <i class='bx bx-history'></i>
                    {{ __('backup.tabs.restore') }}
                </button>
                <button type="button" class="backup-tab-btn" :class="{ 'active': activeTab === 'settings' }" @click="activeTab = 'settings'">
                    <i class='bx bx-cog'></i>
                    {{ __('backup.tabs.settings') }}
                </button>
            </div>

            <div class="backup-header-actions">
                <button type="button" class="btn-action-outline" @click="window.location.reload()">
                    <i class='bx bx-refresh'></i>
                    {{ __('backup.actions.refresh') }}
                </button>
                <button type="button" class="btn-action-primary" @click="openCreateModal()">
                    <i class='bx bx-plus-circle'></i>
                    {{ __('backup.actions.create_backup') }}
                </button>
            </div>
        </div>

        {{-- Tab 1: Local Server Backups --}}
        <div class="backup-tab-pane" x-show="activeTab === 'local'">
            @if(count($localBackups) === 0)
                <div style="padding: 48px 20px; text-align: center; color: #64748b;">
                    <i class='bx bx-archive' style="font-size: 52px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                    <p style="font-size: 14px; margin: 0 0 16px 0;">{{ __('backup.table.no_backups') }}</p>
                    <button type="button" class="btn-action-primary" @click="openCreateModal()">
                        <i class='bx bx-plus-circle'></i> {{ __('backup.actions.create_backup') }}
                    </button>
                </div>
            @else
                <div class="backup-table-wrapper">
                    <table class="backup-table">
                        <thead>
                            <tr>
                                <th>{{ __('backup.table.name') }}</th>
                                <th>{{ __('backup.table.type') }}</th>
                                <th>{{ __('backup.table.size') }}</th>
                                <th>{{ __('backup.table.tables_files') }}</th>
                                <th>{{ __('backup.table.created_at') }}</th>
                                <th>{{ __('backup.table.cloud_status') }}</th>
                                <th style="text-align: right;">{{ __('backup.table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localBackups as $backup)
                                <tr>
                                    <td>
                                        <div style="display: flex; flex-direction: column;">
                                            <strong style="color: #1e293b; font-size: 13.5px;">{{ $backup->name }}</strong>
                                            <span style="font-family: monospace; font-size: 11.5px; color: #64748b;">{{ $backup->filename }}</span>
                                            @if($backup->notes)
                                                <small style="color: #94a3b8; font-size: 11px; margin-top: 2px;">{{ $backup->notes }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($backup->type === 'full')
                                            <span class="status-badge badge-full">
                                                <i class='bx bx-check-circle'></i> {{ __('backup.types.full') }}
                                            </span>
                                        @else
                                            <span class="status-badge badge-db">
                                                <i class='bx bx-data'></i> {{ __('backup.types.database') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: #1e293b;">{{ $backup->formatted_size }}</span>
                                    </td>
                                    <td>
                                        <span style="font-size: 12px; color: #475569;">
                                            {{ $backup->tables_count }} tables
                                            @if($backup->files_count > 0)
                                                / {{ $backup->files_count }} files
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; font-size: 12px;">
                                            <span>{{ $backup->created_at->format('Y-m-d H:i:s') }}</span>
                                            <span style="color: #94a3b8; font-size: 11px;">{{ $backup->created_at->diffForHumans() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($backup->cloud_status === 'uploaded')
                                            <span class="status-badge badge-cloud-ok" title="Uploaded at {{ $backup->cloud_uploaded_at }}">
                                                <i class='bx bx-cloud-check'></i> Synced to Cloud
                                            </span>
                                        @elseif($backup->cloud_status === 'failed')
                                            <span class="status-badge badge-cloud-failed" title="Upload failed">
                                                <i class='bx bx-cloud-lightning'></i> Failed
                                            </span>
                                        @else
                                            <span class="status-badge badge-cloud-none">
                                                <i class='bx bx-hdd'></i> Local Only
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="table-actions" style="justify-content: flex-end;">
                                            {{-- Download to local PC --}}
                                            <a href="{{ route('admin-backup-download', $backup->id) }}" class="action-icon-btn" title="{{ __('backup.actions.download') }}">
                                                <i class='bx bx-download'></i>
                                            </a>

                                            {{-- Restore system from this backup --}}
                                            <button type="button" class="action-icon-btn btn-restore" @click="confirmRestore('local', {{ $backup->id }}, '{{ $backup->name }} ({{ $backup->filename }})')" title="{{ __('backup.actions.restore') }}">
                                                <i class='bx bx-history'></i>
                                            </button>

                                            {{-- Upload to Cloud Storage --}}
                                            @if($setting->isCloudConfigured() && $backup->cloud_status !== 'uploaded')
                                                <button type="button" class="action-icon-btn btn-cloud" @click="uploadToCloud({{ $backup->id }})" title="{{ __('backup.actions.upload_to_cloud') }}">
                                                    <i class='bx bx-cloud-upload'></i>
                                                </button>
                                            @endif

                                            {{-- Delete --}}
                                            <button type="button" class="action-icon-btn btn-delete" @click="deleteLocalBackup({{ $backup->id }}, '{{ $backup->filename }}')" title="{{ __('backup.actions.delete') }}">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tab 2: Cloud Storage Backups --}}
        <div class="backup-tab-pane" x-show="activeTab === 'cloud'">
            @if(!$setting->isCloudConfigured())
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 40px 20px; text-align: center;">
                    <i class='bx bx-cloud' style="font-size: 54px; color: #94a3b8; margin-bottom: 12px; display: block;"></i>
                    <h4 style="margin: 0 0 8px 0; color: #334155; font-size: 16px;">{{ __('backup.cloud_table.not_configured') }}</h4>
                    <p style="margin: 0 0 16px 0; color: #64748b; font-size: 13px;">Configure your AWS S3, Cloudflare R2, MinIO, or DigitalOcean Spaces credentials to enable automated off-site backups.</p>
                    <button type="button" class="btn-action-primary" @click="activeTab = 'settings'">
                        <i class='bx bx-cog'></i> Configure Cloud Settings
                    </button>
                </div>
            @elseif($cloudError)
                <div style="background: #fef2f2; border: 1px solid #fee2e2; border-left: 4px solid #ef4444; border-radius: 8px; padding: 18px; color: #991b1b;">
                    <strong style="display: block; font-size: 14px; margin-bottom: 4px;">Cloud Connection Issue</strong>
                    <span style="font-size: 13px;">{{ $cloudError }}</span>
                    <div style="margin-top: 12px;">
                        <button type="button" class="btn-action-outline" @click="activeTab = 'settings'">
                            Check Cloud Credentials
                        </button>
                    </div>
                </div>
            @elseif(count($cloudBackups) === 0)
                <div style="padding: 48px 20px; text-align: center; color: #64748b;">
                    <i class='bx bx-cloud' style="font-size: 52px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                    <p style="font-size: 14px; margin: 0 0 16px 0;">{{ __('backup.cloud_table.no_cloud') }}</p>
                    <button type="button" class="btn-action-primary" @click="openCreateModal(true)">
                        <i class='bx bx-cloud-upload'></i> Create & Upload Backup to Cloud
                    </button>
                </div>
            @else
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #166534;">
                        <i class='bx bx-cloud-check' style="font-size: 20px;"></i>
                        <span>Connected to Bucket: <strong>{{ $setting->s3_bucket }}</strong> (Folder: <code>{{ $setting->s3_path_prefix }}</code>)</span>
                    </div>
                    <span style="font-size: 12px; color: #15803d; font-weight: 600;">{{ count($cloudBackups) }} objects</span>
                </div>

                <div class="backup-table-wrapper">
                    <table class="backup-table">
                        <thead>
                            <tr>
                                <th>{{ __('backup.cloud_table.filename') }}</th>
                                <th>{{ __('backup.cloud_table.size') }}</th>
                                <th>{{ __('backup.cloud_table.last_modified') }}</th>
                                <th style="text-align: right;">{{ __('backup.cloud_table.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cloudBackups as $cb)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <i class='bx bxs-file-archive' style="font-size: 24px; color: #0284c7;"></i>
                                            <span style="font-weight: 600; color: #1e293b; font-family: monospace;">{{ $cb['filename'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: #334155;">{{ $cb['formatted_size'] }}</span>
                                    </td>
                                    <td>
                                        <span style="font-size: 12px; color: #64748b;">{{ $cb['last_modified'] }}</span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="table-actions" style="justify-content: flex-end;">
                                            {{-- Restore directly from Cloud --}}
                                            <button type="button" class="action-icon-btn btn-restore" @click="confirmRestore('cloud', '{{ $cb['filename'] }}', 'Cloud Backup: {{ $cb['filename'] }}')" title="Restore from Cloud">
                                                <i class='bx bx-history'></i>
                                            </button>

                                            {{-- Download from Cloud to local PC --}}
                                            <a href="{{ route('admin-backup-download-cloud', ['filename' => $cb['filename']]) }}" class="action-icon-btn" title="{{ __('backup.actions.download') }}">
                                                <i class='bx bx-download'></i>
                                            </a>

                                            {{-- Delete from Cloud --}}
                                            <button type="button" class="action-icon-btn btn-delete" @click="deleteCloudBackup('{{ $cb['filename'] }}')" title="{{ __('backup.actions.delete') }}">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tab 3: Restore Center --}}
        <div class="backup-tab-pane" x-show="activeTab === 'restore'">
            <div class="restore-zone-wrapper">
                {{-- Safety notice --}}
                <div class="safety-alert-banner">
                    <i class='bx bx-error-circle'></i>
                    <div class="safety-alert-content">
                        <h4>{{ __('backup.restore_section.warning_title') }}</h4>
                        <p>{{ __('backup.restore_section.warning_text') }}</p>
                    </div>
                </div>

                {{-- Option 1: Upload from local computer --}}
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px;">
                    <h4 style="margin: 0 0 6px 0; font-size: 15px; color: #1e293b; font-weight: 700;">
                        <i class='bx bx-laptop' style="color: #2563eb; margin-right: 4px;"></i>
                        {{ __('backup.restore_section.upload_box_title') }}
                    </h4>
                    <p style="margin: 0 0 18px 0; font-size: 12.5px; color: #64748b;">
                        {{ __('backup.restore_section.upload_box_desc') }}
                    </p>

                    <div class="dropzone-box" :class="{ 'dragover': isDragging }"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleFileDrop($event)"
                         @click="$refs.fileInput.click()">
                        <i class='bx bx-cloud-upload dropzone-icon'></i>
                        <div class="dropzone-title">Click to select backup file from your computer</div>
                        <div class="dropzone-desc">Supports full system backup packages (<code>.zip</code>) or raw database dump scripts (<code>.sql</code>). Max 512 MB.</div>
                        <button type="button" class="btn-action-outline">
                            <i class='bx bx-folder-open'></i> {{ __('backup.actions.browse_file') }}
                        </button>
                        <input type="file" x-ref="fileInput" accept=".zip,.sql" style="display: none;" @change="handleFileSelect($event)">
                    </div>

                    {{-- Selected file preview --}}
                    <template x-if="selectedFile">
                        <div class="selected-file-card">
                            <div class="selected-file-info">
                                <i class='bx bxs-file-archive'></i>
                                <div>
                                    <strong style="color: #1e293b; font-size: 13.5px;" x-text="selectedFile.name"></strong>
                                    <div style="font-size: 11.5px; color: #64748b;">
                                        <span x-text="formatBytes(selectedFile.size)"></span> • Ready for restore
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-action-outline" style="color: #ef4444; border-color: #fca5a5;" @click="selectedFile = null">
                                <i class='bx bx-x'></i> Remove
                            </button>
                        </div>
                    </template>

                    {{-- Options & Action button --}}
                    <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 14px;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; cursor: pointer;">
                            <input type="checkbox" x-model="createPreRestoreSnapshot" style="width: 16px; height: 16px; accent-color: #2563eb;">
                            <span>{{ __('backup.restore_section.snapshot_option') }}</span>
                        </label>

                        <div>
                            <button type="button" class="btn-action-primary" :disabled="!selectedFile"
                                    :style="!selectedFile ? 'opacity: 0.5; cursor: not-allowed;' : ''"
                                    @click="startUploadRestore()">
                                <i class='bx bx-play-circle'></i> {{ __('backup.restore_section.start_restore') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 4: Cloud Storage & Settings --}}
        <div class="backup-tab-pane" x-show="activeTab === 'settings'">
            <div style="max-width: 900px; margin: 0 auto;">
                <div style="margin-bottom: 20px;">
                    <h3 style="margin: 0 0 6px 0; font-size: 16px; color: #1e293b; font-weight: 700;">{{ __('backup.settings.title') }}</h3>
                    <p style="margin: 0; font-size: 13px; color: #64748b;">{{ __('backup.settings.subtitle') }}</p>
                </div>

                <form @submit.prevent="saveSettings()">
                    <div class="settings-form-grid">
                        {{-- Provider selection --}}
                        <div class="form-field form-group-full">
                            <label>{{ __('backup.settings.provider') }}</label>
                            <select x-model="config.cloud_driver" @change="onProviderChange()">
                                <option value="s3">Amazon Web Services (AWS S3)</option>
                                <option value="r2">Cloudflare R2</option>
                                <option value="minio">MinIO (Self-Hosted Object Storage)</option>
                                <option value="digitalocean">DigitalOcean Spaces</option>
                                <option value="hostinger">Hostinger / Wasabi / Backblaze S3</option>
                                <option value="custom">Custom S3-Compatible Endpoint</option>
                            </select>
                        </div>

                        {{-- Endpoint URL --}}
                        <div class="form-field form-group-full">
                            <label>{{ __('backup.settings.endpoint') }}</label>
                            <input type="text" x-model="config.s3_endpoint" placeholder="https://<account-id>.r2.cloudflarestorage.com or https://ams3.digitaloceanspaces.com">
                            <small>{{ __('backup.settings.endpoint_hint') }}</small>
                        </div>

                        {{-- Region --}}
                        <div class="form-field">
                            <label>{{ __('backup.settings.region') }} <span>*</span></label>
                            <input type="text" x-model="config.s3_region" required placeholder="e.g. us-east-1, auto, ap-southeast-1">
                        </div>

                        {{-- Bucket Name --}}
                        <div class="form-field">
                            <label>{{ __('backup.settings.bucket') }} <span>*</span></label>
                            <input type="text" x-model="config.s3_bucket" required placeholder="e.g. my-pos-backups">
                        </div>

                        {{-- Access Key --}}
                        <div class="form-field">
                            <label>{{ __('backup.settings.access_key') }} <span>*</span></label>
                            <input type="text" x-model="config.s3_access_key" required placeholder="AKIAIOSFODNN7EXAMPLE">
                        </div>

                        {{-- Secret Key --}}
                        <div class="form-field">
                            <label>{{ __('backup.settings.secret_key') }}</label>
                            <div style="position: relative;">
                                <input :type="showSecretKey ? 'text' : 'password'" x-model="config.s3_secret_key" placeholder="••••••••••••••••••••••••">
                                <button type="button" @click="showSecretKey = !showSecretKey" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
                                    <i class='bx' :class="showSecretKey ? 'bx-hide' : 'bx-show'"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Path Prefix --}}
                        <div class="form-field">
                            <label>{{ __('backup.settings.path_prefix') }}</label>
                            <input type="text" x-model="config.s3_path_prefix" placeholder="backups/">
                            <small>{{ __('backup.settings.path_prefix_hint') }}</small>
                        </div>

                        {{-- Max Retention --}}
                        <div class="form-field">
                            <label>{{ __('backup.settings.retention') }}</label>
                            <input type="number" x-model.number="config.max_local_backups" min="1" max="100">
                            <small>{{ __('backup.settings.retention_hint') }}</small>
                        </div>

                        {{-- Hosting Plan Storage Quota --}}
                        <div class="form-field">
                            <label>Hosting Plan Storage Quota (GB)</label>
                            <input type="number" x-model.number="config.disk_quota_gb" min="1" max="50000" placeholder="50">
                            <small>Storage limit of your hosting plan (e.g. 50 GB for Hostinger). Default: 50 GB.</small>
                        </div>

                        {{-- Toggles --}}
                        <div class="form-group-full" style="display: flex; flex-direction: column; gap: 12px; margin-top: 6px;">
                            <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; cursor: pointer;">
                                <input type="checkbox" x-model="config.s3_use_path_style" style="width: 16px; height: 16px; accent-color: #2563eb;">
                                <span>{{ __('backup.settings.use_path_style') }}</span>
                            </label>

                            <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; cursor: pointer;">
                                <input type="checkbox" x-model="config.auto_upload_cloud" style="width: 16px; height: 16px; accent-color: #2563eb;">
                                <span>{{ __('backup.settings.auto_upload') }} - <span style="color: #64748b;">{{ __('backup.settings.auto_upload_hint') }}</span></span>
                            </label>
                        </div>
                    </div>

                    {{-- Form Footer Buttons --}}
                    <div style="margin-top: 24px; display: flex; align-items: center; gap: 12px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                        <button type="button" class="btn-action-outline" @click="testCloudConnection()" :disabled="testingConnection">
                            <i class='bx bx-check-shield'></i>
                            <span x-text="testingConnection ? 'Testing Connection...' : '{{ __('backup.actions.test_connection') }}'"></span>
                        </button>

                        <button type="submit" class="btn-action-primary">
                            <i class='bx bx-save'></i> {{ __('backup.actions.save_settings') }}
                        </button>

                        <span x-show="testStatus" :style="testStatus?.success ? 'color: #16a34a;' : 'color: #dc2626;'" style="font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                            <i class='bx' :class="testStatus?.success ? 'bx-check-circle' : 'bx-x-circle'"></i>
                            <span x-text="testStatus?.message"></span>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Create Backup Modal --}}
    <div class="modal-backdrop" x-show="showCreateModal" style="display: none;" @click.self="showCreateModal = false">
        <div class="modal-box">
            <div class="modal-header">
                <h3><i class='bx bx-archive-in'></i> {{ __('backup.create_modal.title') }}</h3>
                <button type="button" class="btn-close" @click="showCreateModal = false">&times;</button>
            </div>
            <div class="modal-body">
                <div class="scope-selector">
                    <label class="scope-card" :class="{ 'selected': createParams.type === 'full' }">
                        <input type="radio" name="scope_type" value="full" x-model="createParams.type">
                        <div class="scope-card-content">
                            <h5>{{ __('backup.types.full') }}</h5>
                            <p>{{ __('backup.create_modal.type_full') }}</p>
                        </div>
                    </label>

                    <label class="scope-card" :class="{ 'selected': createParams.type === 'database' }">
                        <input type="radio" name="scope_type" value="database" x-model="createParams.type">
                        <div class="scope-card-content">
                            <h5>{{ __('backup.types.database') }}</h5>
                            <p>{{ __('backup.create_modal.type_db') }}</p>
                        </div>
                    </label>
                </div>

                <div class="form-field">
                    <label>{{ __('backup.create_modal.name_label') }}</label>
                    <input type="text" x-model="createParams.name" placeholder="{{ __('backup.create_modal.name_placeholder') }}">
                </div>

                <div class="form-field">
                    <label>{{ __('backup.create_modal.notes_label') }}</label>
                    <textarea x-model="createParams.notes" placeholder="{{ __('backup.create_modal.notes_placeholder') }}"></textarea>
                </div>

                @if($setting->isCloudConfigured())
                    <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; cursor: pointer;">
                        <input type="checkbox" x-model="createParams.upload_cloud" style="width: 16px; height: 16px; accent-color: #2563eb;">
                        <span>{{ __('backup.create_modal.upload_cloud_label') }}</span>
                    </label>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action-outline" @click="showCreateModal = false">{{ __('backup.actions.cancel') }}</button>
                <button type="button" class="btn-action-primary" @click="executeCreateBackup()">
                    <i class='bx bx-play'></i> {{ __('backup.create_modal.submit') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Confirm Restore Modal --}}
    <div class="modal-backdrop" x-show="showRestoreModal" style="display: none;" @click.self="showRestoreModal = false">
        <div class="modal-box">
            <div class="modal-header" style="background: #fef2f2; border-bottom-color: #fee2e2;">
                <h3 style="color: #991b1b;"><i class='bx bx-error-alt' style="color: #dc2626;"></i> {{ __('backup.confirm_modal.title') }}</h3>
                <button type="button" class="btn-close" @click="showRestoreModal = false">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 13.5px; color: #334155; margin: 0; line-height: 1.5;">
                    {{ __('backup.confirm_modal.message') }}:<br>
                    <strong style="color: #1e293b; font-size: 14px; font-family: monospace; display: block; margin: 8px 0; padding: 8px 12px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;" x-text="restoreTarget?.label"></strong>
                </p>
                <p style="font-size: 12.5px; color: #dc2626; margin: 0; line-height: 1.4; background: #fff1f2; padding: 10px; border-radius: 6px;">
                    {{ __('backup.confirm_modal.consequence') }}
                </p>

                <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; cursor: pointer; margin-top: 4px;">
                    <input type="checkbox" x-model="createPreRestoreSnapshot" style="width: 16px; height: 16px; accent-color: #2563eb;">
                    <span>{{ __('backup.restore_section.snapshot_option') }}</span>
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action-outline" @click="showRestoreModal = false">{{ __('backup.actions.cancel') }}</button>
                <button type="button" class="btn-action-primary" style="background: #dc2626;" @click="executeRestore()">
                    <i class='bx bx-check-circle'></i> {{ __('backup.actions.confirm_restore') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Full Size Loading Overlay --}}
    <div class="loadingFullSizeLayout" x-show="loading" style="display: none;">
        <div class="spinner-circle"></div>
        <h3 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #ffffff;" x-text="loadingTitle"></h3>
        <p style="margin: 0; font-size: 14px; color: #94a3b8;" x-text="loadingSubtitle"></p>
    </div>
</div>

<script>
    function backupApp() {
        return {
            activeTab: 'local',
            loading: false,
            loadingTitle: '',
            loadingSubtitle: '',

            // Modals
            showCreateModal: false,
            createParams: {
                type: 'full',
                name: '',
                notes: '',
                upload_cloud: false
            },

            showRestoreModal: false,
            restoreTarget: null,
            createPreRestoreSnapshot: true,

            // File upload restore
            selectedFile: null,
            isDragging: false,

            // Settings
            config: {
                cloud_driver: @json($setting->cloud_driver ?? 's3'),
                s3_endpoint: @json($setting->s3_endpoint ?? ''),
                s3_region: @json($setting->s3_region ?? 'us-east-1'),
                s3_bucket: @json($setting->s3_bucket ?? ''),
                s3_access_key: @json($setting->s3_access_key ?? ''),
                s3_secret_key: @json($setting->s3_secret_key ? '••••••••••••••••••••••••' : ''),
                s3_path_prefix: @json($setting->s3_path_prefix ?? 'backups/'),
                s3_use_path_style: @json((bool)$setting->s3_use_path_style),
                auto_upload_cloud: @json((bool)$setting->auto_upload_cloud),
                max_local_backups: @json($setting->max_local_backups ?? 15),
                disk_quota_gb: @json($setting->disk_quota_gb ?? 50),
            },
            showSecretKey: false,
            testingConnection: false,
            testStatus: null,

            openCreateModal(uploadCloud = false) {
                this.createParams = {
                    type: 'full',
                    name: '',
                    notes: '',
                    upload_cloud: uploadCloud
                };
                this.showCreateModal = true;
            },

            executeCreateBackup() {
                this.showCreateModal = false;
                this.loading = true;
                this.loadingTitle = 'Creating System Backup...';
                this.loadingSubtitle = 'Dumping database and packaging system files. Please wait.';

                fetch('{{ route("admin-backup-create") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.createParams)
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.status === 'success') {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.success({ title: 'Success', message: data.message, position: 'topCenter' });
                        }
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({ title: 'Error', message: data.message || 'Backup creation failed', position: 'topCenter' });
                        }
                    }
                })
                .catch(err => {
                    this.loading = false;
                    if (typeof iziToast !== 'undefined') {
                        iziToast.error({ title: 'Error', message: err.message || 'Request failed', position: 'topCenter' });
                    }
                });
            },

            uploadToCloud(id) {
                this.loading = true;
                this.loadingTitle = 'Uploading to Cloud Storage...';
                this.loadingSubtitle = 'Transferring backup package to cloud bucket. Please do not close this window.';

                fetch('{{ url("admin/backup/upload-cloud") }}/' + id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.status === 'success') {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.success({ title: 'Success', message: data.message, position: 'topCenter' });
                        }
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({ title: 'Error', message: data.message || 'Upload failed', position: 'topCenter' });
                        }
                    }
                })
                .catch(err => {
                    this.loading = false;
                    if (typeof iziToast !== 'undefined') {
                        iziToast.error({ title: 'Error', message: err.message || 'Upload failed', position: 'topCenter' });
                    }
                });
            },

            confirmRestore(source, idOrFilename, label) {
                this.restoreTarget = {
                    source: source, // 'local', 'cloud', 'upload'
                    idOrFilename: idOrFilename,
                    label: label
                };
                this.showRestoreModal = true;
            },

            executeRestore() {
                this.showRestoreModal = false;
                this.loading = true;
                this.loadingTitle = 'Restoring System Data...';
                this.loadingSubtitle = 'Executing database transactions and restoring application files. Please do not refresh.';

                let url = '';
                let payload = { snapshot: this.createPreRestoreSnapshot };

                if (this.restoreTarget.source === 'local') {
                    url = '{{ url("admin/backup/restore-local") }}/' + this.restoreTarget.idOrFilename;
                } else if (this.restoreTarget.source === 'cloud') {
                    url = '{{ route("admin-backup-restore-cloud") }}';
                    payload.filename = this.restoreTarget.idOrFilename;
                }

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.status === 'success') {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.success({ title: 'Restore Complete', message: data.message, position: 'topCenter', timeout: 5000 });
                        }
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({ title: 'Restore Error', message: data.message || 'Restore failed', position: 'topCenter' });
                        }
                    }
                })
                .catch(err => {
                    this.loading = false;
                    if (typeof iziToast !== 'undefined') {
                        iziToast.error({ title: 'Error', message: err.message || 'Restore request failed', position: 'topCenter' });
                    }
                });
            },

            // File Selection
            handleFileSelect(event) {
                const files = event.target.files;
                if (files.length > 0) {
                    this.selectedFile = files[0];
                }
            },

            handleFileDrop(event) {
                this.isDragging = false;
                const files = event.dataTransfer.files;
                if (files.length > 0) {
                    const ext = files[0].name.split('.').pop().toLowerCase();
                    if (['zip', 'sql'].includes(ext)) {
                        this.selectedFile = files[0];
                    } else {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.warning({ title: 'Invalid File', message: 'Only .zip or .sql files are allowed.', position: 'topCenter' });
                        }
                    }
                }
            },

            startUploadRestore() {
                if (!this.selectedFile) return;

                if (!confirm('Are you sure you want to restore system data from ' + this.selectedFile.name + '? Current database and files will be updated.')) {
                    return;
                }

                this.loading = true;
                this.loadingTitle = 'Uploading & Restoring System...';
                this.loadingSubtitle = 'Transferring file from your computer and executing restore. Please wait.';

                const formData = new FormData();
                formData.append('backup_file', this.selectedFile);
                formData.append('snapshot', this.createPreRestoreSnapshot ? '1' : '0');

                fetch('{{ route("admin-backup-restore-upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.status === 'success') {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.success({ title: 'Restore Complete', message: data.message, position: 'topCenter', timeout: 5000 });
                        }
                        this.selectedFile = null;
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({ title: 'Restore Failed', message: data.message || 'Error occurred during restore', position: 'topCenter' });
                        }
                    }
                })
                .catch(err => {
                    this.loading = false;
                    if (typeof iziToast !== 'undefined') {
                        iziToast.error({ title: 'Upload Error', message: err.message || 'File upload failed', position: 'topCenter' });
                    }
                });
            },

            deleteLocalBackup(id, filename) {
                if (!confirm('Are you sure you want to delete backup "' + filename + '"?')) {
                    return;
                }

                this.loading = true;
                this.loadingTitle = 'Deleting Backup...';

                fetch('{{ url("admin/backup/delete") }}/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.status === 'success') {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.success({ title: 'Deleted', message: data.message, position: 'topCenter' });
                        }
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({ title: 'Error', message: data.message, position: 'topCenter' });
                        }
                    }
                })
                .catch(err => {
                    this.loading = false;
                });
            },

            deleteCloudBackup(filename) {
                if (!confirm('Are you sure you want to delete "' + filename + '" from Cloud Storage?')) {
                    return;
                }

                this.loading = true;
                this.loadingTitle = 'Deleting Cloud Backup...';

                fetch('{{ route("admin-backup-delete-cloud") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ filename: filename })
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.status === 'success') {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.success({ title: 'Deleted', message: data.message, position: 'topCenter' });
                        }
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({ title: 'Error', message: data.message, position: 'topCenter' });
                        }
                    }
                })
                .catch(err => {
                    this.loading = false;
                });
            },

            onProviderChange() {
                if (this.config.cloud_driver === 's3') {
                    this.config.s3_endpoint = '';
                    this.config.s3_region = 'us-east-1';
                    this.config.s3_use_path_style = false;
                } else if (this.config.cloud_driver === 'r2') {
                    this.config.s3_region = 'auto';
                    this.config.s3_use_path_style = false;
                    if (!this.config.s3_endpoint) {
                        this.config.s3_endpoint = 'https://<account_id>.r2.cloudflarestorage.com';
                    }
                } else if (this.config.cloud_driver === 'minio') {
                    this.config.s3_use_path_style = true;
                    if (!this.config.s3_endpoint) {
                        this.config.s3_endpoint = 'http://127.0.0.1:9000';
                    }
                } else if (this.config.cloud_driver === 'digitalocean') {
                    this.config.s3_region = 'nyc3';
                    this.config.s3_endpoint = 'https://nyc3.digitaloceanspaces.com';
                    this.config.s3_use_path_style = false;
                } else if (this.config.cloud_driver === 'hostinger') {
                    if (!this.config.s3_endpoint || this.config.s3_endpoint.includes('digitalocean') || this.config.s3_endpoint.includes('cloudflarestorage') || this.config.s3_endpoint.includes(':9000')) {
                        this.config.s3_endpoint = 'https://fr-par-1.hostingerobjectstorage.com';
                    }
                    if (!this.config.s3_region || this.config.s3_region === 'us-east-1' || this.config.s3_region === 'nyc3' || this.config.s3_region === 'auto') {
                        this.config.s3_region = 'fr-par-1';
                    }
                    this.config.s3_use_path_style = false;
                }
            },

            testCloudConnection() {
                this.testingConnection = true;
                this.testStatus = null;

                fetch('{{ route("admin-backup-test-cloud") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.config)
                })
                .then(r => r.json())
                .then(data => {
                    this.testingConnection = false;
                    this.testStatus = data;
                    if (data.success) {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.success({ title: 'Connected', message: data.message, position: 'topCenter' });
                        }
                    } else {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({ title: 'Connection Failed', message: data.message, position: 'topCenter' });
                        }
                    }
                })
                .catch(err => {
                    this.testingConnection = false;
                    this.testStatus = { success: false, message: err.message || 'Connection failed' };
                });
            },

            saveSettings() {
                this.loading = true;
                this.loadingTitle = 'Saving Configuration...';

                fetch('{{ route("admin-backup-save-settings") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.config)
                })
                .then(r => r.json())
                .then(data => {
                    this.loading = false;
                    if (data.status === 'success') {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.success({ title: 'Saved', message: data.message, position: 'topCenter' });
                        }
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        if (typeof iziToast !== 'undefined') {
                            iziToast.error({ title: 'Error', message: data.message || 'Failed to save', position: 'topCenter' });
                        }
                    }
                })
                .catch(err => {
                    this.loading = false;
                    if (typeof iziToast !== 'undefined') {
                        iziToast.error({ title: 'Error', message: err.message || 'Request failed', position: 'topCenter' });
                    }
                });
            },

            formatBytes(bytes) {
                if (bytes <= 0) return '0 B';
                const units = ['B', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + units[i];
            }
        };
    }
</script>
@stop

