<script setup lang="ts">
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
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    cpe: any;
    areas: Array<any>;
    onts: Array<any>;
    routers: Array<any>;
}>();

const form = useForm({
    infrastructure_area_id: props.cpe.infrastructure_area_id.toString(),
    active_device_id: props.cpe.active_device_id?.toString() || '',
    active_device_type: props.cpe.active_device_type || '',
    code: props.cpe.code,
    name: props.cpe.name,
    address: props.cpe.address,
    longitude: props.cpe.longitude || '',
    latitude: props.cpe.latitude || '',
    type: props.cpe.type,
    brand: props.cpe.brand || '',
    model: props.cpe.model || '',
    serial_number: props.cpe.serial_number || '',
    mac_address: props.cpe.mac_address || '',
    status: props.cpe.status,
    installed_at: props.cpe.installed_at || '',
    description: props.cpe.description || '',
});

const submit = () => {
    form.put(route('cpe.update', props.cpe.id));
};
</script>

<template>
    <Head title="Edit CPE" />

    <AppLayout
        :breadcrumbs="[
            { title: 'CPEs', href: route('cpe.index') },
            { title: 'Edit CPE', href: '#' },
        ]"
    >
        <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit CPE</h1>
                    <p class="text-muted-foreground">
                        Modify Customer Premises Equipment details.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>CPE Information</CardTitle>
                        <CardDescription>
                            Update equipment identification and customer
                            details.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="area">Infrastructure Area</Label>
                                <Select v-v-model="form.infrastructure_area_id">
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
                                <Label for="type">CPE Type</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger
                                        :class="{
                                            'border-destructive':
                                                form.errors.type,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Select Type"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="ONT">ONT</SelectItem>
                                        <SelectItem value="Router"
                                            >Router</SelectItem
                                        >
                                        <SelectItem value="Radio CPE"
                                            >Radio CPE</SelectItem
                                        >
                                        <SelectItem value="Modem"
                                            >Modem</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="form.errors.type"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.type }}
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
                                <Label for="name"
                                    >Customer Name / Location</Label
                                >
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

                        <div class="space-y-2">
                            <Label for="address">Full Address</Label>
                            <Input
                                id="address"
                                v-model="form.address"
                                :class="{
                                    'border-destructive': form.errors.address,
                                }"
                            />
                            <p
                                v-if="form.errors.address"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.address }}
                            </p>
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
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Select Status"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Active"
                                            >Active</SelectItem
                                        >
                                        <SelectItem value="Inactive"
                                            >Inactive</SelectItem
                                        >
                                        <SelectItem value="Damaged"
                                            >Damaged</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
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

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                            />
                        </div>
                    </CardContent>
                    <CardFooter
                        class="mt-6 flex justify-end gap-2 border-t p-6"
                    >
                        <Button variant="outline" as-child>
                            <Link :href="route('cpe.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update CPE' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
