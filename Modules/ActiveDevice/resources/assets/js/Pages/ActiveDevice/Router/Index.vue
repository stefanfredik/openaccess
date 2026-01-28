<script setup lang="ts">
import DeleteAction from '@/components/DeleteAction.vue';
import EditAction from '@/components/EditAction.vue';
import ShowAction from '@/components/ShowAction.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

defineProps<{
    routers: {
        data: Array<any>;
    };
}>();
</script>

<template>
    <Head title="Routers" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Routers', href: route('active-device.router.index') },
        ]"
    >
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Routers</h1>
                    <p class="text-muted-foreground">Daftar Routers.</p>
                </div>
                <Button as-child>
                    <Link :href="route('active-device.router.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Tambah
                    </Link>
                </Button>
            </div>

            <Card>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[120px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Ports</TableHead>
                                <TableHead>IP Address</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="item in routers.data"
                                :key="item.id"
                            >
                                <TableCell class="font-medium">{{
                                    item.code
                                }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span>{{ item.name }}</span>
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >{{ item.brand }}
                                            {{ item.model }}</span
                                        >
                                    </div>
                                </TableCell>
                                <TableCell>{{
                                    item.area?.name || '-'
                                }}</TableCell>
                                <TableCell>{{ item.port_count }}</TableCell>
                                <TableCell class="font-mono text-xs">{{
                                    item.ip_address || '-'
                                }}</TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            item.is_active
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{
                                            item.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <ShowAction
                                            :href="
                                                route(
                                                    'active-device.router.show',
                                                    item.id,
                                                )
                                            "
                                            title="View Detail"
                                        />
                                        <EditAction
                                            :href="
                                                route(
                                                    'active-device.router.edit',
                                                    item.id,
                                                )
                                            "
                                            title="Edit"
                                        />
                                        <DeleteAction
                                            :href="
                                                route(
                                                    'active-device.router.destroy',
                                                    item.id,
                                                )
                                            "
                                        />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="routers.data.length === 0">
                                <TableCell colspan="7" class="h-24 text-center">
                                    No Routers found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
