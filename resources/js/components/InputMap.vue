<script setup lang="ts">
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    latitude: string | number;
    longitude: string | number;
    areas?: Array<any>;
    areaId?: string | number;
}>();

const emit = defineEmits(['update:latitude', 'update:longitude']);

const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;
let areaLayer: L.Polygon | null = null;

const initMap = () => {
    if (!mapContainer.value) return;

    // Default to Indonesia center: -2.5489, 118.0149
    const hasLocation = props.latitude && props.longitude;
    const lat = hasLocation ? parseFloat(props.latitude as string) : -2.5489;
    const lng = hasLocation ? parseFloat(props.longitude as string) : 118.0149;
    const zoom = hasLocation ? 15 : 5;

    map = L.map(mapContainer.value, {
        scrollWheelZoom: false,
    }).setView([lat, lng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    // Fix marker icon issue for Leaflet in Vite/Webpack
    const iconRetinaUrl =
        'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png';
    const iconUrl =
        'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png';
    const shadowUrl =
        'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png';

    const DefaultIcon = L.icon({
        iconUrl: iconUrl,
        iconRetinaUrl: iconRetinaUrl,
        shadowUrl: shadowUrl,
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
    });
    L.Marker.prototype.options.icon = DefaultIcon;

    if (hasLocation) {
        addMarker(lat, lng);
    } else if (props.areaId) {
        drawAreaBoundary(props.areaId);
    }

    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        addMarker(lat, lng);
        emit('update:latitude', lat.toString());
        emit('update:longitude', lng.toString());
    });
};

const drawAreaBoundary = (areaId: string | number) => {
    if (!map || !areaId || !props.areas) return;

    // Clear existing layer
    if (areaLayer) {
        map.removeLayer(areaLayer);
        areaLayer = null;
    }

    const area = props.areas?.find(
        (a: any) => a.id.toString() === areaId.toString(),
    );

    if (area && area.boundary) {
        try {
            const boundaryData = area.boundary;

            areaLayer = L.polygon(boundaryData, {
                color: '#3b82f6',
                weight: 2,
                opacity: 0.6,
                fillOpacity: 0.1,
                dashArray: '5, 10',
            }).addTo(map);

            const bounds = areaLayer.getBounds();
            if (bounds.isValid()) {
                map.fitBounds(bounds);
            }
        } catch (e) {
            console.error('Failed to draw area boundary', e);
        }
    }
};

const addMarker = (lat: number, lng: number) => {
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        if (!map) return;
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', (event) => {
            const position = event.target.getLatLng();
            emit('update:latitude', position.lat.toString());
            emit('update:longitude', position.lng.toString());
        });
    }
};

onMounted(() => {
    // Small timeout to ensure container is rendered
    setTimeout(() => {
        initMap();
    }, 100);
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});

// Watch Area Change
watch(
    () => props.areaId,
    (newVal: string | number | undefined) => {
        if (newVal) {
            drawAreaBoundary(newVal);
        } else {
            if (areaLayer && map) {
                map.removeLayer(areaLayer);
                areaLayer = null;
            }
        }
    },
);

watch(
    () => [props.latitude, props.longitude],
    ([newLat, newLng]: [
        string | number | undefined,
        string | number | undefined,
    ]) => {
        if (newLat && newLng && map) {
            const lat = parseFloat(newLat as string);
            const lng = parseFloat(newLng as string);

            if (marker) {
                const cur = marker.getLatLng();
                if (
                    Math.abs(cur.lat - lat) > 0.0001 ||
                    Math.abs(cur.lng - lng) > 0.0001
                ) {
                    marker.setLatLng([lat, lng]);
                    // map.setView([lat, lng], 15); // Optional: Pan to new location
                }
            } else {
                addMarker(lat, lng);
                map.setView([lat, lng], 15);
            }
        }
    },
);
</script>

<template>
    <div class="space-y-2">
        <label
            class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
            >Lokasi Pemasangan (Map)</label
        >
        <div
            ref="mapContainer"
            class="z-0 h-[350px] w-full rounded-lg border border-slate-200"
        ></div>
        <p class="text-[11px] text-muted-foreground">
            Klik pada peta untuk menandai lokasi pemasangan.
        </p>
    </div>
</template>
