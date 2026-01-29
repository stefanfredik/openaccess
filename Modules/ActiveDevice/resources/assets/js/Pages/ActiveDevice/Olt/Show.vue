<script setup lang="ts">
import ConnectionManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ConnectionManager.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router as inertiaRouter, Link, useForm } from '@inertiajs/vue3';
import { Activity, ArrowLeft, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    olt: any;
    availableDevices: Array<any>;
}>();

const isAddPortOpen = ref(false);

const form = useForm({
    portable_id: props.olt.id,
    portable_type: 'Modules\\ActiveDevice\\Models\\Olt',
    name: '',
    port: '',
    status: 'Active',
});

const addPort = () => {
    form.post(route('active-device.service-ports.store'), {
        onSuccess: () => {
            isAddPortOpen.value = false;
            form.reset('name', 'port', 'status');
        },
    });
};

const deletePort = (id: number) => {
    if (confirm('Are you sure you want to delete this service port?')) {
        inertiaRouter.delete(route('active-device.service-ports.destroy', id));
    }
};
</script>

<template>
    <Head :title="`OLT: ${olt.name}`" />

    <AppLayout
        :breadcrumbs="[
            { title: 'OLT', href: route('active-device.olt.index') },
            { title: olt.name, href: '#' },
        ]"
    >
        <div class="mx-auto max-w-7xl space-y-6 p-4 md:p-6">
            <!-- Page Header -->
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ olt.name }}
                        </h1>
                        <Badge
                            :variant="
                                olt.status === 'Active'
                                    ? 'default'
                                    : olt.status === 'Planned'
                                      ? 'secondary'
                                      : 'destructive'
                            "
                            class="capitalize"
                        >
                            {{ olt.status }}
                        </Badge>
                    </div>
                    <p
                        class="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <span
                            class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs dark:bg-slate-800"
                            >{{ olt.code }}</span
                        >
                        <span>&bull;</span>
                        <span>{{ olt.brand }} {{ olt.model }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="route('active-device.olt.index')">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="route('active-device.olt.edit', olt.id)">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit OLT
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column: Status & Image -->
                <div class="space-y-6">
                    <Card class="overflow-hidden">
                        <div
                            class="relative flex aspect-video items-center justify-center bg-slate-100 dark:bg-slate-800"
                        >
                            <img
                                v-if="olt.device_image"
                                :src="'/storage/' + olt.device_image"
                                alt="Device Image"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex flex-col items-center gap-2 text-muted-foreground"
                            >
                                <Activity class="h-12 w-12 opacity-20" />
                                <span class="text-xs">No Image Available</span>
                            </div>
                        </div>
                        <CardContent class="grid gap-4 p-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <span
                                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                        >Status</span
                                    >
                                    <div class="font-medium">
                                        {{ olt.status }}
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <span
                                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                        >Installed</span
                                    >
                                    <div class="font-medium">
                                        {{ olt.installed_at || '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-1 border-t pt-4">
                                <span
                                    class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                    >Service Status</span
                                >
                                <div class="mt-1 flex flex-wrap gap-2">
                                    <Badge
                                        variant="secondary"
                                        v-for="status in olt.service_status"
                                        :key="status"
                                        class="h-5 px-2 py-0 text-xs"
                                    >
                                        {{ status }}
                                    </Badge>
                                    <span
                                        v-if="!olt.service_status?.length"
                                        class="text-sm text-muted-foreground"
                                        >-</span
                                    >
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle
                                class="text-sm font-medium tracking-wider text-muted-foreground uppercase"
                                >Networking</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="grid gap-4">
                            <div
                                class="flex items-center justify-between border-b border-dashed py-1 last:border-0"
                            >
                                <span class="text-sm text-muted-foreground"
                                    >IP Address</span
                                >
                                <span
                                    class="font-mono text-sm font-medium text-blue-600 dark:text-blue-400"
                                    >{{ olt.ip_address || '-' }}</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-dashed py-1 last:border-0"
                            >
                                <span class="text-sm text-muted-foreground"
                                    >MAC Address</span
                                >
                                <span class="font-mono text-sm">{{
                                    olt.mac_address || '-'
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-dashed py-1 last:border-0"
                            >
                                <span class="text-sm text-muted-foreground"
                                    >Username</span
                                >
                                <span class="font-mono text-sm">{{
                                    olt.username || '-'
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-dashed py-1 last:border-0"
                            >
                                <span class="text-sm text-muted-foreground"
                                    >Password</span
                                >
                                <span
                                    class="font-mono text-sm text-muted-foreground"
                                    >********</span
                                >
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader
                            class="flex flex-row items-center justify-between pb-3"
                        >
                            <CardTitle
                                class="text-sm font-medium tracking-wider text-muted-foreground uppercase"
                                >Service Ports</CardTitle
                            >
                            <Dialog v-model:open="isAddPortOpen">
                                <DialogTrigger as-child>
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        class="h-8 w-8"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle
                                            >Add Service Port</DialogTitle
                                        >
                                    </DialogHeader>
                                    <form
                                        @submit.prevent="addPort"
                                        class="space-y-6 pt-4"
                                    >
                                        <div class="space-y-2">
                                            <Label
                                                for="name"
                                                class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                                >Service Name</Label
                                            >
                                            <Input
                                                id="name"
                                                v-model="form.name"
                                                class="h-10 w-full"
                                                placeholder="e.g. SSH"
                                                required
                                            />
                                        </div>
                                        <div class="grid grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <Label
                                                    for="port"
                                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                                    >Port Number</Label
                                                >
                                                <Input
                                                    id="port"
                                                    type="number"
                                                    v-model="form.port"
                                                    class="h-10 w-full"
                                                    placeholder="e.g. 22"
                                                    required
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <Label
                                                    for="status"
                                                    class="text-[11px] font-bold tracking-wider text-muted-foreground uppercase"
                                                    >Status</Label
                                                >
                                                <Select v-model="form.status">
                                                    <SelectTrigger
                                                        class="h-10 w-full"
                                                    >
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem
                                                            value="Active"
                                                            >Active</SelectItem
                                                        >
                                                        <SelectItem
                                                            value="Inactive"
                                                            >Inactive</SelectItem
                                                        >
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>
                                        <DialogFooter class="border-t pt-4">
                                            <Button
                                                type="button"
                                                variant="outline"
                                                @click="isAddPortOpen = false"
                                                >Cancel</Button
                                            >
                                            <Button
                                                type="submit"
                                                :disabled="form.processing"
                                                >Save Port</Button
                                            >
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-hidden rounded-md border">
                                <table class="w-full text-sm">
                                    <thead class="border-b bg-muted/50">
                                        <tr>
                                            <th
                                                class="px-4 py-2 text-left font-medium"
                                            >
                                                Service Port
                                            </th>
                                            <th
                                                class="px-4 py-2 text-left font-medium"
                                            >
                                                Port
                                            </th>
                                            <th
                                                class="px-4 py-2 text-center font-medium"
                                            >
                                                Status
                                            </th>
                                            <th
                                                class="px-4 py-2 text-right font-medium"
                                            >
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        <tr
                                            v-for="port in olt.service_ports"
                                            :key="port.id"
                                            class="hover:bg-muted/30"
                                        >
                                            <td class="px-4 py-2">
                                                {{ port.name }}
                                            </td>
                                            <td
                                                class="px-4 py-2 font-mono text-xs"
                                            >
                                                {{ port.port }}
                                            </td>
                                            <td class="px-4 py-2 text-center">
                                                <Badge
                                                    :variant="
                                                        port.status === 'Active'
                                                            ? 'default'
                                                            : 'secondary'
                                                    "
                                                    class="h-5 px-1.5 py-0 text-[10px]"
                                                >
                                                    {{ port.status }}
                                                </Badge>
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-7 w-7 text-destructive"
                                                    @click="deletePort(port.id)"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </td>
                                        </tr>
                                        <tr v-if="!olt.service_ports?.length">
                                            <td
                                                colspan="4"
                                                class="px-4 py-4 text-center text-muted-foreground italic"
                                            >
                                                No service ports defined.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Details & Map -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Location & Specs -->
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader class="pb-3">
                                <CardTitle
                                    class="text-sm font-medium tracking-wider text-muted-foreground uppercase"
                                    >Location</CardTitle
                                >
                            </CardHeader>
                            <CardContent class="grid gap-4">
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground"
                                        >Wilayah (Area)</span
                                    >
                                    <div class="font-medium">
                                        {{ olt.area?.name || '-' }}
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground"
                                        >POP / Site</span
                                    >
                                    <div class="font-medium">
                                        {{ olt.pop?.name || '-' }}
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground"
                                        >Coordinates</span
                                    >
                                    <div
                                        class="flex items-center gap-2 font-mono text-sm"
                                    >
                                        <span
                                            >{{ olt.latitude || '0' }},
                                            {{ olt.longitude || '0' }}</span
                                        >
                                        <a
                                            v-if="olt.latitude && olt.longitude"
                                            :href="`https://www.google.com/maps/search/?api=1&query=${olt.latitude},${olt.longitude}`"
                                            target="_blank"
                                            class="text-xs text-blue-500 hover:underline"
                                        >
                                            (Google Maps)
                                        </a>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader class="pb-3">
                                <CardTitle
                                    class="text-sm font-medium tracking-wider text-muted-foreground uppercase"
                                    >Hardware Specification</CardTitle
                                >
                            </CardHeader>
                            <CardContent class="grid gap-4">
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground"
                                        >Brand / Model</span
                                    >
                                    <div class="font-medium">
                                        {{ olt.brand }} {{ olt.model }}
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-xs text-muted-foreground"
                                        >Serial Number</span
                                    >
                                    <div class="font-mono text-sm">
                                        {{ olt.serial_number || '-' }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >PON Type</span
                                        >
                                        <div class="font-medium">
                                            {{ olt.pon_type || '-' }}
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >Purchase Year</span
                                        >
                                        <div class="font-medium">
                                            {{ olt.purchase_year || '-' }}
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Description -->
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle
                                class="text-sm font-medium tracking-wider text-muted-foreground uppercase"
                                >Keterangan Tambahan</CardTitle
                            >
                        </CardHeader>
                        <CardContent>
                            <p
                                class="text-sm leading-relaxed text-foreground/80"
                            >
                                {{
                                    olt.description ||
                                    'Tidak ada keterangan tambahan.'
                                }}
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Connections -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold tracking-tight">
                                Konektivitas
                            </h3>
                        </div>
                        <ConnectionManager
                            :device="olt"
                            device-type="Modules\ActiveDevice\Models\Olt"
                            :connections="olt.source_connections"
                            :incoming-connections="olt.destination_connections"
                            :available-devices="availableDevices"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
