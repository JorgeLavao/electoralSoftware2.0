const COLOMBIA_CENTER = { lat: 4.570868, lng: -74.297333 };
const COLOMBIA_BOUNDS = {
    north: 13.8,
    south: -4.6,
    east: -66.2,
    west: -81.9,
};

const ROLE_FALLBACK_COLORS = {
    simpatizante: '#2563eb',
    lider: '#16a34a',
    coordinador: '#dc2626',
    coordinator: '#dc2626',
    administrador: '#7c3aed',
    admin: '#7c3aed',
    'call-center': '#f97316',
    soporte: '#475569',
    support: '#475569',
};

const DEPARTMENT_CENTERS = [
    ['La Guajira', 11.45, -72.45], ['Magdalena', 10.25, -74.15], ['Atlantico', 10.85, -74.9],
    ['Cesar', 9.45, -73.45], ['Bolivar', 8.9, -74.5], ['Sucre', 9.1, -75.15],
    ['Cordoba', 8.35, -75.75], ['Norte de Santander', 7.9, -72.9], ['Santander', 6.8, -73.45],
    ['Antioquia', 6.75, -75.65], ['Arauca', 6.75, -70.75], ['Choco', 5.7, -76.8],
    ['Boyaca', 5.55, -73.25], ['Casanare', 5.35, -71.55], ['Caldas', 5.25, -75.45],
    ['Risaralda', 4.85, -75.75], ['Cundinamarca', 4.75, -74.25], ['Vichada', 4.7, -69.35],
    ['Quindio', 4.45, -75.7], ['Tolima', 4.05, -75.25], ['Valle del Cauca', 3.75, -76.45],
    ['Meta', 3.55, -72.95], ['Guainia', 2.7, -68.95], ['Cauca', 2.55, -76.85],
    ['Huila', 2.45, -75.65], ['Guaviare', 1.9, -72.7], ['Narino', 1.35, -77.85],
    ['Caqueta', 0.75, -74.25], ['Vaupes', 0.65, -70.85], ['Putumayo', 0.05, -76.3],
    ['Amazonas', -2.45, -70.4], ['San Andres y Providencia', 12.55, -81.7], ['Bogota D.C.', 4.71, -74.07],
];

let mapsPromise;
let activeMap;

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function normalizeKey(value) {
    return String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
}

function readPayload(element) {
    try {
        return JSON.parse(element.dataset.payload || '{}');
    } catch (error) {
        console.error('Invalid electoral map payload', error);
        return {};
    }
}

function loadGoogleMaps() {
    if (window.google?.maps) {
        return Promise.resolve(window.google.maps);
    }

    mapsPromise ??= new Promise((resolve, reject) => {
        const key = document.querySelector('meta[name="gmaps-key"]')?.content;
        if (!key) {
            reject(new Error('GMAPS KEY meta not found'));
            return;
        }

        window.__electoralMapsInit = () => resolve(window.google.maps);

        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}&callback=__electoralMapsInit&loading=async`;
        script.async = true;
        script.defer = true;
        script.onerror = () => reject(new Error('Google Maps failed to load'));
        document.head.appendChild(script);
    });

    return mapsPromise;
}

function popupTemplate(point) {
    return `
        <div class="electoral-map-popup">
            <div class="electoral-map-popup__head">
                <img src="${escapeHtml(point.photo)}" alt="" loading="lazy">
                <div>
                    <strong>${escapeHtml(point.name)}</strong>
                    <span>${escapeHtml(point.role)}</span>
                </div>
            </div>
            <dl>
                <div><dt>Departamento</dt><dd>${escapeHtml(point.department)}</dd></div>
                <div><dt>Municipio</dt><dd>${escapeHtml(point.municipality)}</dd></div>
                <div><dt>Telefono</dt><dd>${escapeHtml(point.phone)}</dd></div>
                <div><dt>Campana</dt><dd>${escapeHtml(point.campaign)}</dd></div>
                <div><dt>Ultima actividad</dt><dd>${escapeHtml(point.lastActivity)}</dd></div>
            </dl>
        </div>
    `;
}

function markerSymbol(point) {
    const color = point.color || ROLE_FALLBACK_COLORS[String(point.role).toLowerCase()] || '#64748b';

    return {
        path: google.maps.SymbolPath.CIRCLE,
        fillColor: color,
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 3,
        scale: 9,
    };
}

function validPoints(payload) {
    return (payload.points || [])
        .map((point) => ({ ...point, lat: Number(point.lat), lng: Number(point.lng) }))
        .filter((point) => Number.isFinite(point.lat) && Number.isFinite(point.lng));
}

function colorByDensity(total, max) {
    if (!total) return '#e2e8f0';
    const ratio = max > 0 ? total / max : 0;
    if (ratio > 0.8) return '#9f1239';
    if (ratio > 0.6) return '#e11d48';
    if (ratio > 0.4) return '#f97316';
    if (ratio > 0.2) return '#facc15';
    return '#93c5fd';
}

function buildMarkers(map, payload, infoWindow) {
    return validPoints(payload).map((point) => {
        const marker = new google.maps.Marker({
            map,
            position: { lat: point.lat, lng: point.lng },
            title: point.name,
            icon: markerSymbol(point),
        });

        marker.addListener('click', () => {
            infoWindow.setContent(popupTemplate(point));
            infoWindow.open({ map, anchor: marker });
        });

        return marker;
    });
}

function buildHeatLayer(map, payload) {
    const heatPoints = validPoints(payload).map((point) => ({
        lat: point.lat,
        lng: point.lng,
        weight: Math.max(1, Number(point.voters || 0) + 1),
    }));

    class CanvasHeatmapOverlay extends google.maps.OverlayView {
        constructor(points) {
            super();
            this.points = points;
            this.canvas = null;
        }

        onAdd() {
            this.canvas = document.createElement('canvas');
            this.canvas.style.position = 'absolute';
            this.canvas.style.pointerEvents = 'none';
            this.canvas.style.opacity = '0.72';

            this.getPanes().overlayLayer.appendChild(this.canvas);
        }

        draw() {
            if (!this.canvas) return;

            const projection = this.getProjection();
            const currentMap = this.getMap();

            if (!projection || !currentMap) return;

            const bounds = currentMap.getBounds();
            if (!bounds) return;

            const ne = projection.fromLatLngToDivPixel(bounds.getNorthEast());
            const sw = projection.fromLatLngToDivPixel(bounds.getSouthWest());

            if (!ne || !sw) return;

            const width = Math.max(1, ne.x - sw.x);
            const height = Math.max(1, sw.y - ne.y);

            this.canvas.style.left = `${sw.x}px`;
            this.canvas.style.top = `${ne.y}px`;
            this.canvas.width = width;
            this.canvas.height = height;

            const ctx = this.canvas.getContext('2d');
            ctx.clearRect(0, 0, width, height);

            const maxWeight = Math.max(1, ...this.points.map((point) => point.weight));
            const radius = 32;

            this.points.forEach((point) => {
                const pixel = projection.fromLatLngToDivPixel(
                    new google.maps.LatLng(point.lat, point.lng)
                );

                if (!pixel) return;

                const x = pixel.x - sw.x;
                const y = pixel.y - ne.y;
                const intensity = Math.min(1, point.weight / maxWeight);

                const gradient = ctx.createRadialGradient(x, y, 0, x, y, radius);
                gradient.addColorStop(0, `rgba(225, 29, 72, ${0.45 + intensity * 0.45})`);
                gradient.addColorStop(0.35, `rgba(250, 204, 21, ${0.3 + intensity * 0.35})`);
                gradient.addColorStop(0.7, `rgba(34, 197, 94, ${0.18 + intensity * 0.25})`);
                gradient.addColorStop(1, 'rgba(56, 189, 248, 0)');

                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.arc(x, y, radius, 0, Math.PI * 2);
                ctx.fill();
            });
        }

        onRemove() {
            if (this.canvas) {
                this.canvas.remove();
                this.canvas = null;
            }
        }
    }

    const overlay = new CanvasHeatmapOverlay(heatPoints);
    overlay.setMap(map || null);

    return overlay;
}

function buildDepartmentCircles(map, payload, infoWindow) {
    const departments = payload.departments || {};
    const max = Math.max(1, ...Object.values(departments).map((item) => Number(item.total || 0)));

    return DEPARTMENT_CENTERS.map(([name, lat, lng]) => {
        const stat = departments[normalizeKey(name)] || { total: 0, percentage: 0 };
        const total = Number(stat.total || 0);
        const circle = new google.maps.Circle({
            map,
            center: { lat, lng },
            radius: total ? 26000 + (total / max) * 78000 : 18000,
            strokeColor: '#ffffff',
            strokeOpacity: 0.95,
            strokeWeight: 2,
            fillColor: colorByDensity(total, max),
            fillOpacity: total ? 0.72 : 0.22,
        });

        circle.addListener('click', () => {
            infoWindow.setContent(`
                <div class="electoral-map-popup electoral-map-popup--compact">
                    <strong>${escapeHtml(name)}</strong>
                    <span>${escapeHtml(total)} simpatizantes</span>
                    <small>${escapeHtml(stat.percentage || 0)}% de la muestra</small>
                </div>
            `);
            infoWindow.setPosition({ lat, lng });
            infoWindow.open(map);
        });

        return circle;
    });
}

function createLegend(payload) {
    const element = document.createElement('div');
    element.className = 'electoral-map-legend';
    const roles = payload.legend || [];
    element.innerHTML = `
        <strong>Roles</strong>
        <div>${roles.map((item) => `
            <span><i style="background:${escapeHtml(item.color)}"></i>${escapeHtml(item.role)}</span>
        `).join('') || '<small>Sin roles disponibles</small>'}</div>
    `;

    return element;
}

function setLayerVisible(items, visible) {
    items.forEach((item) => item.setMap(visible ? activeMap.map : null));
}

function showLayer(mode) {
    if (!activeMap) return;

    setLayerVisible(activeMap.layers.markers, mode === 'markers' || mode === 'heat');
    activeMap.layers.heat.setMap(mode === 'heat' ? activeMap.map : null);
    setLayerVisible(activeMap.layers.departments, mode === 'departments');

    document.querySelectorAll('[data-map-mode]').forEach((button) => {
        button.dataset.active = String(button.dataset.mapMode === mode);
    });
}

function injectMapStyles() {
    if (document.getElementById('electoral-map-styles')) return;

    const style = document.createElement('style');
    style.id = 'electoral-map-styles';
    style.textContent = `
        .electoral-map-popup{min-width:250px;color:#0f172a;font-family:Instrument Sans,system-ui,sans-serif}
        .electoral-map-popup--compact{display:grid;gap:4px;min-width:170px}
        .electoral-map-popup--compact strong{font-size:14px}
        .electoral-map-popup--compact span{font-size:13px;font-weight:700;color:#0f172a}
        .electoral-map-popup--compact small{font-size:12px;color:#64748b}
        .electoral-map-popup__head{display:flex;gap:10px;align-items:center;padding-bottom:10px;margin-bottom:10px;border-bottom:1px solid #e2e8f0}
        .electoral-map-popup__head img{width:42px;height:42px;border-radius:12px;object-fit:cover}
        .electoral-map-popup__head strong{display:block;font-size:14px}
        .electoral-map-popup__head span{display:inline-flex;margin-top:3px;border-radius:999px;background:#f1f5f9;padding:2px 8px;font-size:11px;font-weight:700;color:#475569}
        .electoral-map-popup dl{display:grid;gap:7px;margin:0}
        .electoral-map-popup div{display:grid;grid-template-columns:105px 1fr;gap:8px}
        .electoral-map-popup dt{font-size:11px;font-weight:700;text-transform:uppercase;color:#64748b}
        .electoral-map-popup dd{margin:0;font-size:12px;font-weight:600;color:#0f172a}
        .electoral-map-legend{margin:12px;min-width:180px;border:1px solid rgba(148,163,184,.35);border-radius:14px;background:rgba(255,255,255,.94);padding:12px;box-shadow:0 18px 40px rgba(15,23,42,.18);backdrop-filter:blur(12px);color:#0f172a;font-family:Instrument Sans,system-ui,sans-serif}
        .electoral-map-legend strong{display:block;margin-bottom:8px;font-size:12px}
        .electoral-map-legend div{display:grid;gap:6px}
        .electoral-map-legend span{display:flex;align-items:center;gap:7px;font-size:12px;font-weight:600;color:#475569}
        .electoral-map-legend i{display:block;width:10px;height:10px;border-radius:999px}
        [data-map-mode][data-active="true"]{background:#0f172a!important;color:#fff!important;border-color:#0f172a!important}
    `;
    document.head.appendChild(style);
}

async function renderGoogleMap(element, overridePayload = null) {
    const payload = overridePayload || readPayload(element);
    await loadGoogleMaps();
    injectMapStyles();

    if (activeMap?.legend) {
        activeMap.legend.remove();
    }

    const map = new google.maps.Map(element, {
        center: COLOMBIA_CENTER,
        zoom: 5,
        minZoom: 5,
        restriction: {
            latLngBounds: COLOMBIA_BOUNDS,
            strictBounds: false,
        },
        mapTypeControl: true,
        mapTypeControlOptions: {
            position: google.maps.ControlPosition.TOP_LEFT,
        },
        fullscreenControl: true,
        streetViewControl: false,
        zoomControl: true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.RIGHT_TOP,
        },
    });

    const infoWindow = new google.maps.InfoWindow();
    const legend = createLegend(payload);
    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);

    const layers = {
        markers: buildMarkers(map, payload, infoWindow),
        heat: buildHeatLayer(null, payload),
        departments: buildDepartmentCircles(null, payload, infoWindow),
    };

    activeMap = { element, map, layers, legend };

    const points = validPoints(payload);
    if (points.length > 0) {
        const bounds = new google.maps.LatLngBounds();
        points.forEach((point) => bounds.extend({ lat: point.lat, lng: point.lng }));
        map.fitBounds(bounds, 42);

        google.maps.event.addListenerOnce(map, 'bounds_changed', () => {
            if (map.getZoom() > 8) map.setZoom(8);
        });
    }

    showLayer('markers');
}

function initListLocationMaps() {
    document.querySelectorAll('[data-list-location-map]').forEach((element) => {
        const signature = element.dataset.payload || '{}';
        if (element.dataset.renderedSignature === signature) return;

        element.dataset.renderedSignature = signature;
        renderGoogleMap(element).catch((error) => {
            console.error('Google map failed', error);
            element.innerHTML = '<div class="flex h-full items-center justify-center p-6 text-center text-sm font-semibold text-slate-600">No fue posible cargar Google Maps. Revisa la llave o la conexion del navegador.</div>';
        });
    });
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-map-mode]');
    if (!button || !activeMap) return;

    showLayer(button.dataset.mapMode || 'markers');
});

window.addEventListener('electoral-map-updated', (event) => {
    const element = document.querySelector('[data-list-location-map]');
    if (!element) return;

    const payload = event.detail?.payload || {};
    element.dataset.payload = JSON.stringify(payload);
    element.dataset.renderedSignature = '';
    renderGoogleMap(element, payload).catch(console.error);
});

window.renderListLocationMaps = initListLocationMaps;

document.addEventListener('DOMContentLoaded', initListLocationMaps);
document.addEventListener('livewire:navigated', initListLocationMaps);
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', () => requestAnimationFrame(initListLocationMaps));
});
