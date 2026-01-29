<script setup lang="ts">
import InterfaceManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/InterfaceManager.vue';
import FileUploader from '@/components/FileUploader.vue';
import InputMap from '@/components/InputMap.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    olt: any;
    areas: Array<any>;
    pops: Array<any>;
}>();

const form = useForm({
    _method: 'put',
    infrastructure_area_id: props.olt.infrastructure_area_id.toString(),
    pop_id: props.olt.pop_id ? props.olt.pop_id.toString() : '',
    code: props.olt.code,
    name: props.olt.name,
    brand: props.olt.brand || '',
    model: props.olt.model || '',
    serial_number: props.olt.serial_number || '',
    mac_address: props.olt.mac_address || '',
    ip_address: props.olt.ip_address || '',
    username: props.olt.username || '',
    password: props.olt.password || '', // Usually explicit password fields are empty on edit
    service_status: props.olt.service_status || [],
    purchase_year: props.olt.purchase_year || '',
    pon_type: props.olt.pon_type || '',
    latitude: props.olt.latitude || '',
    longitude: props.olt.longitude || '',
    status: props.olt.status || 'Active',
    installed_at: props.olt.installed_at || '',
    description: props.olt.description || '',
    device_image: null as File | null,
    is_active: !!props.olt.is_active,
    service_ports: props.olt.service_ports || [],
});

const submit = () => {
    form.post(route('active-device.olt.update', props.olt.id));
};

const addServicePort = () => {
    form.service_ports.push({ name: '', port: '', status: 'Active' });
};

const removeServicePort = (index: number) => {
    form.service_ports.splice(index, 1);
};

const serviceOptions = ['Telnet', 'SSH', 'WEB'];
</script>

<template>
    <Head title="Edit OLT" />

    <AppLayout
        :breadcrumbs="[
            { title: 'OLT', href: route('active-device.olt.index') },
            { title: 'Edit OLT', href: '#' },
        ]"
    >
        <div class="mx-4 max-w-4xl space-y-6 p-4 md:p-4">
            <!-- Header -->
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">Edit OLT</h1>
                <p class="text-sm text-foreground/60 text-muted-foreground">
                    Perbarui informasi perangkat OLT.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Informasi Dasar -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold"
                            >Informasi Dasar</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >Identifikasi dasar dan lokasi
                            perangkat.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="grid gap-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label for="area" class="text-sm font-medium"
                                    >Wilayah</Label
                                >
                                <Select v-model="form.infrastructure_area_id">
                                    <SelectTrigger
                                        class="h-11 w-full rounded-lg border-slate-200"
                                        :class="{
                                            'border-destructive':
                                                form.errors
                                                    .infrastructure_area_id,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Pilih Wilayah"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="area in areas"
                                            :key="area.id"
                                            :value="area.id.toString()"
                                        >
                                            {{ area.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div
                                    v-if="form.errors.infrastructure_area_id"
                                    class="mt-1 text-xs text-destructive"
                                >
                                    {{ form.errors.infrastructure_area_id }}
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="pop" class="text-sm font-medium"
                                    >POP</Label
                                >
                                <Select v-model="form.pop_id">
                                    <SelectTrigger
                                        class="h-11 w-full rounded-lg border-slate-200"
                                        :class="{
                                            'border-destructive':
                                                form.errors.pop_id,
                                        }"
                                    >
                                        <SelectValue placeholder="Pilih POP" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="pop in pops"
                                            :key="pop.id"
                                            :value="pop.id.toString()"
                                        >
                                            {{ pop.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div
                                    v-if="form.errors.pop_id"
                                    class="mt-1 text-xs text-destructive"
                                >
                                    {{ form.errors.pop_id }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label for="code" class="text-sm font-medium"
                                    >Kode OLT</Label
                                >
                                <Input
                                    id="code"
                                    v-model="form.code"
                                    placeholder="OLT-XYZ"
                                    class="h-11 rounded-lg border-slate-200"
                                    :class="{
                                        'border-destructive': form.errors.code,
                                    }"
                                />
                                <div
                                    v-if="form.errors.code"
                                    class="mt-1 text-xs text-destructive"
                                >
                                    {{ form.errors.code }}
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="name" class="text-sm font-medium"
                                    >Hostname</Label
                                >
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="OLT Core A"
                                    class="h-11 rounded-lg border-slate-200"
                                    :class="{
                                        'border-destructive': form.errors.name,
                                    }"
                                />
                                <div
                                    v-if="form.errors.name"
                                    class="mt-1 text-xs text-destructive"
                                >
                                    {{ form.errors.name }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label for="brand" class="text-sm font-medium"
                                    >Merek/Vendor</Label
                                >
                                <Input
                                    id="brand"
                                    v-model="form.brand"
                                    class="h-11 rounded-lg border-slate-200"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <Label for="model" class="text-sm font-medium"
                                    >Tipe Perangkat</Label
                                >
                                <Input
                                    id="model"
                                    v-model="form.model"
                                    class="h-11 rounded-lg border-slate-200"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label
                                    for="serial_number"
                                    class="text-sm font-medium"
                                    >Serial Number</Label
                                >
                                <Input
                                    id="serial_number"
                                    v-model="form.serial_number"
                                    class="h-11 rounded-lg border-slate-200"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <Label
                                    for="mac_address"
                                    class="text-sm font-medium"
                                    >MAC Address</Label
                                >
                                <Input
                                    id="mac_address"
                                    v-model="form.mac_address"
                                    class="h-11 rounded-lg border-slate-200"
                                />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label
                                    for="pon_type"
                                    class="text-sm font-medium"
                                    >Tipe PON</Label
                                >
                                <Select v-model="form.pon_type">
                                    <SelectTrigger
                                        class="h-11 w-full rounded-lg border-slate-200"
                                    >
                                        <SelectValue
                                            placeholder="Pilih Tipe PON"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="GPON"
                                            >GPON</SelectItem
                                        >
                                        <SelectItem value="EPON"
                                            >EPON</SelectItem
                                        >
                                        <SelectItem value="XGPON"
                                            >XGPON</SelectItem
                                        >
                                        <SelectItem value="Combo"
                                            >Combo</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Informasi Jaringan & Autentikasi -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold"
                            >Jaringan & Autentikasi</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >Detail koneksi, IP Address dan kredensial
                            akses.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="grid gap-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label
                                    for="ip_address"
                                    class="text-sm font-medium"
                                    >IP Address</Label
                                >
                                <Input
                                    id="ip_address"
                                    v-model="form.ip_address"
                                    class="h-11 rounded-lg border-slate-200"
                                    :class="{
                                        'border-destructive':
                                            form.errors.ip_address,
                                    }"
                                />
                                <div
                                    v-if="form.errors.ip_address"
                                    class="mt-1 text-xs text-destructive"
                                >
                                    {{ form.errors.ip_address }}
                                </div>
                            </div>
                            <div class="space-y-1.5">
                                <Label class="text-sm font-medium"
                                    >Service Status</Label
                                >
                                <div class="mt-2 flex gap-4">
                                    <div
                                        v-for="service in serviceOptions"
                                        :key="service"
                                        class="flex items-center space-x-2"
                                    >
                                        <Checkbox
                                            :id="'service-' + service"
                                            :checked="
                                                form.service_status.includes(
                                                    service,
                                                )
                                            "
                                            @update:checked="
                                                (checked) => {
                                                    if (checked)
                                                        form.service_status.push(
                                                            service,
                                                        );
                                                    else
                                                        form.service_status =
                                                            form.service_status.filter(
                                                                (s) =>
                                                                    s !==
                                                                    service,
                                                            );
                                                }
                                            "
                                        />
                                        <Label
                                            :for="'service-' + service"
                                            class="font-normal"
                                            >{{ service }}</Label
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label
                                    for="username"
                                    class="text-sm font-medium"
                                    >Username</Label
                                >
                                <Input
                                    id="username"
                                    v-model="form.username"
                                    class="h-11 rounded-lg border-slate-200"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <Label
                                    for="password"
                                    class="text-sm font-medium"
                                    >Password</Label
                                >
                                <Input
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    placeholder="Leave empty to keep current"
                                    class="h-11 rounded-lg border-slate-200"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Service Ports -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-base font-semibold"
                                    >Service Ports</CardTitle
                                >
                                <CardDescription class="text-xs"
                                    >Daftar port layanan aktif pada
                                    perangkat.</CardDescription
                                >
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="addServicePort"
                            >
                                <Plus class="mr-2 h-4 w-4" /> Tambah Port
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                        <div
                            v-for="(sp, index) in form.service_ports"
                            :key="index"
                            class="grid grid-cols-12 items-end gap-3 rounded-lg border border-slate-100 bg-slate-50/30 p-3"
                        >
                            <div class="col-span-4 space-y-1.5">
                                <Label class="text-xs">Nama Layanan</Label>
                                <Input
                                    v-model="sp.name"
                                    placeholder="e.g. SSH"
                                    class="h-10 bg-white"
                                />
                            </div>
                            <div class="col-span-3 space-y-1.5">
                                <Label class="text-xs">Port</Label>
                                <Input
                                    type="number"
                                    v-model="sp.port"
                                    placeholder="22"
                                    class="h-10 bg-white"
                                />
                            </div>
                            <div class="col-span-3 space-y-1.5">
                                <Label class="text-xs">Status</Label>
                                <Select v-model="sp.status">
                                    <SelectTrigger class="h-10 bg-white">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active"
                                            >Active</SelectItem
                                        >
                                        <SelectItem value="Inactive"
                                            >Inactive</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="col-span-2 flex justify-end">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    @click="removeServicePort(index)"
                                    class="text-red-500 hover:bg-red-50 hover:text-red-600"
                                >
                                    <Trash2 class="h-5 w-5" />
                                </Button>
                            </div>
                        </div>
                        <div
                            v-if="!form.service_ports.length"
                            class="py-4 text-center text-sm text-muted-foreground italic"
                        >
                            Belum ada service port yang ditambahkan.
                        </div>
                    </CardContent>
                </Card>

                <!-- Foto & Status -->
                <Card class="border shadow-none">
                    <CardHeader class="pb-4">
                        <CardTitle class="text-base font-semibold"
                            >Foto & Status</CardTitle
                        >
                        <CardDescription class="text-xs"
                            >Dokumentasi fisik dan status operasional
                            perangkat.</CardDescription
                        >
                    </CardHeader>
                    <CardContent class="grid gap-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label
                                    for="purchase_year"
                                    class="text-sm font-medium"
                                    >Tahun Beli</Label
                                >
                                <Input
                                    id="purchase_year"
                                    type="number"
                                    v-model="form.purchase_year"
                                    placeholder="YYYY"
                                    class="h-11 rounded-lg border-slate-200"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <Label
                                    for="installed_at"
                                    class="text-sm font-medium"
                                    >Tanggal Install</Label
                                >
                                <Input
                                    id="installed_at"
                                    type="date"
                                    v-model="form.installed_at"
                                    class="h-11 rounded-lg border-slate-200"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <InputMap
                                v-model:latitude="form.latitude"
                                v-model:longitude="form.longitude"
                                :area-id="form.infrastructure_area_id"
                                :areas="areas"
                            />
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label
                                    for="latitude"
                                    class="text-sm font-medium"
                                    >Latitude</Label
                                >
                                <Input
                                    id="latitude"
                                    v-model="form.latitude"
                                    class="h-11 rounded-lg border-slate-200"
                                />
                            </div>
                            <div class="space-y-1.5">
                                <Label
                                    for="longitude"
                                    class="text-sm font-medium"
                                    >Longitude</Label
                                >
                                <Input
                                    id="longitude"
                                    v-model="form.longitude"
                                    class="h-11 rounded-lg border-slate-200"
                                />
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-sm font-medium"
                                >Foto Perangkat</Label
                            >
                            <FileUploader
                                v-model="form.device_image"
                                accept="image/png, image/jpeg, image/jpg"
                                :max-size="5"
                                :error="form.errors.device_image"
                                :initial-image="
                                    olt.device_image
                                        ? '/storage/' + olt.device_image
                                        : null
                                "
                            />
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="space-y-1.5">
                                <Label for="status" class="text-sm font-medium"
                                    >Status</Label
                                >
                                <Select v-model="form.status">
                                    <SelectTrigger
                                        class="h-11 w-full rounded-lg border-slate-200"
                                    >
                                        <SelectValue
                                            placeholder="Pilih Status"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Planned"
                                            >Planned</SelectItem
                                        >
                                        <SelectItem value="Installed"
                                            >Installed</SelectItem
                                        >
                                        <SelectItem value="Active"
                                            >Active</SelectItem
                                        >
                                        <SelectItem value="Inactive"
                                            >Inactive</SelectItem
                                        >
                                        <SelectItem value="Retired"
                                            >Retired</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="description" class="text-sm font-medium"
                                >Keterangan</Label
                            >
                            <Textarea
                                id="description"
                                v-model="form.description"
                                class="min-h-[100px] resize-none rounded-lg border-slate-200"
                            />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Switch
                                id="is_active"
                                v-model:checked="form.is_active"
                            />
                            <Label for="is_active" class="font-normal"
                                >Status Aktif</Label
                            >
                        </div>
                    </CardContent>
                    <CardFooter
                        class="flex justify-end gap-3 rounded-b-lg border-t bg-slate-50/50 p-6"
                    >
                        <Button variant="outline" as-child>
                            <Link :href="route('active-device.olt.index')"
                                >Batal</Link
                            >
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-primary px-10 font-bold hover:bg-primary/90"
                        >
                            {{
                                form.processing ? 'Menyimpan...' : 'Update OLT'
                            }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>

            <Card class="border shadow-none">
                <CardHeader class="pb-4">
                    <CardTitle class="text-base font-semibold"
                        >Physical Interfaces</CardTitle
                    >
                    <CardDescription class="text-xs"
                        >Kelola port fisik dan status operasional
                        perangkat.</CardDescription
                    >
                </CardHeader>
                <CardContent>
                    <InterfaceManager
                        :olt-id="olt.id"
                        :olt-type="'Modules\\ActiveDevice\\Models\\Olt'"
                        :interfaces="olt.interfaces || []"
                    />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
