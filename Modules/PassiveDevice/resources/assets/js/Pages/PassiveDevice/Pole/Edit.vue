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
import { index as poleIndex, update as poleUpdate } from '@/routes/passive-device/pole';

const props = defineProps<{
    pole: any;
    areas: Array<any>;
    materials: Array<string>;
    ownerships: Array<string>;
}>();

const form = useForm({
    infrastructure_area_id: props.pole.infrastructure_area_id.toString(),
    code: props.pole.code,
    name: props.pole.name,
    height: props.pole.height,
    material: props.pole.material,
    ownership: props.pole.ownership,
    pole_number: props.pole.pole_number || '',
    longitude: props.pole.longitude || '',
    latitude: props.pole.latitude || '',
    description: props.pole.description || '',
    is_active: !!props.pole.is_active,
    installed_at: props.pole.installed_at || '',
});

const submit = () => {
    form.put(poleUpdate(props.pole.id).url);
};
</script>

<template>
    <Head title="Edit Pole" />

    <AppLayout :breadcrumbs="[
        { title: 'Poles', href: poleIndex().url },
        { title: 'Edit Pole', href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Pole</h1>
                    <p class="text-muted-foreground">Update Pole infrastructure asset details.</p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Pole Information</CardTitle>
                        <CardDescription>
                            Update physical details for {{ pole.name }}.
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
                                <Label for="code">Code</Label>
                                <Input id="code" v-model="form.code" :class="{ 'border-destructive': form.errors.code }" />
                                <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <Label for="height">Height (meters)</Label>
                                <Input id="height" type="number" step="0.1" v-model="form.height" :class="{ 'border-destructive': form.errors.height }" />
                                <p v-if="form.errors.height" class="text-sm text-destructive">{{ form.errors.height }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="material">Material</Label>
                                <Select v-model="form.material">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.material }">
                                        <SelectValue placeholder="Select Material" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="material in materials" :key="material" :value="material">
                                            {{ material }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.material" class="text-sm text-destructive">{{ form.errors.material }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="ownership">Ownership</Label>
                                <Select v-model="form.ownership">
                                    <SelectTrigger :class="{ 'border-destructive': form.errors.ownership }">
                                        <SelectValue placeholder="Select Ownership" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="ownership in ownerships" :key="ownership" :value="ownership">
                                            {{ ownership }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.ownership" class="text-sm text-destructive">{{ form.errors.ownership }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="pole_number">Pole Number</Label>
                            <Input id="pole_number" v-model="form.pole_number" :class="{ 'border-destructive': form.errors.pole_number }" />
                            <p v-if="form.errors.pole_number" class="text-sm text-destructive">{{ form.errors.pole_number }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="longitude">Longitude</Label>
                                <Input id="longitude" v-model="form.longitude" :class="{ 'border-destructive': form.errors.longitude }" />
                                <p v-if="form.errors.longitude" class="text-sm text-destructive">{{ form.errors.longitude }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="latitude">Latitude</Label>
                                <Input id="latitude" v-model="form.latitude" :class="{ 'border-destructive': form.errors.latitude }" />
                                <p v-if="form.errors.latitude" class="text-sm text-destructive">{{ form.errors.latitude }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="installed_at">Installed At</Label>
                            <Input id="installed_at" type="date" v-model="form.installed_at" :class="{ 'border-destructive': form.errors.installed_at }" />
                            <p v-if="form.errors.installed_at" class="text-sm text-destructive">{{ form.errors.installed_at }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" :class="{ 'border-destructive': form.errors.description }" />
                            <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Switch id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active">Active Status</Label>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2 border-t p-6 mt-6">
                        <Button variant="outline" as-child>
                            <Link :href="poleIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update Pole' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
