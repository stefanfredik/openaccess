<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil, ArrowLeft } from 'lucide-vue-next';
import ConnectionManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ConnectionManager.vue';

const props = defineProps<{
    router: any;
    availableDevices: Array<any>;
}>();
</script>

<template>
    <Head :title="`Router: ${router.name}`" />

    <AppLayout :breadcrumbs="[
        { title: 'Routers', href: '/active-devices/router' },
        { title: router.name, href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 w-full">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/active-devices/router">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ router.name }}</h1>
                        <p class="text-muted-foreground">{{ router.code }}</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/active-devices/router/${router.id}/edit`">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Router
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-6">
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Area</dt>
                                <dd class="text-sm font-semibold">{{ router.area?.name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">POP / Site</dt>
                                <dd class="text-sm font-semibold">{{ router.pop?.name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd>
                                    <Badge :variant="router.is_active ? 'default' : 'destructive'">
                                        {{ router.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Ports</dt>
                                <dd class="text-sm font-semibold">{{ router.port_count }} Ports</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Brand / Model</dt>
                                <dd class="text-sm font-semibold">{{ router.brand || '-' }} / {{ router.model || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">IP Address</dt>
                                <dd class="text-sm font-semibold font-mono">{{ router.ip_address || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">MAC Address</dt>
                                <dd class="text-sm font-semibold font-mono">{{ router.mac_address || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Serial Number</dt>
                                <dd class="text-sm font-semibold font-mono">{{ router.serial_number || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Installed At</dt>
                                <dd class="text-sm font-semibold">{{ router.installed_at || '-' }}</dd>
                            </div>
                            <div class="sm:col-span-2 md:col-span-3">
                                <dt class="text-sm font-medium text-muted-foreground">Description</dt>
                                <dd class="text-sm mt-1">{{ router.description || 'No description provided.' }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <div class="mt-4">
                    <ConnectionManager 
                        :device="router" 
                        device-type="Modules\ActiveDevice\Models\Router"
                        :connections="router.source_connections"
                        :incoming-connections="router.destination_connections"
                        :available-devices="availableDevices"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
