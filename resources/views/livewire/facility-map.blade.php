<div class="relative w-full" style="height: calc(100vh - 10rem);">
    {{-- Mapbox CSS --}}
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.css" rel="stylesheet">

    <style>
        @keyframes pulse-marker {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.8); opacity: 0.4; }
            100% { transform: scale(1); opacity: 1; }
        }
        .marker-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            cursor: pointer;
            position: relative;
            border: 2px solid rgba(255,255,255,0.8);
            box-shadow: 0 0 8px rgba(0,0,0,0.4);
        }
        .marker-dot::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: inherit;
            opacity: 0.4;
            animation: pulse-marker 2s ease-in-out infinite;
        }
        .marker-green { background: #10b981; }
        .marker-green::after { background: #10b981; }
        .marker-amber { background: #f59e0b; }
        .marker-amber::after { background: #f59e0b; }
        .marker-red { background: #ef4444; }
        .marker-red::after { background: #ef4444; }

        .mapboxgl-popup-content {
            background: #1a1f2e !important;
            border: 1px solid #2d3548 !important;
            border-radius: 12px !important;
            padding: 0 !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5) !important;
            min-width: 260px;
        }
        .mapboxgl-popup-tip {
            border-top-color: #1a1f2e !important;
        }
        .mapboxgl-popup-close-button {
            color: #9ca3af !important;
            font-size: 18px;
            padding: 4px 8px;
        }
    </style>

    {{-- Header Overlay --}}
    <div class="absolute top-4 left-4 z-10 bg-zinc-900/90 backdrop-blur-sm border border-zinc-700 rounded-xl px-5 py-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-white font-bold text-sm">Facility Map</h2>
                <p class="text-zinc-400 text-xs">{{ count($this->markers) }} facilities tracked</p>
            </div>
        </div>
    </div>

    {{-- 3D Buildings Toggle --}}
    <div class="absolute top-4 right-4 z-10">
        <button id="toggle3d" class="bg-zinc-900/90 backdrop-blur-sm border border-zinc-700 rounded-lg px-4 py-2 text-xs font-medium text-zinc-300 hover:text-white hover:border-emerald-500/50 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 0h.008v.008h-.008V7.5z" />
            </svg>
            3D Buildings
        </button>
    </div>

    {{-- Legend --}}
    <div class="absolute bottom-6 left-4 z-10 bg-zinc-900/90 backdrop-blur-sm border border-zinc-700 rounded-xl px-4 py-3">
        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider mb-2">Readiness Score</p>
        <div class="flex flex-col gap-1.5">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span class="text-xs text-zinc-300">Above 80% — Operational</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                <span class="text-xs text-zinc-300">50-80% — In Progress</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-xs text-zinc-300">Below 50% — Critical</span>
            </div>
        </div>
    </div>

    {{-- Map Container --}}
    <div id="facility-map" class="w-full h-full rounded-xl overflow-hidden" wire:ignore></div>

    {{-- Mapbox JS --}}
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.3.0/mapbox-gl.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            mapboxgl.accessToken = '{{ config("services.mapbox.token", "pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw") }}';

            const map = new mapboxgl.Map({
                container: 'facility-map',
                style: 'mapbox://styles/mapbox/dark-v11',
                center: [-95.7129, 37.0902],
                zoom: 5,
                pitch: 45,
                bearing: 0,
                antialias: true
            });

            map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');

            const markers = @json($this->markers);
            let buildings3dEnabled = false;

            map.on('load', function () {
                // Add 3D buildings source (hidden initially)
                map.addLayer({
                    'id': '3d-buildings',
                    'source': 'composite',
                    'source-layer': 'building',
                    'filter': ['==', 'extrude', 'true'],
                    'type': 'fill-extrusion',
                    'minzoom': 12,
                    'paint': {
                        'fill-extrusion-color': '#10b981',
                        'fill-extrusion-height': ['get', 'height'],
                        'fill-extrusion-base': ['get', 'min_height'],
                        'fill-extrusion-opacity': 0
                    }
                });

                // Add markers
                markers.forEach(function (m) {
                    const colorClass = m.readiness_score > 80 ? 'marker-green' : (m.readiness_score >= 50 ? 'marker-amber' : 'marker-red');
                    const colorLabel = m.readiness_score > 80 ? 'Operational' : (m.readiness_score >= 50 ? 'In Progress' : 'Critical');
                    const scoreColor = m.readiness_score > 80 ? '#10b981' : (m.readiness_score >= 50 ? '#f59e0b' : '#ef4444');

                    const el = document.createElement('div');
                    el.className = 'marker-dot ' + colorClass;

                    const popupHtml = `
                        <div class="p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-2.5 h-2.5 rounded-full" style="background:${scoreColor}"></div>
                                <span class="text-[10px] font-bold uppercase tracking-wider" style="color:${scoreColor}">${colorLabel}</span>
                            </div>
                            <h3 class="text-white font-bold text-sm mb-1">${m.name}</h3>
                            <p class="text-zinc-400 text-xs mb-3">${m.city}, ${m.state}</p>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div class="bg-zinc-800 rounded-lg p-2 text-center">
                                    <p class="text-white font-bold text-base">${Math.round(m.readiness_score)}%</p>
                                    <p class="text-zinc-500 text-[9px] uppercase tracking-wider">Score</p>
                                </div>
                                <div class="bg-zinc-800 rounded-lg p-2 text-center">
                                    <p class="text-amber-400 font-bold text-base">${m.open_wos}</p>
                                    <p class="text-zinc-500 text-[9px] uppercase tracking-wider">Open WOs</p>
                                </div>
                                <div class="bg-zinc-800 rounded-lg p-2 text-center">
                                    <p class="text-red-400 font-bold text-base">${m.sensor_alerts}</p>
                                    <p class="text-zinc-500 text-[9px] uppercase tracking-wider">Alerts</p>
                                </div>
                            </div>
                            <a href="/projects/${m.id}" class="block w-full text-center bg-accent-600 hover:bg-accent-700 text-white text-xs font-semibold py-2 rounded-lg transition-colors">
                                View Details
                            </a>
                        </div>
                    `;

                    const popup = new mapboxgl.Popup({ offset: 15, closeButton: true })
                        .setHTML(popupHtml);

                    const marker = new mapboxgl.Marker(el)
                        .setLngLat([m.lng, m.lat])
                        .setPopup(popup)
                        .addTo(map);

                    el.addEventListener('click', function () {
                        map.flyTo({
                            center: [m.lng, m.lat],
                            zoom: 13,
                            pitch: 60,
                            duration: 1500
                        });
                    });
                });
            });

            // 3D Buildings toggle
            document.getElementById('toggle3d').addEventListener('click', function () {
                buildings3dEnabled = !buildings3dEnabled;
                const opacity = buildings3dEnabled ? 0.6 : 0;
                map.setPaintProperty('3d-buildings', 'fill-extrusion-opacity', opacity);
                this.classList.toggle('border-emerald-500', buildings3dEnabled);
                this.classList.toggle('text-emerald-400', buildings3dEnabled);
            });
        });
    </script>
</div>
