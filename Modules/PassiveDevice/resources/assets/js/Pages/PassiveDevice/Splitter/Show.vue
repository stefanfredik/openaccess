<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
// import { index as splitterIndex, edit as splitterEdit } from '@/routes/passive-device/splitter';

defineProps<{
    splitter: any;
}>();
</script>

<template>
    <Head :title="`Splitter: ${splitter.name}`" />

    <AppLayout
        :breadcrumbs="[
            {
                title: 'Splitters',
                href: route('passive-device.splitter.index'),
            },
            { title: splitter.name, href: '#' },
        ]"
    >
        <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="route('passive-device.splitter.index')">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ splitter.name }}
                        </h1>
                        <p class="text-muted-foreground">{{ splitter.code }}</p>
                    </div>
                </div>
                <Button as-child>
                    <Link
                        :href="
                            route('passive-device.splitter.edit', splitter.id)
                        "
                    >
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Splitter
                    </Link>
                </Button>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl
                            class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2"
                        >
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Area
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splitter.area?.name || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Status
                                </dt>
                                <dd>
                                    <Badge
                                        :variant="
                                            splitter.is_active
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{
                                            splitter.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Ratio
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splitter.ratio || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Insertion Loss
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splitter.loss_db || '-' }} dB
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Brand / Model
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splitter.brand || '-' }} /
                                    {{ splitter.model || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Location
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splitter.latitude || '-' }},
                                    {{ splitter.longitude || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Installed At
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splitter.installed_at || '-' }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Description
                                </dt>
                                <dd class="mt-1 text-sm">
                                    {{
                                        splitter.description ||
                                        'No description provided.'
                                    }}
                                </dd>
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
                            <div
                                v-if="splitter.photo"
                                class="relative aspect-square overflow-hidden rounded-md bg-muted"
                            >
                                <img
                                    :src="splitter.photo"
                                    :alt="splitter.name"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <div
                                v-else
                                class="flex aspect-square items-center justify-center rounded-md border-2 border-dashed border-muted text-sm text-muted-foreground italic"
                            >
                                No photo uploaded
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
