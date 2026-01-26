<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { index as serverIndex, store as serverStore } from '@/routes/server';
import LocationPicker from '@/components/LocationPicker.vue';
import PhotoUpload from '@/components/PhotoUpload.vue';

defineProps<{
    areas: Array<any>;
    pops: Array<any>;
}>();

const form = useForm({
    name: '',
    code: '',
    function: 'Server',
    area_id: null as number | null,
    pop_id: null as number | null,
    building: '',
    floor: '',
    area_location: '',
    latitude: '',
    longitude: '',
    description: '',
    status: 'Active',
    photos: {
        Room: [] as File[],
        Rack: [] as File[],
        Installation: [] as File[],
        Cabling: [] as File[],
    },
});

const submit = () => {
    form.post(serverStore().url);
};
</script>

<template>
    <Head title="Create Server" />

    <AppLayout :breadcrumbs="[
        { title: 'Servers', href: serverIndex().url },
        { title: 'Create', href: '#' }
    ]">
        <div class="max-w-5xl mx-auto p-4 md:p-6">
            <Card>
                <CardHeader>
                    <CardTitle>Create New Server / Node</CardTitle>
                    <CardDescription>Add a new core network node or server.</CardDescription>
                </CardHeader>
                <form @submit.prevent="submit">
                    <CardContent class="space-y-8">
                        <!-- Section 1: Basic Info -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold border-b pb-2">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="name">Server/Node Name</Label>
                                    <Input id="name" v-model="form.name" required placeholder="e.g. CORE-ROUTER-01" />
                                    <div v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="code">Unique Code</Label>
                                    <Input id="code" v-model="form.code" required placeholder="e.g. SRV-001" />
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
                                            <Input id="building" v-model="form.building" placeholder="e.g. Gedung A" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="floor">Floor</Label>
                                            <Input id="floor" v-model="form.floor" placeholder="e.g. 2" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="area_location">Internal Area / Room</Label>
                                        <Input id="area_location" v-model="form.area_location" placeholder="e.g. Server Room 1" />
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
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold border-b pb-2">Documentation Photos</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label>Room / Environment</Label>
                                    <PhotoUpload v-model="form.photos.Room" />
                                    <div v-if="form.errors['photos.Room']" class="text-sm text-destructive">{{ form.errors['photos.Room'] }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Rack / Cabinet</Label>
                                    <PhotoUpload v-model="form.photos.Rack" />
                                    <div v-if="form.errors['photos.Rack']" class="text-sm text-destructive">{{ form.errors['photos.Rack'] }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Device Installation</Label>
                                    <PhotoUpload v-model="form.photos.Installation" />
                                    <div v-if="form.errors['photos.Installation']" class="text-sm text-destructive">{{ form.errors['photos.Installation'] }}</div>
                                </div>
                                <div class="space-y-2">
                                    <Label>Cabling / Wiring</Label>
                                    <PhotoUpload v-model="form.photos.Cabling" />
                                    <div v-if="form.errors['photos.Cabling']" class="text-sm text-destructive">{{ form.errors['photos.Cabling'] }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Additional Notes</Label>
                            <Textarea id="description" v-model="form.description" rows="4" placeholder="..." />
                        </div>

                    </CardContent>
                    <CardFooter class="flex justify-between border-t p-6 mt-6">
                        <Button variant="outline" as-child>
                            <Link :href="serverIndex().url">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">Create Server Node</Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
