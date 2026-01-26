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
import { index as jointBoxIndex, update as jointBoxUpdate } from '@/routes/passive-device/joint-box';

const props = defineProps<{
    jointBox: any;
    areas: Array<any>;
}>();

const form = useForm({
    infrastructure_area_id: props.jointBox.infrastructure_area_id.toString(),
    code: props.jointBox.code,
    name: props.jointBox.name,
    core_capacity: props.jointBox.core_capacity,
    input_count: props.jointBox.input_count,
    output_count: props.jointBox.output_count,
    brand: props.jointBox.brand || '',
    model: props.jointBox.model || '',
    longitude: props.jointBox.longitude || '',
    latitude: props.jointBox.latitude || '',
    description: props.jointBox.description || '',
    is_active: !!props.jointBox.is_active,
    installed_at: props.jointBox.installed_at || '',
});

const submit = () => {
    form.put(jointBoxUpdate(props.jointBox.id).url);
};
</script>

<template>
    <Head title="Edit Joint Box" />

    <AppLayout :breadcrumbs="[
        { title: 'Joint Boxes', href: jointBoxIndex().url },
        { title: 'Edit Joint Box', href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Joint Box</h1>
                    <p class="text-muted-foreground">Update Splice Enclosure asset details.</p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <Card>
                    <CardHeader>
                        <CardTitle>Joint Box Information</CardTitle>
                        <CardDescription>
                            Update technical details for {{ jointBox.name }}.
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
                                <Label for="core_capacity">Core Capacity</Label>
                                <Input id="core_capacity" type="number" v-model="form.core_capacity" :class="{ 'border-destructive': form.errors.core_capacity }" />
                                <p v-if="form.errors.core_capacity" class="text-sm text-destructive">{{ form.errors.core_capacity }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="input_count">Input Count</Label>
                                <Input id="input_count" type="number" v-model="form.input_count" :class="{ 'border-destructive': form.errors.input_count }" />
                                <p v-if="form.errors.input_count" class="text-sm text-destructive">{{ form.errors.input_count }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="output_count">Output Count</Label>
                                <Input id="output_count" type="number" v-model="form.output_count" :class="{ 'border-destructive': form.errors.output_count }" />
                                <p v-if="form.errors.output_count" class="text-sm text-destructive">{{ form.errors.output_count }}</p>
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
                            <Link :href="jointBoxIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update Joint Box' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
