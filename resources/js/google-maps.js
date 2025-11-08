// ========== loader dinamico =========
function loadMaps(callback) {
    if (window.google?.maps) { callback(); return; }
    if (window.__mapsLoading) { window.__mapsCallbacks.push(callback); return; }
    // carga
    window.__mapsLoading   = true;
    window.__mapsCallbacks = [callback];

    const key = document.querySelector('meta[name="gmaps-key"]')?.content;
    if (!key) { console.error('GMAPS KEY meta not found'); return; }

    const s = document.createElement('script');
    s.src   = `https://maps.googleapis.com/maps/api/js?key=${key}&libraries=places&callback=__mapsInit`;
    s.async = true; s.defer = true;
    document.head.appendChild(s);

    window.__mapsInit = () => {
        window.__mapsReady = true;
        (window.__mapsCallbacks || []).forEach(fn => { try { fn(); } catch(e){ console.error(e); } });
        window.__mapsCallbacks = [];
    };
}

// ========== Lógica del modal ==========
let map, marker, autocomplete, geocoder;

function initUbicacionModal() {
    console.log('dfdfdfdf');

    if (!document.getElementById('map')) return;

    const COLOMBIA_CENTER = { lat: 4.5709, lng: -74.2973 };

    map = new google.maps.Map(document.getElementById('map'), {
        center: COLOMBIA_CENTER,
        zoom: 6,
        mapTypeControl: false,
        streetViewControl: false,
    });

    geocoder = new google.maps.Geocoder();

    marker = new google.maps.Marker({
        map,
        draggable: true,
        visible: false
    });

    // ---Autocomplete ---
    const input = document.getElementById('buscar');
    if (input) {
        autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: ['CO'] },
            fields: ['place_id', 'geometry', 'formatted_address', 'name']
        });
        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (!place.geometry?.location) return;
            centerAndMark(place.geometry.location);
            setAddress(place.formatted_address || place.name || '');
            syncToLivewire(place.geometry.location);
        });
    }
    // --- Click en el mapa → colocar/ mover marcador ---
    map.addListener('click', (e) => {
        centerAndMark(e.latLng);;
        syncToLivewire(e.latLng);
    });

    // --- Drag del marcador → actualizar inputs ---
    marker.addListener('dragend', (e) => {
        fillLatLngInputs(e.latLng);
        syncToLivewire(e.latLng);
    });

    // --- Entrada manual de lat/lng ---
    const latEl = document.getElementById('lat');
    const lngEl = document.getElementById('lng');
    function updateFromInputs(){
        const lat = parseFloat(latEl.value);
        const lng = parseFloat(lngEl.value);
        if (isFinite(lat) && isFinite(lng)) centerAndMark({ lat, lng });
    }
    latEl?.addEventListener('change', updateFromInputs);
    lngEl?.addEventListener('change', updateFromInputs);

    // --- Ubicación actual (HTML5 Geolocation) ---
    document.getElementById('btn-actual')?.addEventListener('click', () => {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition((pos) => {
            const ll = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            centerAndMark(ll);
            syncToLivewire(ll);
        }, (err) => {
            console.warn('Geolocation error', err);
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
    });

    // Si hay valores previos (modo edición), dibújalos
    const latVal = parseFloat(latEl?.value ?? '');
    const lngVal = parseFloat(lngEl?.value ?? '');
    if (isFinite(latVal) && isFinite(lngVal)) {
        centerAndMark({ lat: latVal, lng: lngVal }, true);
    }
}

// Helpers
function centerAndMark(latLng, keepZoom = false) {
    map.panTo(latLng);
    if (!keepZoom) map.setZoom(16);
    marker.setPosition(latLng);
    marker.setVisible(true);
    fillLatLngInputs(latLng);
}

function fillLatLngInputs(latLng) {
    const lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
    const lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
    const latEl = document.getElementById('lat');
    const lngEl = document.getElementById('lng');
    if (latEl) latEl.value = (+lat).toFixed(7);
    if (lngEl) lngEl.value = (+lng).toFixed(7);
}

function setAddress(addr) {
    const adr = document.getElementById('address');
    if (adr) adr.value = addr;
}

function syncToLivewire(latLng) {
    if (!window.Livewire) return;
    const lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
    const lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
    Livewire.dispatch('coords-updated', { lat, lng });
}

//disparador
window.addEventListener('open-ubicacion-modal', () => {
    loadMaps(() => {
        requestAnimationFrame(() => initUbicacionModal());
    });
});
