<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
// import { index as poleIndex, edit as poleEdit } from '@/routes/passive-device/pole';

defineProps<{
    pole: any;
}>();
</script>

<template>
    <Head :title="`Pole: ${pole.name}`" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Poles', href: route('passive-device.pole.index') },
            { title: pole.name, href: '#' },
        ]"
    >
        <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="route('passive-device.pole.index')">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ pole.name }}
                        </h1>
                        <p class="text-muted-foreground">{{ pole.code }}</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="route('passive-device.pole.edit', pole.id)">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Pole
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
                                    {{ pole.area?.name || '-' }}
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
                                            pole.is_active
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{
                                            pole.is_active
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
                                    Height
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ pole.height }} m
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Material
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ pole.material }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Ownership
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ pole.ownership }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Pole Number
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ pole.pole_number || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Location
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ pole.latitude || '-' }},
                                    {{ pole.longitude || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Installed At
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ pole.installed_at || '-' }}
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
                                        pole.description ||
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
                                v-if="pole.photo"
                                class="relative aspect-square overflow-hidden rounded-md bg-muted"
                            >
                                <img
                                    :src="pole.photo"
                                    :alt="pole.name"
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
