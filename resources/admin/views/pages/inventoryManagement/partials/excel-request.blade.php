async requestInventoryExport(params) {
    const reportUrl = @json(\Illuminate\Support\Facades\Route::has('admin-' . $routeName . '-report') ? route('admin-' . $routeName . '-report') : null);
    if (!reportUrl) {
        const error = new Error('Inventory report route is not registered.');
        error.code = 'INVENTORY_ROUTE_MISSING';
        throw error;
    }

    const response = await Axios.get(reportUrl, {
        params,
        headers: { Accept: 'application/json' },
    });
    // An expired session can redirect Axios to a login page with HTTP 200.
    if (!Array.isArray(response.data)) {
        const error = new Error('Inventory report did not return an array.');
        error.code = 'INVENTORY_RESPONSE_INVALID';
        throw error;
    }
    return response.data;
},
showInventoryExportError(error) {
    const status = error.response?.status;
    let message = @json(__('inventory_export.failed'));
    if (error.code === 'INVENTORY_ROUTE_MISSING' || status === 404) {
        message = @json(__('inventory_export.route_missing'));
    } else if (status === 401 || status === 419) {
        message = @json(__('inventory_export.session_expired'));
    } else if (status === 403) {
        message = @json(__('inventory_export.forbidden'));
    } else if (error.code === 'INVENTORY_RESPONSE_INVALID') {
        message = @json(__('inventory_export.invalid_response'));
    } else if (status >= 500) {
        message = @json(__('inventory_export.server_error'));
    } else if (error.isAxiosError && !error.response) {
        message = @json(__('inventory_export.network_error'));
    }
    console.error('Export Excel failed:', error);
    window.alert(message);
},
