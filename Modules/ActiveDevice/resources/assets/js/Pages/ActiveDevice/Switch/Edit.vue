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
    networkSwitch: any;
    areas: Array<any>;
    pops: Array<any>;
}>();

const form = useForm({
    infrastructure_area_id:
        props.networkSwitch.infrastructure_area_id.toString(),
    pop_id: props.networkSwitch.pop_id
        ? props.networkSwitch.pop_id.toString()
        : '',
    code: props.networkSwitch.code,
    name: props.networkSwitch.name,
    brand: props.networkSwitch.brand || '',
    model: props.networkSwitch.model || '',
    serial_number: props.networkSwitch.serial_number || '',
    mac_address: props.networkSwitch.mac_address || '',
    ip_address: props.networkSwitch.ip_address || '',
    port_count: props.networkSwitch.port_count,
    switch_type: props.networkSwitch.switch_type || '',
    is_active: !!props.networkSwitch.is_active,
    installed_at: props.networkSwitch.installed_at || '',
    description: props.networkSwitch.description || '',
});

const submit = () => {
    form.put(route('active-device.switch.update', props.networkSwitch.id));
};
</script>

<template>
    <Head title="Edit Switch" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Switches', href: route('active-device.switch.index') },
            { title: 'Edit Switch', href: '#' },
        ]"
    >
        <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1>Edit Switch</h1>
                    <p class="text-muted-foreground">
                        Update Network Switch device details.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Switch Information</CardTitle>
                        <CardDescription>
                            Update device details for {{ networkSwitch.name }}.
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
                                <Label for="switch_type">Switch Type</Label>
                                <Select v-model="form.switch_type">
                                    <SelectTrigger>
                                        <SelectValue
                                            placeholder="Select Type"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Access"
                                            >Access</SelectItem
                                        >
                                        <SelectItem value="Aggregation"
                                            >Aggregation</SelectItem
                                        >
                                        <SelectItem value="Core"
                                            >Core</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="port_count">Port Count</Label>
                                <Input
                                    id="port_count"
                                    type="number"
                                    v-model="form.port_count"
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

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                            />
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
                            <Link :href="route('active-device.switch.index')"
                                >Cancel</Link
                            >
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{
                                form.processing
                                    ? 'Updating...'
                                    : 'Update Switch'
                            }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
