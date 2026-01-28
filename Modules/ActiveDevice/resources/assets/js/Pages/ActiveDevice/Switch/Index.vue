<script setup lang="ts">
import DeleteAction from '@/components/DeleteAction.vue';
import EditAction from '@/components/EditAction.vue';
import ShowAction from '@/components/ShowAction.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
    switches: {
        data: Array<any>;
    };
}>();
</script>

<template>
    <Head title="Switches" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Switches', href: route('active-device.switch.index') },
        ]"
    >
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Switches</h1>
                    <p class="text-muted-foreground">
                        Manage Network Switches (L2/L3).
                    </p>
                </div>
                <Button as-child>
                    <Link :href="route('active-device.switch.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Switch
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Switches</CardTitle>
                    <CardDescription>
                        List of all registered Switches.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[120px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Ports</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="item in switches.data"
                                :key="item.id"
                            >
                                <TableCell class="font-medium">{{
                                    item.code
                                }}</TableCell>
                                <TableCell>{{ item.name }}</TableCell>
                                <TableCell>{{
                                    item.area?.name || '-'
                                }}</TableCell>
                                <TableCell>{{
                                    item.switch_type || '-'
                                }}</TableCell>
                                <TableCell>{{ item.port_count }}</TableCell>
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
                                                    'active-device.switch.show',
                                                    item.id,
                                                )
                                            "
                                            title="View Detail"
                                        />
                                        <EditAction
                                            :href="
                                                route(
                                                    'active-device.switch.edit',
                                                    item.id,
                                                )
                                            "
                                            title="Edit"
                                        />
                                        <DeleteAction
                                            :href="
                                                route(
                                                    'active-device.switch.destroy',
                                                    item.id,
                                                )
                                            "
                                        />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="switches.data.length === 0">
                                <TableCell colspan="7" class="h-24 text-center">
                                    No Switches found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
