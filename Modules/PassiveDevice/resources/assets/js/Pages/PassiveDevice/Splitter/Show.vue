<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil, ArrowLeft } from 'lucide-vue-next';
import { index as splitterIndex, edit as splitterEdit } from '@/routes/passive-device/splitter';

defineProps<{
    splitter: any;
}>();
</script>

<template>
    <Head :title="`Splitter: ${splitter.name}`" />

    <AppLayout :breadcrumbs="[
        { title: 'Splitters', href: splitterIndex().url },
        { title: splitter.name, href: '#' }
    ]">
        <div class="flex flex-col gap-6 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="splitterIndex().url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ splitter.name }}</h1>
                        <p class="text-muted-foreground">{{ splitter.code }}</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="splitterEdit(splitter.id).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Splitter
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
                                <dd class="text-sm font-semibold">{{ splitter.area?.name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd>
                                    <Badge :variant="splitter.is_active ? 'default' : 'destructive'">
                                        {{ splitter.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Ratio</dt>
                                <dd class="text-sm font-semibold">{{ splitter.ratio || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Insertion Loss</dt>
                                <dd class="text-sm font-semibold">{{ splitter.loss_db || '-' }} dB</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Brand / Model</dt>
                                <dd class="text-sm font-semibold">{{ splitter.brand || '-' }} / {{ splitter.model || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Location</dt>
                                <dd class="text-sm font-semibold">
                                    {{ splitter.latitude || '-' }}, {{ splitter.longitude || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Installed At</dt>
                                <dd class="text-sm font-semibold">{{ splitter.installed_at || '-' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground">Description</dt>
                                <dd class="text-sm mt-1">{{ splitter.description || 'No description provided.' }}</dd>
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
                            <div v-if="splitter.photo" class="aspect-square relative rounded-md overflow-hidden bg-muted">
                                <img :src="splitter.photo" :alt="splitter.name" class="object-cover w-full h-full" />
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
