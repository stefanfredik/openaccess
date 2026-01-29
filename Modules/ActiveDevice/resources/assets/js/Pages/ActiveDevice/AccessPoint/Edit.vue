<script setup lang="ts">
import MapLocationPicker from '@/components/MapLocationPicker.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    accessPoint: any;
    areas: Array<any>;
    pops: Array<any>;
}>();

const form = useForm({
    infrastructure_area_id: props.accessPoint.infrastructure_area_id.toString(),
    pop_id: props.accessPoint.pop_id ? props.accessPoint.pop_id.toString() : '',
    code: props.accessPoint.code,
    name: props.accessPoint.name,
    brand: props.accessPoint.brand || '',
    model: props.accessPoint.model || '',
    serial_number: props.accessPoint.serial_number || '',
    mac_address: props.accessPoint.mac_address || '',
    ip_address: props.accessPoint.ip_address || '',
    frequency: props.accessPoint.frequency || '',
    ssid_count: props.accessPoint.ssid_count,
    is_active: !!props.accessPoint.is_active,
    installed_at: props.accessPoint.installed_at || '',
    latitude: props.accessPoint.latitude || '',
    longitude: props.accessPoint.longitude || '',
    description: props.accessPoint.description || '',
    service_ports: props.accessPoint.service_ports || [],
});

const submit = () => {
    form.put(route('active-device.access-point.update', props.accessPoint.id));
};

const addServicePort = () => {
    form.service_ports.push({ name: '', port: '', status: 'Active' });
};

const removeServicePort = (index: number) => {
    form.service_ports.splice(index, 1);
};
</script>

<template>
    <Head title="Edit Access Point" />

    <AppLayout
        :breadcrumbs="[
            {
                title: 'Access Points',
                href: route('active-device.access-point.index'),
            },
            { title: 'Edit AP', href: '#' },
        ]"
    >
        <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Edit Access Point
                    </h1>
                    <p class="text-muted-foreground">
                        Update Wireless Radio / Access Point details.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>AP Information</CardTitle>
                        <CardDescription>
                            Update device details for {{ accessPoint.name }}.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="area">Infrastructure Area</Label>
                                <Select v-model="form.infrastructure_area_id">
                                    <SelectTrigger
                                        :class="{
                                            'border-destructive':
                                                form.errors
                                                    .infrastructure_area_id,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Select Area"
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
                                <p
                                    v-if="form.errors.infrastructure_area_id"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.infrastructure_area_id }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="pop">POP (Site)</Label>
                                <Select v-model="form.pop_id">
                                    <SelectTrigger
                                        :class="{
                                            'border-destructive':
                                                form.errors.pop_id,
                                        }"
                                    >
                                        <SelectValue placeholder="Select POP" />
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
                                <p
                                    v-if="form.errors.pop_id"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.pop_id }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="code">Code</Label>
                                <Input
                                    id="code"
                                    v-model="form.code"
                                    :class="{
                                        'border-destructive': form.errors.code,
                                    }"
                                />
                                <p
                                    v-if="form.errors.code"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.code }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    :class="{
                                        'border-destructive': form.errors.name,
                                    }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="frequency">Frequency</Label>
                                <Select v-model="form.frequency">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Select Frequency"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="2.4GHz"
                                            >2.4 GHz</SelectItem
                                        >
                                        <SelectItem value="5GHz"
                                            >5 GHz</SelectItem
                                        >
                                        <SelectItem value="Dual Band"
                                            >Dual Band</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="ssid_count">SSID Count</Label>
                                <Input
                                    id="ssid_count"
                                    type="number"
                                    v-model="form.ssid_count"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="brand">Brand</Label>
                                <Input id="brand" v-model="form.brand" />
                            </div>
                            <div class="space-y-2">
                                <Label for="model">Model</Label>
                                <Input id="model" v-model="form.model" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="serial_number">Serial Number</Label>
                                <Input
                                    id="serial_number"
                                    v-model="form.serial_number"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="mac_address">MAC Address</Label>
                                <Input
                                    id="mac_address"
                                    v-model="form.mac_address"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="ip_address">IP Address</Label>
                                <Input
                                    id="ip_address"
                                    v-model="form.ip_address"
                                    :class="{
                                        'border-destructive':
                                            form.errors.ip_address,
                                    }"
                                />
                                <p
                                    v-if="form.errors.ip_address"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.ip_address }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="installed_at">Installed At</Label>
                                <Input
                                    id="installed_at"
                                    type="date"
                                    v-model="form.installed_at"
                                />
                            </div>
                        </div>

                        <MapLocationPicker
                            v-model:latitude="form.latitude"
                            v-model:longitude="form.longitude"
                            :area-id="form.infrastructure_area_id"
                            :areas="areas"
                        />

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="latitude">Latitude</Label>
                                <Input
                                    id="latitude"
                                    v-model="form.latitude"
                                    :class="{
                                        'border-destructive':
                                            form.errors.latitude,
                                    }"
                                />
                                <p
                                    v-if="form.errors.latitude"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.latitude }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="longitude">Longitude</Label>
                                <Input
                                    id="longitude"
                                    v-model="form.longitude"
                                    :class="{
                                        'border-destructive':
                                            form.errors.longitude,
                                    }"
                                />
                                <p
                                    v-if="form.errors.longitude"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.longitude }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                            />
                        </div>

                        <!-- Service Ports -->
                        <div class="mt-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium">
                                    Service Ports
                                </h3>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="addServicePort"
                                >
                                    <Plus class="mr-2 h-4 w-4" /> Add Port
                                </Button>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="(sp, index) in form.service_ports"
                                    :key="index"
                                    class="grid grid-cols-12 items-end gap-3 rounded-lg border p-3"
                                >
                                    <div class="col-span-4 space-y-1.5">
                                        <Label class="text-xs">Name</Label>
                                        <Input
                                            v-model="sp.name"
                                            placeholder="e.g. SSH"
                                            class="h-9"
                                        />
                                    </div>
                                    <div class="col-span-3 space-y-1.5">
                                        <Label class="text-xs">Port</Label>
                                        <Input
                                            type="number"
                                            v-model="sp.port"
                                            placeholder="22"
                                            class="h-9"
                                        />
                                    </div>
                                    <div class="col-span-3 space-y-1.5">
                                        <Label class="text-xs">Status</Label>
                                        <Select v-model="sp.status">
                                            <SelectTrigger class="h-9">
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
                                            class="text-destructive"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                                <div
                                    v-if="!form.service_ports.length"
                                    class="py-4 text-center text-sm text-muted-foreground italic"
                                >
                                    No service ports added.
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Switch
                                id="is_active"
                                v-model:checked="form.is_active"
                            />
                            <Label for="is_active">Active Status</Label>
                        </div>
                    </CardContent>
                    <CardFooter
                        class="mt-6 flex justify-end gap-2 border-t p-6"
                    >
                        <Button variant="outline" as-child>
                            <Link
                                :href="
                                    route('active-device.access-point.index')
                                "
                                >Cancel</Link
                            >
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update AP' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
