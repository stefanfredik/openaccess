<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil, ArrowLeft } from 'lucide-vue-next';
import { index as poleIndex, edit as poleEdit } from '@/routes/passive-device/pole';

defineProps<{
    pole: any;
}>();
</script>

<template>
    <Head :title="`Pole: ${pole.name}`" />

    <AppLayout :breadcrumbs="[
        { title: 'Poles', href: poleIndex().url },
        { title: pole.name, href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="poleIndex().url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ pole.name }}</h1>
                        <p class="text-muted-foreground">{{ pole.code }}</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="poleEdit(pole.id).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Pole
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
                                <dd class="text-sm font-semibold">{{ pole.area?.name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd>
                                    <Badge :variant="pole.is_active ? 'default' : 'destructive'">
                                        {{ pole.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Height</dt>
                                <dd class="text-sm font-semibold">{{ pole.height }} m</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Material</dt>
                                <dd class="text-sm font-semibold">{{ pole.material }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Ownership</dt>
                                <dd class="text-sm font-semibold">{{ pole.ownership }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Pole Number</dt>
                                <dd class="text-sm font-semibold">{{ pole.pole_number || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Location</dt>
                                <dd class="text-sm font-semibold">
                                    {{ pole.latitude || '-' }}, {{ pole.longitude || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Installed At</dt>
                                <dd class="text-sm font-semibold">{{ pole.installed_at || '-' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground">Description</dt>
                                <dd class="text-sm mt-1">{{ pole.description || 'No description provided.' }}</dd>
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
                            <div v-if="pole.photo" class="aspect-square relative rounded-md overflow-hidden bg-muted">
                                <img :src="pole.photo" :alt="pole.name" class="object-cover w-full h-full" />
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
