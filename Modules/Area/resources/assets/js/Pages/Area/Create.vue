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

const props = defineProps<{}>();

const form = useForm({
    name: '',
    code: '',
    type: 'area',
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

document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement) {
        isFullscreen.value = false;
        setTimeout(() => {
            map?.invalidateSize();
        }, 100);
        if (mapContainer.value) {
            mapContainer.value.classList.remove('fullscreen-map');
        }
    } else {
        isFullscreen.value = true;
        if (mapContainer.value) {
            mapContainer.value.classList.add('fullscreen-map');
        }
    }
});

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
        drawnItems?.clearLayers();
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
    <Head title="Tambah Wilayah" />

    <AppLayout :breadcrumbs="[
        { title: 'Wilayah', href: areaIndex().url },
        { title: 'Tambah', href: areaCreate().url }
    ]">
        <div class="max-w-4xl mx-4 p-4 md:p-4 space-y-6">
            <!-- Header -->
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">Tambah Wilayah</h1>
                <p class="text-muted-foreground text-sm text-foreground/60">Silahkan lengkapi data wilayah infrastruktur dibawah ini.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Section 1: Identitas -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold">Informasi Wilayah</CardTitle>
                        <CardDescription class="text-xs">Detail nama dan tipe klasifikasi wilayah.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="space-y-1.5">
                            <Label for="name" class="text-sm font-medium">Nama Wilayah</Label>
                            <Input id="name" v-model="form.name" placeholder="Masukkan nama wilayah" required class="h-11 rounded-lg border-slate-200" />
                            <div v-if="form.errors.name" class="text-xs text-destructive mt-1">{{ form.errors.name }}</div>
                        </div>
                        
                        <div class="space-y-5">
                            <div class="space-y-1.5">
                                <Label for="code" class="text-sm font-medium">Kode Wilayah</Label>
                                <Input id="code" v-model="form.code" placeholder="Masukkan kode wilayah" class="h-11 rounded-lg border-slate-200" />
                                <div v-if="form.errors.code" class="text-xs text-destructive mt-1">{{ form.errors.code }}</div>
                            </div>
                            <div class="space-y-1.5">
                                <Label for="type" class="text-sm font-medium">Tipe Wilayah</Label>
                                <Select v-model="form.type" >
                                    <SelectTrigger class="h-11 rounded-lg border-slate-200">
                                        <SelectValue placeholder="Pilih tipe wilayah" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="region">Region</SelectItem>
                                        <SelectItem value="area">Area</SelectItem>
                                        <SelectItem value="subarea">Subarea</SelectItem>
                                        <SelectItem value="pop_location">POP Location</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.type" class="text-xs text-destructive mt-1">{{ form.errors.type }}</div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="description" class="text-sm font-medium">Keterangan</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Masukkan keterangan tambahan jika ada" class="min-h-[100px] rounded-lg border-slate-200 resize-none" />
                            <div v-if="form.errors.description" class="text-xs text-destructive mt-1">{{ form.errors.description }}</div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 2: Lokasi -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold">Lokasi Administratif</CardTitle>
                        <CardDescription class="text-xs">Data wilayah berdasarkan administrasi nasional.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="space-y-1.5">
                            <Label class="text-sm font-medium">Provinsi</Label>
                            <Select v-model="form.province_id">
                                <SelectTrigger class="w-full h-11 rounded-lg border-slate-200">
                                    <SelectValue placeholder="Pilih provinsi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="prov in provinces" :key="prov.id" :value="prov.id">
                                        {{ prov.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-sm font-medium">Kota / Kabupaten</Label>
                            <Select v-model="form.regency_id" :disabled="!form.province_id">
                                <SelectTrigger class="w-full h-11 rounded-lg border-slate-200">
                                    <SelectValue placeholder="Pilih kota/kabupaten" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="reg in regencies" :key="reg.id" :value="reg.id">
                                        {{ reg.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-sm font-medium">Kecamatan</Label>
                            <Select v-model="form.district_id" :disabled="!form.regency_id">
                                <SelectTrigger class="w-full h-11 rounded-lg border-slate-200">
                                    <SelectValue placeholder="Pilih kecamatan" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="dist in districts" :key="dist.id" :value="dist.id">
                                        {{ dist.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-sm font-medium">Kelurahan / Desa</Label>
                            <Select v-model="form.village_id" :disabled="!form.district_id">
                                <SelectTrigger class="w-full h-11 rounded-lg border-slate-200">
                                    <SelectValue placeholder="Pilih kelurahan/desa" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="vill in villages" :key="vill.id" :value="vill.id">
                                        {{ vill.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 3: Map -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold">Batas Wilayah (Map)</CardTitle>
                        <CardDescription class="text-xs">Gambarkan area cakupan pada peta dibawah ini.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="h-[450px] w-full rounded-lg border border-slate-200 overflow-hidden relative">
                            <div ref="mapContainer" class="absolute inset-0 z-0"></div>
                            <Button
                                type="button"
                                @click="toggleFullscreen"
                                variant="outline"
                                size="icon"
                                class="absolute top-3 right-3 z-[1000] bg-white shadow-sm border-slate-200"
                            >
                                <Maximize2 class="h-4 w-4" />
                            </Button>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 uppercase tracking-tight">
                            <span>Gunakan ikon Polygon di kiri atas peta untuk mulai menggambar.</span>
                            <span v-if="form.boundary" class="text-emerald-600 font-bold">Batas wilayah telah ditentukan</span>
                        </div>
                    </CardContent>
                    <CardFooter class="border-t bg-slate-50/50 p-6 flex justify-end gap-3 rounded-b-lg">
                        <Button variant="ghost" as-child class="font-medium">
                            <Link :href="areaIndex().url">Batal</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="px-10 font-bold bg-primary hover:bg-primary/90 rounded-lg">
                            Simpan Wilayah
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>

<style>
:deep(.leaflet-control-zoom),
:deep(.leaflet-draw-section) {
    z-index: 1001 !important;
}
.fullscreen-map .map-controls {
    z-index: 1002 !important;
}
:deep(.fullscreen-map) {
    z-index: 9999 !important;
}
</style>
