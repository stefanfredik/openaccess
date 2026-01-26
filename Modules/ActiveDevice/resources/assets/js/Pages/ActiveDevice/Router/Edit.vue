<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';

const props = defineProps<{
    router: any;
    areas: Array<any>;
    pops: Array<any>;
}>();

const form = useForm({
    infrastructure_area_id: props.router.infrastructure_area_id.toString(),
    pop_id: props.router.pop_id ? props.router.pop_id.toString() : '',
    code: props.router.code,
    name: props.router.name,
    brand: props.router.brand || '',
    model: props.router.model || '',
    serial_number: props.router.serial_number || '',
    mac_address: props.router.mac_address || '',
    ip_address: props.router.ip_address || '',
    port_count: props.router.port_count,
    is_active: !!props.router.is_active,
    installed_at: props.router.installed_at || '',
    description: props.router.description || '',
});

const submit = () => {
    form.put(`/active-devices/router/${props.router.id}`);
};
</script>

<template>
    <Head title="Edit Router" />

    <AppLayout :breadcrumbs="[
        { title: 'Routers', href: '/active-devices/router' },
        { title: 'Edit Router', href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Router</h1>
                    <p class="text-muted-foreground">Update IP Router device details.</p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Router Information</CardTitle>
                        <CardDescription>
                            Update device details for {{ router.name }}.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="area">Infrastructure Area</Label>
                                <Select v-model="form.infrastructure_area_id">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.infrastructure_area_id }">
                                        <SelectValue placeholder="Select Area" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="area in areas" :key="area.id" :value="area.id.toString()">
                                            {{ area.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.infrastructure_area_id" class="text-sm text-destructive">{{ form.errors.infrastructure_area_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="pop">POP (Site)</Label>
                                <Select v-model="form.pop_id">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.pop_id }">
                                        <SelectValue placeholder="Select POP" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="pop in pops" :key="pop.id" :value="pop.id.toString()">
                                            {{ pop.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.pop_id" class="text-sm text-destructive">{{ form.errors.pop_id }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="code">Code</Label>
                                <Input id="code" v-model="form.code" :class="{ 'border-destructive': form.errors.code }" />
                                <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
                                <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="port_count">Port Count</Label>
                                <Input id="port_count" type="number" v-model="form.port_count" />
                            </div>
                            <div class="space-y-2">
                                <Label for="ip_address">IP Address</Label>
                                <Input id="ip_address" v-model="form.ip_address" :class="{ 'border-destructive': form.errors.ip_address }" />
                                <p v-if="form.errors.ip_address" class="text-sm text-destructive">{{ form.errors.ip_address }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="brand">Brand</Label>
                                <Input id="brand" v-model="form.brand" />
                            </div>
                            <div class="space-y-2">
                                <Label for="model">Model</Label>
                                <Input id="model" v-model="form.model" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="serial_number">Serial Number</Label>
                                <Input id="serial_number" v-model="form.serial_number" />
                            </div>
                            <div class="space-y-2">
                                <Label for="mac_address">MAC Address</Label>
                                <Input id="mac_address" v-model="form.mac_address" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="installed_at">Installed At</Label>
                                <Input id="installed_at" type="date" v-model="form.installed_at" />
                            </div>
                            <div class="flex items-center space-x-2 pt-8">
                                <Switch id="is_active" v-model:checked="form.is_active" />
                                <Label for="is_active">Active Status</Label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" />
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2 border-t p-6 mt-6">
                        <Button variant="outline" as-child>
                            <Link href="/active-devices/router">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update Router' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
