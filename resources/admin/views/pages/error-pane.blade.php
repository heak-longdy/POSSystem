<div class="workspace-tab-pane active workspace-tab-error-pane"
     id="{{ !empty($tabPaneId) ? $tabPaneId : (!empty($tabKey) ? 'tab-pane-' . $tabKey : 'tab-pane-initial') }}"
     data-tab-key="{{ $tabKey ?? '' }}"
     style="width: 100%; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; text-align: center; background: #fff; min-height: 450px;">
    
    <div class="workspace-error-card" style="max-width: 650px; width: 100%; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
        <!-- Soft Amber Warning Icon -->
        <div style="width: 72px; height: 72px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.15);">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>

        <!-- Error Heading -->
        <h3 style="color: #0f172a; font-size: 20px; font-weight: 600; margin: 0 0 8px 0; line-height: 1.4;">
            {{ $errorTitle ?? __('global.workspace.load_error_title') ?? 'Could not load page' }}
        </h3>

        <!-- Friendly Description -->
        <p style="color: #64748b; font-size: 14px; margin: 0 0 16px 0; max-width: 480px; line-height: 1.5;">
            {{ $errorDescription ?? __('global.workspace.load_error_desc') ?? 'An unexpected error occurred while loading this page. Please try again.' }}
        </p>

        <!-- Error Reference ID Badge -->
        @if(!empty($errorId))
        <div class="error-reference-badge" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: #f1f5f9; border-radius: 20px; font-size: 12px; color: #475569; font-weight: 500; margin-bottom: 24px; border: 1px solid #e2e8f0;">
            <span>{{ __('global.workspace.error_reference') ?? 'Error Reference' }}:</span>
            <code style="color: #2563eb; font-weight: 600; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">{{ $errorId }}</code>
        </div>
        @endif

        <!-- Action Buttons -->
        <div style="display: flex; gap: 12px; justify-content: center; align-items: center; flex-wrap: wrap; margin-bottom: 24px;">
            <button type="button" 
                    class="btn btn-primary mdi-retry-btn" 
                    data-tab-key="{{ $tabKey ?? '' }}"
                    onclick="window.MDI ? window.MDI.retryTab('{{ $tabKey ?? '' }}', this) : window.location.reload()" 
                    aria-label="{{ __('global.workspace.retry') ?? 'Retry' }}">
                <span class="mdi-retry-icon-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                    </svg>
                </span>
                <span class="mdi-retry-text">{{ __('global.workspace.retry') ?? 'Retry' }}</span>
            </button>

            @if(!empty($tabKey) && $tabKey !== 'admin-dashboard')
            <button type="button" 
                    class="btn btn-secondary" 
                    onclick="window.MDI ? window.MDI.closeTab(window.MDI.tabs.find(t => t.key === '{{ $tabKey }}') || window.MDI.activeTabKey) : (window.location.href='/admin/dashboard')" 
                    style="min-width: 100px; height: 38px; padding: 0 16px; font-size: 13px; font-weight: 500; border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; transition: all 0.2s; background: #f8fafc; border: 1px solid #cbd5e1; color: #475569;">
                <span>{{ __('global.workspace.close_tab') ?? 'Close Tab' }}</span>
            </button>
            @endif
        </div>

        {{-- Developer Debugging Information (Accessible when APP_DEBUG=true) --}}
        @if(config('app.debug') && !empty($debugInfo))
        <div class="pos-dev-debug-wrapper" style="width: 100%; margin-top: 10px; text-align: left; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;" x-data="{ expanded: false }">
            <div @click="expanded = !expanded" style="padding: 10px 16px; background: #f1f5f9; display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none;">
                <span style="font-size: 12.5px; font-weight: 600; color: #334155; display: inline-flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
                    {{ __('global.workspace.developer_details') ?? 'Developer Debugging Information' }}
                </span>
                <span style="font-size: 11px; color: #64748b; font-weight: 500;" x-text="expanded ? 'Hide Details ▲' : 'Show Details ▼'"></span>
            </div>
            
            <div x-show="expanded" style="display: none; padding: 14px 16px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; color: #1e293b;" x-cloak>
                <div style="display: flex; justify-content: flex-end; margin-bottom: 8px;">
                    <button type="button" 
                            onclick="window.copyPosErrorDetails(this)" 
                            data-error-json="{{ htmlspecialchars(json_encode($debugInfo), ENT_QUOTES, 'UTF-8') }}" 
                            style="background: #e2e8f0; border: none; border-radius: 4px; padding: 4px 10px; font-size: 11px; color: #334155; cursor: pointer; transition: background 0.2s;">
                        {{ __('global.workspace.copy_error_details') ?? 'Copy Error Details' }}
                    </button>
                </div>

                <div style="margin-bottom: 8px;">
                    <strong style="color: #64748b;">Request URL:</strong>
                    <span style="color: #0369a1;">{{ $debugInfo['method'] ?? 'GET' }} {{ $debugInfo['url'] ?? request()->fullUrl() }}</span>
                </div>

                <div style="margin-bottom: 8px;">
                    <strong style="color: #64748b;">Exception:</strong>
                    <span style="color: #b91c1c; font-weight: 600;">{{ $debugInfo['exception'] ?? 'Error' }}</span>
                </div>

                <div style="margin-bottom: 8px;">
                    <strong style="color: #64748b;">Message:</strong>
                    <span style="color: #0f172a; word-break: break-word;">{{ $debugInfo['message'] ?? 'No message provided' }}</span>
                </div>

                @if(!empty($debugInfo['file']))
                <div style="margin-bottom: 8px;">
                    <strong style="color: #64748b;">Location:</strong>
                    <span style="color: #475569;">{{ $debugInfo['file'] }}:{{ $debugInfo['line'] ?? '' }}</span>
                </div>
                @endif

                @if(!empty($debugInfo['trace']))
                <div style="margin-top: 10px;">
                    <strong style="color: #64748b;">Stack Trace Snippet:</strong>
                    <pre style="margin: 6px 0 0 0; padding: 10px; background: #0f172a; color: #f8fafc; border-radius: 6px; font-size: 11px; overflow-x: auto; max-height: 200px; line-height: 1.5;">@foreach($debugInfo['trace'] as $index => $frame)#{{ $index }} {{ $frame['file'] ?? 'internal' }}({{ $frame['line'] ?? '?' }}): {{ $frame['function'] ?? '' }}
@endforeach</pre>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
