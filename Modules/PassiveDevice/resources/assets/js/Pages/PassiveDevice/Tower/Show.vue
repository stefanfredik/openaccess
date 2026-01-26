<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil, ArrowLeft } from 'lucide-vue-next';
import { index as towerIndex, edit as towerEdit } from '@/routes/passive-device/tower';

defineProps<{
    tower: any;
}>();
</script>

<template>
    <Head :title="`Tower: ${tower.name}`" />

    <AppLayout :breadcrumbs="[
        { title: 'Towers', href: towerIndex().url },
        { title: tower.name, href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="towerIndex().url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ tower.name }}</h1>
                        <p class="text-muted-foreground">{{ tower.code }}</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="towerEdit(tower.id).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Tower
                    </Link>
                </Button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Area</dt>
                                <dd class="text-sm font-semibold">{{ tower.area?.name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd>
                                    <Badge :variant="tower.is_active ? 'default' : 'destructive'">
                                        {{ tower.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Height</dt>
                                <dd class="text-sm font-semibold">{{ tower.height }} m</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Segments</dt>
                                <dd class="text-sm font-semibold">{{ tower.segment_count || 1 }} Segments</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Brand / Model</dt>
                                <dd class="text-sm font-semibold">{{ tower.brand || '-' }} / {{ tower.model || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Location</dt>
                                <dd class="text-sm font-semibold">
                                    {{ tower.latitude || '-' }}, {{ tower.longitude || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Installed At</dt>
                                <dd class="text-sm font-semibold">{{ tower.installed_at || '-' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground">Description</dt>
                                <dd class="text-sm mt-1">{{ tower.description || 'No description provided.' }}</dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Photo</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="tower.photo" class="aspect-square relative rounded-md overflow-hidden bg-muted">
                                <img :src="tower.photo" :alt="tower.name" class="object-cover w-full h-full" />
                            </div>
                            <div v-else class="aspect-square flex items-center justify-center rounded-md border-2 border-dashed border-muted text-muted-foreground italic text-sm">
                                No photo uploaded
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
