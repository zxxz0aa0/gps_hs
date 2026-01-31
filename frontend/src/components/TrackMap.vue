<script setup>
import { onMounted, ref, watch } from 'vue';
import { setOptions, importLibrary } from "@googlemaps/js-api-loader";

const props = defineProps({
    tracks: {
        type: Array,
        default: () => []
    }
});

const mapDiv = ref(null);
const mapError = ref('');
const isMapReady = ref(false);

let map = null;
let polyline = null;
let markers = [];
let highlightMarker = null;

const initMap = async () => {
    const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

    if (!apiKey) {
        mapError.value = 'Google Maps API Key is not configured. Please set VITE_GOOGLE_MAPS_API_KEY in .env file.';
        return;
    }

    setOptions({
        key: apiKey,
        v: "weekly",
    });

    try {
        const { Map } = await importLibrary("maps");

        map = new Map(mapDiv.value, {
            center: { lat: 25.0330, lng: 121.5654 },
            zoom: 12,
            mapId: 'GPS_TRACK_MAP'
        });

        isMapReady.value = true;
        mapError.value = '';

    } catch (e) {
        console.error("Google Maps loading failed", e);
        mapError.value = 'Failed to load Google Maps. Please check your API key and network connection.';
    }
};

const drawTracks = async () => {
    if (!map || !isMapReady.value) return;

    // Clear previous
    if (polyline) polyline.setMap(null);
    markers.forEach(m => m.map = null);
    markers = [];

    if (!props.tracks || props.tracks.length === 0) return;

    const path = props.tracks.map(t => ({
        lat: parseFloat(t.latitude),
        lng: parseFloat(t.longitude)
    }));

    // Draw Polyline
    const { Polyline } = await google.maps.importLibrary("maps");
    polyline = new Polyline({
        path: path,
        geodesic: true,
        strokeColor: "#FF8C00",
        strokeOpacity: 1.0,
        strokeWeight: 3,
    });
    polyline.setMap(map);

    // Fit bounds
    const bounds = new google.maps.LatLngBounds();
    path.forEach(p => bounds.extend(p));
    map.fitBounds(bounds);

    // Add Start/End Markers using AdvancedMarkerElement
    try {
        const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");

        if (path.length > 0) {
            const startPin = new PinElement({
                background: '#00AA00',
                borderColor: '#006600',
                glyphColor: '#FFFFFF',
                glyph: 'S'
            });

            const startMarker = new AdvancedMarkerElement({
                position: path[0],
                map: map,
                title: "Start",
                content: startPin.element
            });
            markers.push(startMarker);

            const endPin = new PinElement({
                background: '#FF0000',
                borderColor: '#AA0000',
                glyphColor: '#FFFFFF',
                glyph: 'E'
            });

            const endMarker = new AdvancedMarkerElement({
                position: path[path.length - 1],
                map: map,
                title: "End",
                content: endPin.element
            });
            markers.push(endMarker);
        }
    } catch (e) {
        console.warn("AdvancedMarkerElement not available, using fallback", e);
        // Fallback to legacy Marker if AdvancedMarkerElement is not available
        const { Marker } = await google.maps.importLibrary("marker");
        if (path.length > 0) {
            markers.push(new Marker({
                position: path[0],
                map: map,
                title: "Start",
                label: 'S'
            }));
            markers.push(new Marker({
                position: path[path.length - 1],
                map: map,
                title: "End",
                label: 'E'
            }));
        }
    }
};

onMounted(() => {
    initMap();
});

watch(() => props.tracks, () => {
    drawTracks();
}, { deep: true });

const highlightPoint = async (track) => {
    if (!map || !isMapReady.value || !track) return;

    // Clear previous highlight marker
    if (highlightMarker) {
        highlightMarker.map = null;
        highlightMarker = null;
    }

    const position = {
        lat: parseFloat(track.latitude),
        lng: parseFloat(track.longitude)
    };

    try {
        const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");

        const pin = new PinElement({
            background: '#1E90FF',
            borderColor: '#0066CC',
            glyphColor: '#FFFFFF',
            scale: 1.3
        });

        highlightMarker = new AdvancedMarkerElement({
            position,
            map,
            title: track.location || '',
            content: pin.element,
            zIndex: 1000
        });
    } catch (e) {
        const { Marker } = await google.maps.importLibrary("marker");
        highlightMarker = new Marker({
            position,
            map,
            title: track.location || '',
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
            },
            zIndex: 1000
        });
    }

    map.panTo(position);
    map.setZoom(16);
};

defineExpose({ highlightPoint });
</script>

<template>
    <div class="map-wrapper">
        <div v-if="mapError" class="map-error">
            {{ mapError }}
        </div>
        <div ref="mapDiv" class="google-map" :class="{ 'has-error': mapError }"></div>
    </div>
</template>

<style scoped>
.map-wrapper {
    position: relative;
}
.google-map {
    width: 100%;
    height: 500px;
    background-color: #eee;
    border-radius: 8px;
}
.google-map.has-error {
    opacity: 0.5;
}
.map-error {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 0, 0, 0.9);
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    z-index: 10;
    text-align: center;
    max-width: 80%;
}
</style>
