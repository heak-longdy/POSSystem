async requestReportExport(params) {
    // Each report must also work when opened directly in a fresh browser session.
    if (typeof window.ExcelJS?.Workbook !== 'function' || typeof window.saveAs !== 'function') {
        const error = new Error('ExcelJS or FileSaver did not load.');
        error.code = 'REPORT_EXPORT_ASSETS_MISSING';
        throw error;
    }
    const reportUrl = @json(\Illuminate\Support\Facades\Route::has('admin-' . $routeName . '-report') ? route('admin-' . $routeName . '-report') : null);
    if (!reportUrl) {
        const error = new Error('Report export route is not registered.');
        error.code = 'REPORT_EXPORT_ROUTE_MISSING';
        throw error;
    }
    const response = await Axios.get(reportUrl + '?' + params.toString(), {
        headers: { Accept: 'application/json' },
    });
    // Login redirects can return HTML with HTTP 200 instead of report data.
    if (!response.data || !Array.isArray(response.data.rows)) {
        const error = new Error('Report export did not return a rows array.');
        error.code = 'REPORT_EXPORT_RESPONSE_INVALID';
        throw error;
    }
    return response.data;
},
showReportExportError(error) {
    const status = error.response?.status;
    let message = @json(__('report_export.failed'));
    if (error.code === 'REPORT_EXPORT_ASSETS_MISSING') {
        message = @json(__('report_export.assets_missing'));
    } else if (error.code === 'REPORT_EXPORT_ROUTE_MISSING' || status === 404) {
        message = @json(__('report_export.route_missing'));
    } else if (status === 401 || status === 419) {
        message = @json(__('report_export.session_expired'));
    } else if (status === 403) {
        message = @json(__('report_export.forbidden'));
    } else if (error.code === 'REPORT_EXPORT_RESPONSE_INVALID') {
        message = @json(__('report_export.invalid_response'));
    } else if (status >= 500) {
        message = @json(__('report_export.server_error'));
    } else if (error.isAxiosError && !error.response) {
        message = @json(__('report_export.network_error'));
    }
    console.error('Report Excel export failed:', error);
    window.alert(message);
},
