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
import { index as areaIndex, edit as areaEdit, update as areaUpdate } from '@/routes/area';

const props = defineProps<{
    area: any;
}>();

const form = useForm({
    name: props.area.name,
    code: props.area.code,
    type: props.area.type,
    province_id: props.area.province_id,
    regency_id: props.area.regency_id,
    district_id: props.area.district_id,
    village_id: props.area.village_id,
    description: props.area.description,
    boundary: props.area.boundary || null,
});

const mapContainer = ref<HTMLElement | null>(null);
let map: L.Map | null = null;
let drawnItems: L.FeatureGroup | null = null;

const provinces = ref<Array<any>>([]);
const regencies = ref<Array<any>>([]);
const districts = ref<Array<any>>([]);
const villages = ref<Array<any>>([]);

const fetchProvinces = async () => {
    try {
        const response = await axios.get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
        provinces.value = response.data;
    } catch (error) {
        console.error('Error fetching provinces:', error);
    }
};

const fetchRegencies = async (id: string) => {
    try {
        const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${id}.json`);
        regencies.value = response.data;
    } catch (error) {
        console.error('Error fetching regencies:', error);
    }
};

const fetchDistricts = async (id: string) => {
    try {
        const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${id}.json`);
        districts.value = response.data;
    } catch (error) {
        console.error('Error fetching districts:', error);
    }
};

const fetchVillages = async (id: string) => {
    try {
        const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${id}.json`);
        villages.value = response.data;
    } catch (error) {
        console.error('Error fetching villages:', error);
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

    // Initial boundary if exists
    if (form.boundary && Array.isArray(form.boundary) && form.boundary.length > 0) {
        const polygon = L.polygon(form.boundary as L.LatLngExpression[]);
        drawnItems.addLayer(polygon);
        map.fitBounds(polygon.getBounds());
    }

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
    if (form.province_id) await fetchRegencies(form.province_id);
    if (form.regency_id) await fetchDistricts(form.regency_id);
    if (form.district_id) await fetchVillages(form.district_id);
    
    await nextTick();
    initMap();
});

// Watchers for cascading dropdowns
watch(() => form.province_id, async (newVal, oldVal) => {
    if (newVal === oldVal) return;
    regencies.value = [];
    districts.value = [];
    villages.value = [];
    form.regency_id = null;
    form.district_id = null;
    form.village_id = null;
    
    if (newVal) {
        await fetchRegencies(newVal);
    }
});

watch(() => form.regency_id, async (newVal, oldVal) => {
    if (newVal === oldVal) return;
    districts.value = [];
    villages.value = [];
    form.district_id = null;
    form.village_id = null;
    
    if (newVal) {
        await fetchDistricts(newVal);
    }
});

watch(() => form.district_id, async (newVal, oldVal) => {
    if (newVal === oldVal) return;
    villages.value = [];
    form.village_id = null;
    
    if (newVal) {
        await fetchVillages(newVal);
    }
});

const submit = () => {
    form.put(areaUpdate({ area: props.area.id }).url);
};
</script>

<template>
    <Head title="Edit Area" />

    <AppLayout :breadcrumbs="[
        { title: 'Areas', href: areaIndex().url },
        { title: 'Edit', href: areaEdit({ area: area.id }).url }
    ]">
        <div class="max-w-2xl mx-auto p-4 md:p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Edit Area</CardTitle>
                    <CardDescription>Update infrastructure area details.</CardDescription>
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
                            </div>
                            <p class="text-xs text-muted-foreground">Click the polygon tool to draw the area boundary on the map.</p>
                            <div v-if="form.errors.boundary" class="text-sm text-destructive">{{ form.errors.boundary }}</div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Button variant="outline" as-child>
                            <Link :href="areaIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">Update Area</Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
