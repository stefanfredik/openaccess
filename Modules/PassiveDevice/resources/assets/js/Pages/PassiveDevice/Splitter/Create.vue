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
import { index as splitterIndex, store as splitterStore } from '@/routes/passive-device/splitter';

defineProps<{
    areas: Array<any>;
}>();

const form = useForm({
    infrastructure_area_id: '',
    code: '',
    name: '',
    ratio: '',
    loss_db: '',
    brand: '',
    model: '',
    longitude: '',
    latitude: '',
    description: '',
    is_active: true,
    installed_at: '',
});

const submit = () => {
    form.post(splitterStore().url);
};
</script>

<template>
    <Head title="Add Splitter" />

    <AppLayout :breadcrumbs="[
        { title: 'Splitters', href: splitterIndex().url },
        { title: 'Add Splitter', href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Add Splitter</h1>
                    <p class="text-muted-foreground">Register a new PLC/FBT Optical Splitter.</p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Splitter Information</CardTitle>
                        <CardDescription>
                            Ratio and loss specifications of the optical splitter.
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
                                <Input id="code" v-model="form.code" :class="{ 'border-destructive': form.errors.code }" placeholder="SPL-XYZ" />
                                <p v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" :class="{ 'border-destructive': form.errors.name }" placeholder="Box A Splitter 1" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="ratio">Ratio</Label>
                                <Input id="ratio" v-model="form.ratio" :class="{ 'border-destructive': form.errors.ratio }" placeholder="1:8, 1:16, etc." />
                                <p v-if="form.errors.ratio" class="text-sm text-destructive">{{ form.errors.ratio }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="loss_db">Insertion Loss (dB)</Label>
                                <Input id="loss_db" v-model="form.loss_db" :class="{ 'border-destructive': form.errors.loss_db }" placeholder="3.5, 7.2, etc." />
                                <p v-if="form.errors.loss_db" class="text-sm text-destructive">{{ form.errors.loss_db }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="brand">Brand</Label>
                                <Input id="brand" v-model="form.brand" :class="{ 'border-destructive': form.errors.brand }" />
                                <p v-if="form.errors.brand" class="text-sm text-destructive">{{ form.errors.brand }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="model">Model</Label>
                                <Input id="model" v-model="form.model" :class="{ 'border-destructive': form.errors.model }" />
                                <p v-if="form.errors.model" class="text-sm text-destructive">{{ form.errors.model }}</p>
                            </div>
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
                            <Link :href="splitterIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Splitter' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
