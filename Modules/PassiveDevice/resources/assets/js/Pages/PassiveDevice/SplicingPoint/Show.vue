<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';

defineProps<{
    splicingPoint: any;
}>();
</script>

<template>
    <Head :title="`Splicing Point: ${splicingPoint.name}`" />

    <AppLayout
        :breadcrumbs="[
            {
                title: 'Splicing Points',
                href: route('passive-device.splicing-point.index'),
            },
            { title: splicingPoint.name, href: '#' },
        ]"
    >
        <div class="mx-auto flex max-w-4xl flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link
                            :href="route('passive-device.splicing-point.index')"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ splicingPoint.name }}
                        </h1>
                        <p class="text-muted-foreground">
                            {{ splicingPoint.code }}
                        </p>
                    </div>
                </div>
                <Button as-child>
                    <Link
                        :href="
                            route(
                                'passive-device.splicing-point.edit',
                                splicingPoint.id,
                            )
                        "
                    >
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Splicing Point
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
                                    {{ splicingPoint.area?.name || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Joint Box
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splicingPoint.joint_box?.name || '-' }}
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
                                            splicingPoint.status === 'Active'
                                                ? 'default'
                                                : splicingPoint.status ===
                                                    'Damaged'
                                                  ? 'destructive'
                                                  : 'secondary'
                                        "
                                    >
                                        {{ splicingPoint.status }}
                                    </Badge>
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Tray Number
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splicingPoint.tray_number || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Splicing Type
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splicingPoint.splicing_type || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Loss
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splicingPoint.loss ?? 0 }} dB
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Spliced At
                                </dt>
                                <dd class="text-sm font-semibold">
                                    {{ splicingPoint.spliced_at || '-' }}
                                </dd>
                            </div>
                            <div>
                                <dt
                                    class="text-sm font-medium text-muted-foreground"
                                >
                                    Active Status
                                </dt>
                                <dd>
                                    <Badge
                                        :variant="
                                            splicingPoint.is_active
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{
                                            splicingPoint.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
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
                                        splicingPoint.description ||
                                        'No description provided.'
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <div class="space-y-6"></div>
            </div>
        </div>
    </AppLayout>
</template>
