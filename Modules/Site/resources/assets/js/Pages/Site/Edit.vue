<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { index as siteIndex, edit as siteEdit, update as siteUpdate } from '@/routes/site';
import LocationPicker from '@/components/LocationPicker.vue';
import PhotoUpload from '@/components/PhotoUpload.vue';
import { Switch } from '@/components/ui/switch';

const props = defineProps<{
    site: any;
    areas: Array<any>;
}>();

const form = useForm({
    name: props.site.name,
    code: props.site.code,
    type: props.site.type,
    area_id: props.site.area_id,
    latitude: props.site.latitude,
    longitude: props.site.longitude,
    address: props.site.address,
    description: props.site.description,
    status: props.site.status,
    electrical_capacity: props.site.electrical_capacity,
    power_source: props.site.power_source,
    has_backup_power: !!props.site.has_backup_power, // Ensure boolean
    photos: [] as File[],
});

const submit = () => {
    form.put(siteUpdate({ site: props.site.id }).url);
};
</script>

<template>
    <Head title="Edit Site" />

    <AppLayout :breadcrumbs="[
        { title: 'Sites', href: siteIndex().url },
        { title: 'Edit', href: siteEdit({ site: site.id }).url }
    ]">
        <div class="max-w-2xl mx-auto p-4 md:p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Edit Site</CardTitle>
                    <CardDescription>Update site details.</CardDescription>
                </CardHeader>
                <form @submit.prevent="submit">
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" required />
                                <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
                            </div>
                            <div class="space-y-2">
                                <Label for="code">Code (Optional)</Label>
                                <Input id="code" v-model="form.code" />
                                <div v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="type">Type</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Tower">Tower</SelectItem>
                                        <SelectItem value="POP">POP</SelectItem>
                                        <SelectItem value="DC">Data Center</SelectItem>
                                        <SelectItem value="Pole">Pole</SelectItem>
                                        <SelectItem value="Customer">Customer</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.type" class="text-sm text-destructive">{{ form.errors.type }}</div>
                            </div>
                            <div class="space-y-2">
                                <Label for="area">Area</Label>
                                <Select v-model="form.area_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select Area" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="area in areas" :key="area.id" :value="area.id">
                                            {{ area.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.area_id" class="text-sm text-destructive">{{ form.errors.area_id }}</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Location</Label>
                            <LocationPicker 
                                :latitude="form.latitude" 
                                :longitude="form.longitude"
                                @update:latitude="form.latitude = $event"
                                @update:longitude="form.longitude = $event"
                            />
                            <div class="grid grid-cols-2 gap-4 mt-2">
                                <div class="space-y-2">
                                    <Label for="latitude">Latitude</Label>
                                    <Input id="latitude" v-model="form.latitude" placeholder="-6.200000" />
                                    <div v-if="form.errors.latitude" class="text-sm text-destructive">{{ form.errors.latitude }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="longitude">Longitude</Label>
                                    <Input id="longitude" v-model="form.longitude" placeholder="106.816666" />
                                    <div v-if="form.errors.longitude" class="text-sm text-destructive">{{ form.errors.longitude }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="address">Address</Label>
                            <Textarea id="address" v-model="form.address" />
                            <div v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</div>
                        </div>

                        <div class="space-y-2">
                            <Label>Photos</Label>
                            <!-- Existing Photos -->
                             <div v-if="site.photos && site.photos.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div v-for="photo in site.photos" :key="photo.id" class="aspect-square rounded-md overflow-hidden border">
                                    <img :src="'/storage/' + photo.path" class="object-cover w-full h-full" />
                                </div>
                            </div>

                            <Label>Add New Photos</Label>
                            <PhotoUpload v-model="form.photos" />
                             <div v-if="form.errors.photos" class="text-sm text-destructive">{{ form.errors.photos }}</div>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Active">Active</SelectItem>
                                    <SelectItem value="Inactive">Inactive</SelectItem>
                                    <SelectItem value="Planned">Planned</SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.status" class="text-sm text-destructive">{{ form.errors.status }}</div>
                        </div>

                        <!-- Power Infrastructure -->
                         <div class="space-y-4 border p-4 rounded-md">
                            <h3 class="text-lg font-medium">Power Infrastructure</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="electrical_capacity">Electrical Capacity (VA/Watt)</Label>
                                    <Input id="electrical_capacity" type="number" v-model="form.electrical_capacity" placeholder="e.g 1300" />
                                    <div v-if="form.errors.electrical_capacity" class="text-sm text-destructive">{{ form.errors.electrical_capacity }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="power_source">Power Source</Label>
                                    <Select v-model="form.power_source">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select Source" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="PLN">PLN</SelectItem>
                                            <SelectItem value="Solar">Solar</SelectItem>
                                            <SelectItem value="Genset">Genset</SelectItem>
                                            <SelectItem value="Hybrid">Hybrid</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.power_source" class="text-sm text-destructive">{{ form.errors.power_source }}</div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <Switch id="backup-power" :checked="form.has_backup_power" @update:checked="form.has_backup_power = $event" />
                                <Label for="backup-power">Has Backup Power?</Label>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" />
                            <div v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</div>
                        </div>

                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Button variant="outline" as-child>
                            <Link :href="siteIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">Update Site</Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
