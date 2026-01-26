<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil, ArrowLeft } from 'lucide-vue-next';

import ConnectionManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ConnectionManager.vue';

const props = defineProps<{
    ont: any;
    availableDevices: Array<any>;
}>();
</script>

<template>
    <Head :title="`ONT: ${ont.name}`" />

    <AppLayout :breadcrumbs="[
        { title: 'ONTs', href: '/active-devices/ont' },
        { title: ont.name, href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 w-full">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/active-devices/ont">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ ont.name }}</h1>
                        <p class="text-muted-foreground">{{ ont.code }}</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/active-devices/ont/${ont.id}/edit`">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit ONT
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
                                <dd class="text-sm font-semibold">{{ ont.area?.name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">POP / Site</dt>
                                <dd class="text-sm font-semibold">{{ ont.pop?.name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd>
                                    <Badge :variant="ont.is_active ? 'default' : 'destructive'">
                                        {{ ont.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Type</dt>
                                <dd class="text-sm font-semibold">{{ ont.onu_type || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Brand / Model</dt>
                                <dd class="text-sm font-semibold">{{ ont.brand || '-' }} / {{ ont.model || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">IP Address</dt>
                                <dd class="text-sm font-semibold font-mono">{{ ont.ip_address || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">MAC Address</dt>
                                <dd class="text-sm font-semibold font-mono">{{ ont.mac_address || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Serial Number</dt>
                                <dd class="text-sm font-semibold font-mono">{{ ont.serial_number || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Installed At</dt>
                                <dd class="text-sm font-semibold">{{ ont.installed_at || '-' }}</dd>
                            </div>
                            <div class="sm:col-span-2 md:col-span-3">
                                <dt class="text-sm font-medium text-muted-foreground">Description</dt>
                                <dd class="text-sm mt-1">{{ ont.description || 'No description provided.' }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <div class="mt-4">
                    <ConnectionManager 
                        :device="ont" 
                        device-type="Modules\ActiveDevice\Models\Ont"
                        :connections="ont.source_connections"
                        :incoming-connections="ont.destination_connections"
                        :available-devices="availableDevices"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
