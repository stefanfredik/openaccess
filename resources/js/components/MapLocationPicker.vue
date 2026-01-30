<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { Map as MapIcon, MapPin, Save } from 'lucide-vue-next';
import { nextTick, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    latitude: string | number | null;
    longitude: string | number | null;
    areas?: Array<any>;
    areaId?: string | number | null;
}>();

const emit = defineEmits(['update:latitude', 'update:longitude']);

const isOpen = ref(false);
const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;
let areaLayer: L.Polygon | null = null;

// Local state for the modal
const tempLat = ref<string | number>(props.latitude || '');
const tempLng = ref<string | number>(props.longitude || '');

const initMap = () => {
    if (!mapContainer.value) return;

    const hasLocation = tempLat.value && tempLng.value;
    const lat = hasLocation ? parseFloat(tempLat.value as string) : -6.2088;
    const lng = hasLocation ? parseFloat(tempLng.value as string) : 106.8456;
    const zoom = hasLocation ? 15 : 12;

    if (map) {
        map.remove();
        map = null;
    }

    map = L.map(mapContainer.value).setView([lat, lng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    // Initial marker icon setup
    const iconRetinaUrl =
        'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png';
    const iconUrl =
        'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png';
    const shadowUrl =
        'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png';

    const DefaultIcon = L.icon({
        iconUrl,
        iconRetinaUrl,
        shadowUrl,
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
    });
    L.Marker.prototype.options.icon = DefaultIcon;

    if (hasLocation) {
        addMarker(lat, lng);
    }

    if (props.areaId) {
        drawAreaBoundary(props.areaId);
    }

    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        addMarker(lat, lng);
        tempLat.value = lat.toFixed(6);
        tempLng.value = lng.toFixed(6);
    });
};

const addMarker = (lat: number, lng: number) => {
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else if (map) {
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', (event) => {
            const position = event.target.getLatLng();
            tempLat.value = position.lat.toFixed(6);
            tempLng.value = position.lng.toFixed(6);
        });
    }
};

const drawAreaBoundary = (id: string | number) => {
    if (!map || !id || !props.areas) return;
    if (areaLayer) {
        map.removeLayer(areaLayer);
        areaLayer = null;
    }

    const area = props.areas.find(
        (a: any) => a.id.toString() === id.toString(),
    );
    if (area && area.boundary) {
        try {
            areaLayer = L.polygon(area.boundary, {
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

const handleSave = () => {
    emit('update:latitude', tempLat.value);
    emit('update:longitude', tempLng.value);
    isOpen.value = false;
};

watch(isOpen, async (val: boolean) => {
    if (val) {
        tempLat.value = props.latitude || '';
        tempLng.value = props.longitude || '';
        await nextTick();
        setTimeout(initMap, 100);
    }
});

watch(
    () => props.areaId,
    (newId: string | number | null | undefined) => {
        if (isOpen.value && newId) {
            drawAreaBoundary(newId);
        }
    },
);

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});
</script>

<template>
    <div class="flex flex-col gap-2">
        <Label class="text-sm font-medium">Lokasi Koordinat</Label>
        <div class="flex items-center gap-3">
            <div class="grid grow grid-cols-2 gap-2">
                <div class="relative">
                    <span
                        class="absolute top-1/2 left-3 -translate-y-1/2 text-[10px] font-bold text-slate-400 uppercase"
                        >Lat</span
                    >
                    <div
                        class="border-borderpx-3 flex h-11 items-center rounded-lg border pl-10 font-mono text-sm text-slate-600"
                    >
                        {{ latitude || '-' }}
                    </div>
                </div>
                <div class="relative">
                    <span
                        class="absolute top-1/2 left-3 -translate-y-1/2 text-[10px] font-bold text-slate-400 uppercase"
                        >Lng</span
                    >
                    <div
                        class="border-borderpx-3 flex h-11 items-center rounded-lg border pl-10 font-mono text-sm text-slate-600"
                    >
                        {{ longitude || '-' }}
                    </div>
                </div>
            </div>

            <Dialog v-model:open="isOpen">
                <DialogTrigger as-child>
                    <Button
                        variant="outline"
                        class="h-11 gap-2 border-primary/20 px-4 text-primary hover:bg-primary/5 hover:text-primary"
                    >
                        <MapPin class="h-4 w-4" />
                        Pilih di Peta
                    </Button>
                </DialogTrigger>
                <DialogContent
                    class="gap-0 overflow-hidden border-none p-0 shadow-2xl sm:max-w-[700px]"
                >
                    <DialogHeader class="border-b bg-white p-6 pb-4">
                        <DialogTitle class="flex items-center gap-2">
                            <MapIcon class="h-5 w-5 text-primary" />
                            Pilih Lokasi Perangkat
                        </DialogTitle>
                        <DialogDescription>
                            Klik pada peta atau geser penanda untuk menetapkan
                            koordinat lokasi.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="relative h-[450px] bg-slate-100">
                        <div ref="mapContainer" class="z-0 h-full w-full"></div>

                        <!-- Overlay Koordinat -->
                        <div
                            class="absolute bottom-6 left-1/2 z-[40] flex -translate-x-1/2 gap-3"
                        >
                            <div
                                class="flex items-center gap-4 rounded-full border bg-white/90 px-5 py-2.5 shadow-xl backdrop-blur-md"
                            >
                                <div class="flex flex-col items-center">
                                    <span
                                        class="mb-1 text-[9px] leading-none font-bold text-slate-400 uppercase"
                                        >Latitude</span
                                    >
                                    <span
                                        class="font-mono text-sm leading-none text-slate-700"
                                        >{{ tempLat || '0.000000' }}</span
                                    >
                                </div>
                                <div class="h-6 w-px bg-slate-200"></div>
                                <div class="flex flex-col items-center">
                                    <span
                                        class="mb-1 text-[9px] leading-none font-bold text-slate-400 uppercase"
                                        >Longitude</span
                                    >
                                    <span
                                        class="font-mono text-sm leading-none text-slate-700"
                                        >{{ tempLng || '0.000000' }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t bg-slate-50 p-4"
                    >
                        <Button variant="ghost" @click="isOpen = false"
                            >Batal</Button
                        >
                        <Button
                            class="gap-2 px-8"
                            @click="handleSave"
                            :disabled="!tempLat || !tempLng"
                        >
                            <Save class="h-4 w-4" />
                            Simpan Lokasi
                        </Button>
                    </div>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>

<style scoped>
:deep(.leaflet-container) {
    cursor: crosshair;
}
</style>
