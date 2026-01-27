<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Switch } from '@/components/ui/switch';
import { index as popIndex, store as popStore } from '@/routes/pop';
import LocationPicker from '@/components/LocationPicker.vue';
import PhotoUpload from '@/components/PhotoUpload.vue';

defineProps<{
    areas: Array<any>;
}>();

const form = useForm({
    name: '',
    code: '',
    area_id: null as number | null,
    address: '',
    province: '',
    regency: '',
    district: '',
    village: '',
    latitude: '',
    longitude: '',
    electrical_capacity: undefined as number | undefined,
    power_source: 'PLN',
    has_backup_power: false,
    description: '',
    status: 'Active',
    photos: [] as File[],
});

const submit = () => {
    form.post(popStore().url);
};
</script>

<template>
    <Head title="Tambah POP" />

    <AppLayout :breadcrumbs="[
        { title: 'POP', href: popIndex().url },
        { title: 'Tambah', href: '#' }
    ]">
        <div class="max-w-4xl mx-4 p-4 md:p-4 space-y-6">
            <!-- Header -->
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">Tambah POP Baru</h1>
                <p class="text-muted-foreground text-sm text-foreground/60">Lengkapi informasi untuk mendaftarkan Point of Presence (POP) baru.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Section 1: Identitas -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold">Informasi Dasar</CardTitle>
                        <CardDescription class="text-xs">Detail identitas dan klasifikasi POP.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="space-y-1.5">
                            <Label for="name" class="text-sm font-medium">Nama POP</Label>
                            <Input id="name" v-model="form.name" required placeholder="Contoh: POP Pusat Jakarta" class="h-11 rounded-lg border-slate-200" />
                            <div v-if="form.errors.name" class="text-xs text-destructive mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div class="space-y-5">
                            <div class="space-y-1.5">
                                <Label for="code" class="text-sm font-medium">Kode POP</Label>
                                <Input id="code" v-model="form.code" required placeholder="Contoh: POP-JKT-01" class="h-11 rounded-lg border-slate-200" />
                                <div v-if="form.errors.code" class="text-xs text-destructive mt-1">{{ form.errors.code }}</div>
                            </div>
                            <div class="space-y-1.5">
                                <Label for="area" class="text-sm font-medium">Wilayah Infrastruktur</Label>
                                <Select v-model="form.area_id">
                                    <SelectTrigger class="w-full h-11 rounded-lg border-slate-200">
                                        <SelectValue placeholder="Pilih Wilayah" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="area in areas" :key="area.id" :value="area.id">
                                            {{ area.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.area_id" class="text-xs text-destructive mt-1">{{ form.errors.area_id }}</div>
                            </div>
                            <div class="space-y-1.5">
                                <Label for="status" class="text-sm font-medium">Status Operasional</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger class="w-full h-11 rounded-lg border-slate-200">
                                        <SelectValue placeholder="Pilih Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active">Active</SelectItem>
                                        <SelectItem value="Inactive">Inactive</SelectItem>
                                        <SelectItem value="Planned">Planned</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.status" class="text-xs text-destructive mt-1">{{ form.errors.status }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 2: Kelistrikan -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold">Infrastruktur Daya</CardTitle>
                        <CardDescription class="text-xs">Informasi kapasitas dan sumber daya listrik.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="space-y-1.5">
                            <Label for="electrical_capacity" class="text-sm font-medium">Kapasitas Listrik (VA/Watt)</Label>
                            <Input id="electrical_capacity" type="number" v-model="form.electrical_capacity" placeholder="Contoh: 1300" class="h-11 rounded-lg border-slate-200" />
                            <div v-if="form.errors.electrical_capacity" class="text-xs text-destructive mt-1">{{ form.errors.electrical_capacity }}</div>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="power_source" class="text-sm font-medium">Sumber Daya Utama</Label>
                            <Select v-model="form.power_source">
                                <SelectTrigger class="w-full h-11 rounded-lg border-slate-200">
                                    <SelectValue placeholder="Pilih Sumber Daya" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="PLN">PLN</SelectItem>
                                    <SelectItem value="Solar">Solar</SelectItem>
                                    <SelectItem value="Genset">Genset</SelectItem>
                                    <SelectItem value="Hybrid">Hybrid</SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.power_source" class="text-xs text-destructive mt-1">{{ form.errors.power_source }}</div>
                        </div>
                        <div class="flex items-center space-x-2 pt-2">
                            <Switch id="backup-power" :checked="form.has_backup_power" @update:checked="form.has_backup_power = $event" />
                            <Label for="backup-power" class="text-sm font-medium">Memiliki Daya Cadangan?</Label>
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 3: Lokasi -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold">Detail Lokasi</CardTitle>
                        <CardDescription class="text-xs">Koordinat dan alamat fisik POP.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="space-y-4">
                            <LocationPicker 
                                :latitude="form.latitude" 
                                :longitude="form.longitude"
                                @update:latitude="form.latitude = $event"
                                @update:longitude="form.longitude = $event"
                            />
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <Label for="latitude" class="text-sm font-medium">Latitude</Label>
                                    <Input id="latitude" v-model="form.latitude" placeholder="-6.200000" class="h-11 rounded-lg border-slate-200" />
                                    <div v-if="form.errors.latitude" class="text-xs text-destructive mt-1">{{ form.errors.latitude }}</div>
                                </div>
                                <div class="space-y-1.5">
                                    <Label for="longitude" class="text-sm font-medium">Longitude</Label>
                                    <Input id="longitude" v-model="form.longitude" placeholder="106.816666" class="h-11 rounded-lg border-slate-200" />
                                    <div v-if="form.errors.longitude" class="text-xs text-destructive mt-1">{{ form.errors.longitude }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="address" class="text-sm font-medium">Alamat Lengkap</Label>
                            <Textarea id="address" v-model="form.address" placeholder="Detail alamat lengkap..." class="min-h-[80px] rounded-lg border-slate-200 resize-none" />
                            <div v-if="form.errors.address" class="text-xs text-destructive mt-1">{{ form.errors.address }}</div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <Label for="province" class="text-sm font-medium">Provinsi</Label>
                                <Input id="province" v-model="form.province" class="h-11 rounded-lg border-slate-200" />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="regency" class="text-sm font-medium">Kota / Kabupaten</Label>
                                <Input id="regency" v-model="form.regency" class="h-11 rounded-lg border-slate-200" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Section 4: Dokumentasi & Catatan -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold">Dokumentasi & Catatan</CardTitle>
                        <CardDescription class="text-xs">Foto lokasi dan catatan tambahan.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-5">
                        <div class="space-y-1.5">
                            <Label class="text-sm font-medium">Foto Dokumentasi</Label>
                            <PhotoUpload v-model="form.photos" />
                            <div v-if="form.errors.photos" class="text-xs text-destructive mt-1">{{ form.errors.photos }}</div>
                        </div>
                        <div class="space-y-1.5">
                            <Label for="description" class="text-sm font-medium">Catatan / Deskripsi</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Informasi tambahan mengenai POP ini..." class="min-h-[100px] rounded-lg border-slate-200 resize-none" />
                            <div v-if="form.errors.description" class="text-xs text-destructive mt-1">{{ form.errors.description }}</div>
                        </div>
                    </CardContent>
                    <CardFooter class="border-t bg-slate-50/50 p-6 flex justify-end gap-3 rounded-b-lg">
                        <Button variant="ghost" as-child class="font-medium">
                            <Link :href="popIndex().url">Batal</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="px-10 font-bold bg-primary hover:bg-primary/90 rounded-lg">
                            Simpan POP
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
