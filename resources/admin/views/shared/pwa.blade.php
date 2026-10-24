{{-- Keep installation assets on this origin, including when ASSET_URL points to a CDN. --}}
<link rel="manifest" href="{{ request()->getBasePath() }}/manifest.webmanifest">
<meta name="theme-color" content="#3c91e6">
<script src="{{ request()->getBasePath() }}/pwa.js" defer></script>
