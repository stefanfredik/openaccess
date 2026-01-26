<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { index as serverIndex, edit as serverEdit, update as serverUpdate } from '@/routes/server';
import LocationPicker from '@/components/LocationPicker.vue';
import PhotoUpload from '@/components/PhotoUpload.vue';

const props = defineProps<{
    server: any;
    areas: Array<any>;
    pops: Array<any>;
}>();

const form = useForm({
    _method: 'put',
    name: props.server.name,
    code: props.server.code,
    function: props.server.function,
    area_id: props.server.area_id,
    pop_id: props.server.pop_id,
    building: props.server.building,
    floor: props.server.floor,
    area_location: props.server.area_location,
    latitude: props.server.latitude,
    longitude: props.server.longitude,
    description: props.server.description,
    status: props.server.status,
    photos: {
        Room: [] as File[],
        Rack: [] as File[],
        Installation: [] as File[],
        Cabling: [] as File[],
    },
});

const submit = () => {
    form.post(serverUpdate({ server: props.server.id }).url, {
        onSuccess: () => form.reset('photos'),
    });
};

const getPhotosByCategory = (category: string) => {
    return props.server.photos?.filter((p: any) => p.category === category) || [];
};
</script>

<template>
    <Head title="Edit Server" />

    <AppLayout :breadcrumbs="[
        { title: 'Servers', href: serverIndex().url },
        { title: 'Edit', href: serverEdit({ server: server.id }).url }
    ]">
        <div class="max-w-5xl mx-auto p-4 md:p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Edit Server / Node</CardTitle>
                    <CardDescription>Update infrastructure node details.</CardDescription>
                </CardHeader>
                <form @submit.prevent="submit">
                    <CardContent class="space-y-8">
                         <!-- Section 1: Basic Info -->
                         <div class="space-y-4">
                            <h3 class="text-lg font-semibold border-b pb-2">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="name">Server/Node Name</Label>
                                    <Input id="name" v-model="form.name" required />
                                    <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="code">Unique Code</Label>
                                    <Input id="code" v-model="form.code" required />
                                    <div v-if="form.errors.code" class="text-sm text-destructive">{{ form.errors.code }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="function">Function</Label>
                                    <Select v-model="form.function">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select Function" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="Server">Server</SelectItem>
                                            <SelectItem value="OLT">OLT</SelectItem>
                                            <SelectItem value="Core Network">Core Network</SelectItem>
                                            <SelectItem value="NOC">NOC</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="form.errors.function" class="text-sm text-destructive">{{ form.errors.function }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="status">Status</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select Status" />
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
                        </div>

                        <!-- Section 2: Placement Details -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold border-b pb-2">Location & Placement</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
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
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="pop">Linked POP (Optional)</Label>
                                            <Select v-model="form.pop_id">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select POP" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="pop in pops" :key="pop.id" :value="pop.id">
                                                        {{ pop.name }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label for="building">Building Name</Label>
                                            <Input id="building" v-model="form.building" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="floor">Floor</Label>
                                            <Input id="floor" v-model="form.floor" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="area_location">Internal Area / Room</Label>
                                        <Input id="area_location" v-model="form.area_location" />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label>Coordinates</Label>
                                    <LocationPicker 
                                        :latitude="form.latitude" 
                                        :longitude="form.longitude"
                                        @update:latitude="form.latitude = $event"
                                        @update:longitude="form.longitude = $event"
                                    />
                                    <div class="grid grid-cols-2 gap-2 mt-2">
                                        <Input v-model="form.latitude" placeholder="Latitude" class="text-xs" />
                                        <Input v-model="form.longitude" placeholder="Longitude" class="text-xs" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Documentation Photos -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-semibold border-b pb-2">Documentation Photos</h3>
                            
                            <div v-for="category in ['Room', 'Rack', 'Installation', 'Cabling']" :key="category" class="space-y-3 p-4 border rounded-md">
                                <Label class="text-primary font-bold">{{ category }} Photos</Label>
                                
                                <!-- Existing -->
                                <div v-if="getPhotosByCategory(category).length > 0" class="grid grid-cols-4 md:grid-cols-6 gap-2 mb-3">
                                    <div v-for="photo in getPhotosByCategory(category)" :key="photo.id" class="aspect-square rounded border overflow-hidden">
                                        <img :src="'/storage/' + photo.path" class="object-cover w-full h-full" />
                                    </div>
                                </div>

                                <!-- Upload New -->
                                <PhotoUpload v-model="form.photos[category as keyof typeof form.photos]" />
                                <div v-if="form.errors[`photos.${category}`]" class="text-sm text-destructive">{{ form.errors[`photos.${category}`] }}</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Additional Notes</Label>
                            <Textarea id="description" v-model="form.description" rows="4" />
                        </div>

                    </CardContent>
                    <CardFooter class="flex justify-between border-t p-6 mt-6">
                        <Button variant="outline" as-child>
                            <Link :href="serverIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">Update Server Node</Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
