<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil, MapPin, Zap, Info } from 'lucide-vue-next';
import { index as popIndex, show as popShow, edit as popEdit } from '@/routes/pop';
import LocationPicker from '@/components/LocationPicker.vue';

defineProps<{
    pop: any;
}>();

const getUiStatus = (status: string) => {
    switch (status) {
        case 'Active':
            return 'default';
        case 'Inactive':
            return 'destructive';
        case 'Planned':
            return 'secondary';
        default:
            return 'outline';
    }
};
</script>

<template>
    <Head :title="pop.name" />

    <AppLayout :breadcrumbs="[
        { title: 'POPs', href: popIndex().url },
        { title: pop.name, href: popShow({ pop: pop.id }).url }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ pop.name }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <Badge variant="outline">{{ pop.code }}</Badge>
                        <Badge :variant="getUiStatus(pop.status)">{{ pop.status }}</Badge>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="popEdit({ pop: pop.id }).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit POP
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Info Card -->
                <Card class="md:col-span-1">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Info class="h-5 w-5" />
                            General Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-1">
                            <span class="text-sm text-muted-foreground">Area</span>
                            <p class="font-medium">{{ pop.area?.name || '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm text-muted-foreground">Description</span>
                            <p class="text-sm">{{ pop.description || 'No description provided.' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm text-muted-foreground">Created At</span>
                            <p class="text-sm">{{ new Date(pop.created_at).toLocaleDateString() }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Power Card -->
                <Card class="md:col-span-1">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Zap class="h-5 w-5 text-yellow-500" />
                            Power Infrastructure
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-1">
                            <span class="text-sm text-muted-foreground">Electrical Capacity</span>
                            <p class="font-bold text-lg">{{ pop.electrical_capacity ? pop.electrical_capacity + ' VA' : '-' }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm text-muted-foreground">Power Source</span>
                            <p class="font-medium">{{ pop.power_source }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm text-muted-foreground">Backup Power</span>
                            <div>
                                <Badge :variant="pop.has_backup_power ? 'default' : 'secondary'">
                                    {{ pop.has_backup_power ? 'Available' : 'None' }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Location Card -->
                <Card class="md:col-span-1">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <MapPin class="h-5 w-5 text-red-500" />
                            Location Details
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-1">
                            <span class="text-sm text-muted-foreground">Coordinates</span>
                            <p class="text-sm font-mono">{{ pop.latitude }}, {{ pop.longitude }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-sm text-muted-foreground">Address</span>
                            <p class="text-sm">{{ pop.address || '-' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="space-y-1">
                                <span class="text-muted-foreground">Village</span>
                                <p>{{ pop.village || '-' }}</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-muted-foreground">District</span>
                                <p>{{ pop.district || '-' }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Map View -->
                <Card>
                    <CardHeader>
                        <CardTitle>Map View</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="pointer-events-none">
                            <LocationPicker 
                                :latitude="pop.latitude" 
                                :longitude="pop.longitude" 
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Photos View -->
                <Card>
                    <CardHeader>
                        <CardTitle>Photos</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="pop.photos && pop.photos.length > 0" class="grid grid-cols-2 gap-4">
                            <div v-for="photo in pop.photos" :key="photo.id" class="aspect-video rounded-md overflow-hidden border">
                                <a :href="'/storage/' + photo.path" target="_blank">
                                    <img :src="'/storage/' + photo.path" class="object-cover w-full h-full hover:scale-105 transition-transform duration-300" />
                                </a>
                            </div>
                        </div>
                        <div v-else class="flex items-center justify-center h-48 bg-muted rounded-md text-muted-foreground">
                            No photos available
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
