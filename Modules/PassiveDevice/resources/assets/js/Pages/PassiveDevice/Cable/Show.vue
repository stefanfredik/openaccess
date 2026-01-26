<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil, ArrowLeft } from 'lucide-vue-next';
import { index as cableIndex, edit as cableEdit } from '@/routes/passive-device/cable';

defineProps<{
    cable: any;
}>();
</script>

<template>
    <Head :title="`Cable: ${cable.name}`" />

    <AppLayout :breadcrumbs="[
        { title: 'Cables', href: cableIndex().url },
        { title: cable.name, href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="cableIndex().url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ cable.name }}</h1>
                        <p class="text-muted-foreground">{{ cable.code }}</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="cableEdit(cable.id).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Cable
                    </Link>
                </Button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Area</dt>
                                <dd class="text-sm font-semibold">{{ cable.area?.name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd>
                                    <Badge :variant="cable.is_active ? 'default' : 'destructive'">
                                        {{ cable.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Cable Type</dt>
                                <dd class="text-sm font-semibold">{{ cable.type || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Length</dt>
                                <dd class="text-sm font-semibold">{{ cable.length }} m</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Core Count</dt>
                                <dd class="text-sm font-semibold">{{ cable.core_count }} Cores</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Brand / Model</dt>
                                <dd class="text-sm font-semibold">{{ cable.brand || '-' }} / {{ cable.model || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Installed At</dt>
                                <dd class="text-sm font-semibold">{{ cable.installed_at || '-' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground">Description</dt>
                                <dd class="text-sm mt-1">{{ cable.description || 'No description provided.' }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
