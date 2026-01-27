<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { ref, watch, onMounted, nextTick } from 'vue';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-draw';
import 'leaflet-draw/dist/leaflet.draw.css';
import { Maximize2 } from 'lucide-vue-next';
import { index as areaIndex, create as areaCreate, store as areaStore } from '@/routes/area';

const props = defineProps<{
    // parents: Array<any>; // Removed
}>();

const form = useForm({
    name: '',
    code: '',
    type: 'area',
    // parent_id: null as number | null, // Removed
    province_id: null as string | null,
    regency_id: null as string | null,
    district_id: null as string | null,
    village_id: null as string | null,
    description: '',
    boundary: null as any,
});

const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let drawnItems: L.FeatureGroup | null = null;

// Fullscreen functionality
const isFullscreen = ref(false);

const toggleFullscreen = () => {
    const mapElement = mapContainer.value;
    if (!mapElement) return;

    if (!document.fullscreenElement) {
        mapElement.requestFullscreen().then(() => {
            isFullscreen.value = true;
            // Ensure map adjusts to new dimensions
            setTimeout(() => {
                map?.invalidateSize();
            }, 100);
        }).catch((err) => {
            console.error(`Error attempting to enable fullscreen: ${err.message}`);
        });
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen().then(() => {
                isFullscreen.value = false;
            });
        }
    }
};

// Listen for fullscreen change events to adjust map
document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement) {
        isFullscreen.value = false;
        // Adjust map size when exiting fullscreen
        setTimeout(() => {
            map?.invalidateSize();
        }, 100);
        
        // Remove fullscreen class from map container
        if (mapContainer.value) {
            mapContainer.value.classList.remove('fullscreen-map');
        }
    } else {
        isFullscreen.value = true;
        // Add fullscreen class to map container
        if (mapContainer.value) {
            mapContainer.value.classList.add('fullscreen-map');
        }
    }
});

const provinces = ref<Array<any>>([]);
const regencies = ref<Array<any>>([]);
const districts = ref<Array<any>>([]);
const villages = ref<Array<any>>([]);

// Fetch Provinces on mount
const fetchProvinces = async () => {
    try {
        const response = await axios.get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
        provinces.value = response.data;
    } catch (error) {
        console.error('Error fetching provinces:', error);
    }
};

const initMap = () => {
    if (!mapContainer.value) return;

    map = L.map(mapContainer.value).setView([-2.5489, 118.0149], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    const drawControl = new L.Control.Draw({
        edit: {
            featureGroup: drawnItems
        },
        draw: {
            polygon: {},
            polyline: false,
            rectangle: false,
            circle: false,
            marker: false,
            circlemarker: false
        }
    });
    map.addControl(drawControl);

    map.on(L.Draw.Event.CREATED, (event: any) => {
        const layer = event.layer;
        drawnItems?.clearLayers(); // Only one polygon allowed
        drawnItems?.addLayer(layer);
        updateFormBoundary();
    });

    map.on(L.Draw.Event.EDITED, () => {
        updateFormBoundary();
    });

    map.on(L.Draw.Event.DELETED, () => {
        form.boundary = null;
    });
};

const updateFormBoundary = () => {
    if (!drawnItems) return;
    const layers = drawnItems.getLayers();
    if (layers.length > 0) {
        const polygon = layers[0] as L.Polygon;
        const latlngs = polygon.getLatLngs();
        // Flatten if nested (which L.Polygon can be)
        const coords = Array.isArray(latlngs[0]) ? latlngs[0] : latlngs;
        form.boundary = (coords as L.LatLng[]).map(ll => [ll.lat, ll.lng]);
    } else {
        form.boundary = null;
    }
};

onMounted(async () => {
    await fetchProvinces();
    await nextTick();
    initMap();
});

// Watchers for cascading dropdowns
watch(() => form.province_id, async (newVal) => {
    regencies.value = [];
    districts.value = [];
    villages.value = [];
    form.regency_id = null;
    form.district_id = null;
    form.village_id = null;
    
    if (newVal) {
        try {
            const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${newVal}.json`);
            regencies.value = response.data;
        } catch (error) {
            console.error('Error fetching regencies:', error);
        }
    }
});

watch(() => form.regency_id, async (newVal) => {
    districts.value = [];
    villages.value = [];
    form.district_id = null;
    form.village_id = null;
    
    if (newVal) {
        try {
            const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${newVal}.json`);
            districts.value = response.data;
        } catch (error) {
            console.error('Error fetching districts:', error);
        }
    }
});

watch(() => form.district_id, async (newVal) => {
    villages.value = [];
    form.village_id = null;
    
    if (newVal) {
        try {
            const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${newVal}.json`);
            villages.value = response.data;
        } catch (error) {
            console.error('Error fetching villages:', error);
        }
    }
});

const submit = () => {
    form.post(areaStore().url);
};
</script>

<template>
    <Head title="Create Area" />

    <AppLayout :breadcrumbs="[
        { title: 'Areas', href: areaIndex().url },
        { title: 'Create', href: areaCreate().url }
    ]">
        <div class="max-w-2xl mx-auto p-4 md:p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Create New Area</CardTitle>
                    <CardDescription>Add a new infrastructure area.</CardDescription>
                </CardHeader>
                <form @submit.prevent="submit">
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" required />
                                <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
                            </div>
                            <div class="space-y-2">
                                <Label for="code">Code (Optional)</Label>
                                <Input id="code" v-model="form.code" />
                                <div v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="type">Type</Label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="region">Region</SelectItem>
                                    <SelectItem value="area">Area</SelectItem>
                                    <SelectItem value="subarea">Subarea</SelectItem>
                                    <SelectItem value="pop_location">POP Location</SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.type" class="text-sm text-destructive">{{ form.errors.type }}</div>
                        </div>

                        <div class="space-y-2">
                            <Label>Location (Regional)</Label>
                            <div class="grid grid-cols-2 gap-4">
                                <Select v-model="form.province_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select Province" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="prov in provinces" :key="prov.id" :value="prov.id">
                                            {{ prov.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>

                                <Select v-model="form.regency_id" :disabled="!form.province_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select Regency" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="reg in regencies" :key="reg.id" :value="reg.id">
                                            {{ reg.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>

                                <Select v-model="form.district_id" :disabled="!form.regency_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select District" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="dist in districts" :key="dist.id" :value="dist.id">
                                            {{ dist.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>

                                <Select v-model="form.village_id" :disabled="!form.district_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select Village" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="vill in villages" :key="vill.id" :value="vill.id">
                                            {{ vill.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" />
                            <div v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</div>
                        </div>

                        <div class="space-y-2">
                            <Label>Area Boundary (Polygon)</Label>
                            <div class="h-[400px] w-full rounded-md border overflow-hidden relative">
                                <div ref="mapContainer" class="absolute inset-0 z-0"></div>
                                <Button
                                    @click="toggleFullscreen"
                                    variant="secondary"
                                    size="sm"
                                    class="absolute top-2 right-2 z-[1000] shadow-md"
                                >
                                    <Maximize2 class="h-4 w-4" />
                                </Button>
                                
                                <!-- Fullscreen Controls -->
                                <div v-if="isFullscreen" class="absolute top-2 left-2 z-[1000] flex gap-2">
                                    <Button
                                        @click="toggleFullscreen"
                                        variant="default"
                                        size="sm"
                                        class="shadow-md"
                                    >
                                        Done
                                    </Button>
                                    <Button
                                        @click="toggleFullscreen"
                                        variant="outline"
                                        size="sm"
                                        class="shadow-md"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground">Click the polygon tool to draw the area boundary on the map.</p>
                            <div v-if="form.errors.boundary" class="text-sm text-destructive">{{ form.errors.boundary }}</div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Button variant="outline" as-child>
                            <Link :href="areaIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">Create Area</Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>

<style>
/* Make sure controls are visible in fullscreen mode */
:deep(.leaflet-control-zoom),
:deep(.leaflet-draw-section) {
    z-index: 1001 !important;
}

.fullscreen-map .map-controls {
    z-index: 1002 !important;
}

/* Ensure fullscreen buttons are visible */
:deep(.fullscreen-map) {
    z-index: 9999 !important;
}

.absolute.top-2.right-2.z-\[1000\].shadow-md {
    z-index: 10000 !important;
}

.absolute.top-2.left-2.z-\[1000\].flex.gap-2 {
    z-index: 10000 !important;
}
</style>
