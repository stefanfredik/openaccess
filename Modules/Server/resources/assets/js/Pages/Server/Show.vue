<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Cpu,
    Image as ImageIcon,
    Info,
    MapPin,
    Pencil,
    Server as ServerIcon,
} from 'lucide-vue-next';
// import { index as serverIndex, show as serverShow, edit as serverEdit, destroy as serverDestroy } from '@/routes/server';
import DeleteAction from '@/components/DeleteAction.vue';
import LocationPicker from '@/components/LocationPicker.vue';

const props = defineProps<{
    server: any;
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

const getPhotosByCategory = (category: string) => {
    return (
        props.server.photos?.filter((p: any) => p.category === category) || []
    );
};
</script>

<template>
    <Head :title="server.name" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Servers', href: route('server.index') },
            { title: server.name, href: route('server.show', server.id) },
        ]"
    >
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-primary/10 p-3">
                        <ServerIcon class="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            {{ server.name }}
                        </h1>
                        <div class="mt-1 flex items-center gap-2">
                            <Badge variant="outline">{{ server.code }}</Badge>
                            <Badge variant="secondary">{{
                                server.function
                            }}</Badge>
                            <Badge :variant="getUiStatus(server.status)">{{
                                server.status
                            }}</Badge>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child>
                        <Link :href="route('server.edit', server.id)">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit Server
                        </Link>
                    </Button>
                    <DeleteAction :href="route('server.destroy', server.id)" />
                </div>
            </div>

            <Tabs default-value="overview" class="w-full">
                <TabsList class="grid w-full grid-cols-3 md:w-auto">
                    <TabsTrigger
                        value="overview"
                        class="flex items-center gap-2"
                    >
                        <Info class="h-4 w-4" />
                        Overview
                    </TabsTrigger>
                    <TabsTrigger value="photos" class="flex items-center gap-2">
                        <ImageIcon class="h-4 w-4" />
                        Photos
                    </TabsTrigger>
                    <TabsTrigger
                        value="devices"
                        class="flex items-center gap-2"
                    >
                        <Cpu class="h-4 w-4" />
                        Devices
                    </TabsTrigger>
                </TabsList>

                <!-- Overview Tab -->
                <TabsContent value="overview" class="mt-4 space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Placement Details</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid grid-cols-2 gap-y-4 text-sm">
                                    <div class="text-muted-foreground">
                                        Area
                                    </div>
                                    <div class="font-medium text-primary">
                                        {{ server.area?.name || '-' }}
                                    </div>

                                    <div class="text-muted-foreground">
                                        Linked POP
                                    </div>
                                    <div>
                                        {{
                                            server.pop?.name ||
                                            'No direct POP link'
                                        }}
                                    </div>

                                    <div class="text-muted-foreground">
                                        Building
                                    </div>
                                    <div>{{ server.building || '-' }}</div>

                                    <div class="text-muted-foreground">
                                        Floor / Level
                                    </div>
                                    <div>{{ server.floor || '-' }}</div>

                                    <div class="text-muted-foreground">
                                        Internal Room/Area
                                    </div>
                                    <div>{{ server.area_location || '-' }}</div>
                                </div>
                                <div class="border-t pt-4">
                                    <p
                                        class="mb-1 text-sm text-muted-foreground"
                                    >
                                        Description
                                    </p>
                                    <p class="text-sm">
                                        {{
                                            server.description ||
                                            'No description provided.'
                                        }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Geographical Location</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div
                                    class="pointer-events-none overflow-hidden rounded-md border"
                                >
                                    <LocationPicker
                                        :latitude="server.latitude"
                                        :longitude="server.longitude"
                                    />
                                </div>
                                <div
                                    class="grid grid-cols-2 gap-2 pt-2 text-sm"
                                >
                                    <div
                                        class="flex items-center gap-1 text-muted-foreground"
                                    >
                                        <MapPin class="h-3 w-3" /> Latitude
                                    </div>
                                    <div class="font-mono">
                                        {{ server.latitude || '-' }}
                                    </div>

                                    <div
                                        class="flex items-center gap-1 text-muted-foreground"
                                    >
                                        <MapPin class="h-3 w-3" /> Longitude
                                    </div>
                                    <div class="font-mono">
                                        {{ server.longitude || '-' }}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <!-- Photos Tab -->
                <TabsContent value="photos" class="mt-4 space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <Card
                            v-for="category in [
                                'Room',
                                'Rack',
                                'Installation',
                                'Cabling',
                            ]"
                            :key="category"
                        >
                            <CardHeader class="pb-3">
                                <CardTitle class="text-lg"
                                    >{{ category }} Documentation</CardTitle
                                >
                            </CardHeader>
                            <CardContent>
                                <div
                                    v-if="
                                        getPhotosByCategory(category).length > 0
                                    "
                                    class="grid grid-cols-2 gap-4"
                                >
                                    <div
                                        v-for="photo in getPhotosByCategory(
                                            category,
                                        )"
                                        :key="photo.id"
                                        class="aspect-video overflow-hidden rounded-md border"
                                    >
                                        <a
                                            :href="'/storage/' + photo.path"
                                            target="_blank"
                                        >
                                            <img
                                                :src="'/storage/' + photo.path"
                                                class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                                            />
                                        </a>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="flex h-40 flex-col items-center justify-center rounded-md border border-dashed bg-muted/30"
                                >
                                    <ImageIcon
                                        class="mb-2 h-8 w-8 text-muted-foreground/50"
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        No photos for
                                        {{ category.toLowerCase() }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <!-- Devices Tab (Future) -->
                <TabsContent value="devices" class="mt-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Installed Devices</CardTitle>
                            <CardDescription
                                >Active devices connected or installed at this
                                node.</CardDescription
                            >
                        </CardHeader>
                        <CardContent>
                            <div
                                class="flex h-64 flex-col items-center justify-center text-muted-foreground"
                            >
                                <Cpu class="mb-4 h-12 w-12 opacity-20" />
                                <p>
                                    Devices management will be available in
                                    Phase 3.4
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
