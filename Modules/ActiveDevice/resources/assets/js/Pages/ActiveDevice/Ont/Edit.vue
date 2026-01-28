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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    ont: any;
    areas: Array<any>;
    pops: Array<any>;
}>();

const form = useForm({
    infrastructure_area_id: props.ont.infrastructure_area_id.toString(),
    pop_id: props.ont.pop_id ? props.ont.pop_id.toString() : '',
    code: props.ont.code,
    name: props.ont.name,
    brand: props.ont.brand || '',
    model: props.ont.model || '',
    serial_number: props.ont.serial_number || '',
    mac_address: props.ont.mac_address || '',
    ip_address: props.ont.ip_address || '',
    onu_type: props.ont.onu_type || '',
    is_active: !!props.ont.is_active,
    installed_at: props.ont.installed_at || '',
    description: props.ont.description || '',
});

const submit = () => {
    form.put(route('active-device.ont.update', props.ont.id));
};
</script>

<template>
    <Head title="Edit ONT" />

    <AppLayout
        :breadcrumbs="[
            { title: 'ONTs', href: route('active-device.ont.index') },
            { title: 'Edit ONT', href: '#' },
        ]"
    >
        <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit ONT</h1>
                    <p class="text-muted-foreground">
                        Update Optical Network Terminal device details.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>ONT Information</CardTitle>
                        <CardDescription>
                            Update device details for {{ ont.name }}.
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
                                <Label for="onu_type">ONT/ONU Type</Label>
                                <Select v-model="form.onu_type">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Select Type"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="GPON"
                                            >GPON</SelectItem
                                        >
                                        <SelectItem value="EPON"
                                            >EPON</SelectItem
                                        >
                                        <SelectItem value="XPON"
                                            >XPON</SelectItem
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
                            <div class="flex items-center space-x-2 pt-8">
                                <Switch
                                    id="is_active"
                                    v-model:checked="form.is_active"
                                />
                                <Label for="is_active">Active Status</Label>
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
                            <Link :href="route('active-device.ont.index')"
                                >Cancel</Link
                            >
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update ONT' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
