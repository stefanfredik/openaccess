<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Switch } from '@/components/ui/switch';
import { index as popIndex, edit as popEdit, update as popUpdate } from '@/routes/pop';
import LocationPicker from '@/components/LocationPicker.vue';
import PhotoUpload from '@/components/PhotoUpload.vue';

const props = defineProps<{
    pop: any;
    areas: Array<any>;
}>();

const form = useForm({
    _method: 'put',
    name: props.pop.name,
    code: props.pop.code,
    area_id: props.pop.area_id,
    address: props.pop.address,
    province: props.pop.province,
    regency: props.pop.regency,
    district: props.pop.district,
    village: props.pop.village,
    latitude: props.pop.latitude,
    longitude: props.pop.longitude,
    electrical_capacity: props.pop.electrical_capacity,
    power_source: props.pop.power_source,
    has_backup_power: !!props.pop.has_backup_power,
    description: props.pop.description,
    status: props.pop.status,
    photos: [] as File[],
});

const submit = () => {
    // Note: for file uploads with PUT, we use POST with _method=PUT
    form.post(popUpdate({ pop: props.pop.id }).url);
};
</script>

<template>
    <Head title="Edit POP" />

    <AppLayout :breadcrumbs="[
        { title: 'POPs', href: popIndex().url },
        { title: 'Edit', href: popEdit({ pop: pop.id }).url }
    ]">
        <div class="max-w-4xl mx-auto p-4 md:p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Edit POP</CardTitle>
                    <CardDescription>Update Point of Presence details.</CardDescription>
                </CardHeader>
                <form @submit.prevent="submit">
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Info -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Basic Information</h3>
                                <div class="space-y-2">
                                    <Label for="name">Name</Label>
                                    <Input id="name" v-model="form.name" required />
                                    <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="code">Code</Label>
                                    <Input id="code" v-model="form.code" required />
                                    <div v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="area">Infrastructure Area</Label>
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
                            </div>

                            <!-- Power Infrastructure -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Power Infrastructure</h3>
                                <div class="space-y-2">
                                    <Label for="electrical_capacity">Electrical Capacity (VA/Watt)</Label>
                                    <Input id="electrical_capacity" type="number" v-model="form.electrical_capacity" />
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
                                <div class="flex items-center space-x-2 pt-2">
                                    <Switch id="backup-power" :checked="form.has_backup_power" @update:checked="form.has_backup_power = $event" />
                                    <Label for="backup-power">Has Backup Power?</Label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Location -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Location Details</h3>
                                <div class="space-y-4">
                                    <LocationPicker 
                                        :latitude="form.latitude" 
                                        :longitude="form.longitude"
                                        @update:latitude="form.latitude = $event"
                                        @update:longitude="form.longitude = $event"
                                    />
                                    <div class="grid grid-cols-2 gap-4 mt-2">
                                        <div class="space-y-2">
                                            <Label for="latitude">Latitude</Label>
                                            <Input id="latitude" v-model="form.latitude" />
                                            <div v-if="form.errors.latitude" class="text-sm text-destructive">{{ form.errors.latitude }}</div>
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="longitude">Longitude</Label>
                                            <Input id="longitude" v-model="form.longitude" />
                                            <div v-if="form.errors.longitude" class="text-sm text-destructive">{{ form.errors.longitude }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="address">Address</Label>
                                    <Textarea id="address" v-model="form.address" />
                                    <div v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</div>
                                </div>
                            </div>

                            <!-- Photos & Desc -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">Documentation & Notes</h3>
                                <div class="space-y-2">
                                    <Label>Photos</Label>
                                    <!-- Existing Photos -->
                                    <div v-if="pop.photos && pop.photos.length > 0" class="grid grid-cols-3 gap-2 mb-4">
                                        <div v-for="photo in pop.photos" :key="photo.id" class="aspect-square rounded-md overflow-hidden border">
                                            <img :src="'/storage/' + photo.path" class="object-cover w-full h-full" />
                                        </div>
                                    </div>

                                    <Label>Add New Photos</Label>
                                    <PhotoUpload v-model="form.photos" />
                                    <div v-if="form.errors.photos" class="text-sm text-destructive">{{ form.errors.photos }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="description">Description/Notes</Label>
                                    <Textarea id="description" v-model="form.description" rows="5" />
                                    <div v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</div>
                                </div>
                            </div>
                        </div>

                    </CardContent>
                    <CardFooter class="flex justify-between border-t p-6">
                        <Button variant="outline" as-child>
                            <Link :href="popIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">Update POP</Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
