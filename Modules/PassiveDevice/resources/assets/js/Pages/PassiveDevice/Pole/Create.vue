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
// import { index as poleIndex, store as poleStore } from '@/routes/passive-device/pole';
import { onMounted } from 'vue';

defineProps<{
    areas: Array<any>;
    materials: Array<string>;
    ownerships: Array<string>;
}>();

const form = useForm({
    infrastructure_area_id: '',
    code: '',
    name: '',
    height: '',
    material: '',
    ownership: '',
    pole_number: '',
    longitude: '',
    latitude: '',
    description: '',
    is_active: true,
    installed_at: '',
});

const submit = () => {
    form.post(route('passive-device.pole.store'));
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const lat = params.get('lat');
    const lng = params.get('lng');
    const areaId = params.get('area_id');

    if (lat) form.latitude = lat;
    if (lng) form.longitude = lng;
    if (areaId) form.infrastructure_area_id = areaId;
});
</script>

<template>
    <Head title="Add Pole" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Poles', href: route('passive-device.pole.index') },
            { title: 'Add Pole', href: '#' },
        ]"
    >
        <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Add Pole</h1>
                    <p class="text-muted-foreground">
                        Register a new Pole infrastructure asset.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Pole Information</CardTitle>
                        <CardDescription>
                            Physical characteristics and location data.
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
                                <Label for="code">Code</Label>
                                <Input
                                    id="code"
                                    v-model="form.code"
                                    :class="{
                                        'border-destructive': form.errors.code,
                                    }"
                                    placeholder="POLE-XYZ"
                                />
                                <p
                                    v-if="form.errors.code"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.code }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                :class="{
                                    'border-destructive': form.errors.name,
                                }"
                                placeholder="Jl. Merdeka Pole 1"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="height">Height (meters)</Label>
                                <Input
                                    id="height"
                                    type="number"
                                    step="0.1"
                                    v-model="form.height"
                                    :class="{
                                        'border-destructive':
                                            form.errors.height,
                                    }"
                                />
                                <p
                                    v-if="form.errors.height"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.height }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="material">Material</Label>
                                <Select v-model="form.material">
                                    <SelectTrigger
                                        :class="{
                                            'border-destructive':
                                                form.errors.material,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Select Material"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="material in materials"
                                            :key="material"
                                            :value="material"
                                        >
                                            {{ material }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="form.errors.material"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.material }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="ownership">Ownership</Label>
                                <Select v-model="form.ownership">
                                    <SelectTrigger
                                        :class="{
                                            'border-destructive':
                                                form.errors.ownership,
                                        }"
                                    >
                                        <SelectValue
                                            placeholder="Select Ownership"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="ownership in ownerships"
                                            :key="ownership"
                                            :value="ownership"
                                        >
                                            {{ ownership }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p
                                    v-if="form.errors.ownership"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.ownership }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="pole_number"
                                >Pole Number (PLN/Provider)</Label
                            >
                            <Input
                                id="pole_number"
                                v-model="form.pole_number"
                                :class="{
                                    'border-destructive':
                                        form.errors.pole_number,
                                }"
                            />
                            <p
                                v-if="form.errors.pole_number"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.pole_number }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                        </div>

                        <div class="space-y-2">
                            <Label for="installed_at">Installed At</Label>
                            <Input
                                id="installed_at"
                                type="date"
                                v-model="form.installed_at"
                                :class="{
                                    'border-destructive':
                                        form.errors.installed_at,
                                }"
                            />
                            <p
                                v-if="form.errors.installed_at"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.installed_at }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                :class="{
                                    'border-destructive':
                                        form.errors.description,
                                }"
                            />
                            <p
                                v-if="form.errors.description"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.description }}
                            </p>
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
                            <Link :href="route('passive-device.pole.index')"
                                >Cancel</Link
                            >
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Pole' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
