document.addEventListener('DOMContentLoaded', function () {
    const mapContainer = document.getElementById('pickup-map');
    if (!mapContainer) {
        console.warn("Map container 'pickup-map' not found on this page.");
        return;
    }

    const mapboxAccessToken = window.MAPBOX_ACCESS_TOKEN_PHP || ''; 
    const defaultLat = parseFloat(window.DEFAULT_PICKUP_LAT_PHP) || 0;
    const defaultLng = parseFloat(window.DEFAULT_PICKUP_LNG_PHP) || 0;
    
    const latInput = document.getElementById('pickup_latitude');
    const lngInput = document.getElementById('pickup_longitude');
    const locationNameInput = document.getElementById('pickup_location_name');
    const clearLocationBtn = document.getElementById('clearLocationBtn');

    const initialLatForm = latInput ? latInput.value : '';
    const initialLngForm = lngInput ? lngInput.value : '';

    const initialLat = initialLatForm !== '' ? parseFloat(initialLatForm) : defaultLat;
    const initialLng = initialLngForm !== '' ? parseFloat(initialLngForm) : defaultLng;

    if (!mapboxAccessToken || mapboxAccessToken === '' || mapboxAccessToken.startsWith('pk.YOUR_ACTUAL_MAPBOX_PUBLIC_ACCESS_TOKEN_HERE')) {
        mapContainer.innerHTML = '<div class="alert alert-warning text-center p-3 small">Mapbox Access Token is not configured correctly. Please set it in the configuration file.</div>';
        mapContainer.style.display = 'flex';
        mapContainer.style.alignItems = 'center';
        mapContainer.style.justifyContent = 'center';
        console.error("Mapbox Access Token is missing or invalid.");
        return;
    }
    
    if (typeof mapboxgl === 'undefined') {
        mapContainer.innerHTML = '<div class="alert alert-danger text-center p-3 small">Mapbox GL JS library not loaded. Ensure it is included in the page footer.</div>';
        console.error("Mapbox GL JS library not loaded.");
        return;
    }

    mapboxgl.accessToken = mapboxAccessToken;

    try {
        const map = new mapboxgl.Map({
            container: 'pickup-map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [initialLng, initialLat],
            zoom: (initialLatForm !== '' && initialLngForm !== '') ? 13 : 6
        });

        map.addControl(new mapboxgl.NavigationControl(), 'top-right');
        
        if (typeof MapboxGeocoder !== 'undefined') {
            const geocoder = new MapboxGeocoder({
                accessToken: mapboxgl.accessToken,
                mapboxgl: mapboxgl,
                marker: false,
                placeholder: 'Search for a location'
            });
            map.addControl(geocoder, 'top-left');
            geocoder.on('result', function(e) {
                if (e.result && e.result.center) {
                    const lngLat = { lng: e.result.center[0], lat: e.result.center[1] };
                    addOrUpdateMarker(lngLat);
                    if (locationNameInput && e.result.place_name) {
                       locationNameInput.value = e.result.place_name.split(',')[0];
                    }
                }
            });
        } else {
            console.warn("MapboxGeocoder library not loaded.");
        }

        let marker = null;

        function updateFormInputs(lngLat) {
            if (lngInput && latInput) {
                lngInput.value = lngLat.lng.toFixed(6);
                latInput.value = lngLat.lat.toFixed(6);
            }
        }
        
        async function reverseGeocode(lngLat) {
            if (!locationNameInput || !mapboxgl.accessToken) return;
            try {
                const response = await fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${lngLat.lng},${lngLat.lat}.json?access_token=${mapboxgl.accessToken}&types=address,poi,place&limit=1`);
                const data = await response.json();
                if (data.features && data.features.length > 0) {
                    locationNameInput.value = data.features[0].place_name;
                } else {
                    // locationNameInput.value = ''; 
                }
            } catch (error) {
                console.error('Reverse geocoding error:', error);
                // locationNameInput.value = '';
            }
        }

        function addOrUpdateMarker(lngLat) {
            if (marker) {
                marker.setLngLat(lngLat);
            } else {
                marker = new mapboxgl.Marker({
                    draggable: true,
                    color: "#0d6efd" 
                })
                .setLngLat(lngLat)
                .addTo(map);

                marker.on('dragend', () => {
                    const newLngLat = marker.getLngLat();
                    updateFormInputs(newLngLat);
                    reverseGeocode(newLngLat);
                });
            }
            updateFormInputs(lngLat);
            reverseGeocode(lngLat); 
            map.flyTo({center: lngLat, zoom: Math.max(map.getZoom(), 13)});
        }

        if (initialLatForm !== '' && initialLngForm !== '' && !isNaN(initialLat) && !isNaN(initialLng)) {
           addOrUpdateMarker({lng: initialLng, lat: initialLat});
        }

        map.on('click', (e) => {
            addOrUpdateMarker(e.lngLat);
        });

        if (clearLocationBtn) {
            clearLocationBtn.addEventListener('click', () => {
                if (marker) {
                    marker.remove();
                    marker = null;
                }
                if(latInput) latInput.value = '';
                if(lngInput) lngInput.value = '';
                if(locationNameInput) locationNameInput.value = '';
                map.flyTo({center: [defaultLng, defaultLat], zoom: 6});
            });
        }
        
        map.on('load', function () {
            map.resize(); 
        });

    } catch (e) {
        console.error("Error initializing Mapbox map:", e);
        if (mapContainer) {
            mapContainer.innerHTML = '<div class="alert alert-danger text-center p-3 small">Could not initialize the map. Check console for errors.</div>';
        }
    }
});