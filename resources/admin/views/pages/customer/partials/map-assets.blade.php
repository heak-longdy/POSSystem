<link rel="stylesheet" href="{{ asset('plugin/leaflet/leaflet.css') }}">
<script src="{{ asset('plugin/leaflet/leaflet.js') }}"></script>
<script>
    if (window.L && window.L.Icon && window.L.Icon.Default) {
        delete window.L.Icon.Default.prototype._getIconUrl;
        window.L.Icon.Default.mergeOptions({
            iconRetinaUrl: "{{ asset('plugin/leaflet/images/marker-icon-2x.png') }}",
            iconUrl: "{{ asset('plugin/leaflet/images/marker-icon.png') }}",
            shadowUrl: "{{ asset('plugin/leaflet/images/marker-shadow.png') }}",
        });
    }
</script>
