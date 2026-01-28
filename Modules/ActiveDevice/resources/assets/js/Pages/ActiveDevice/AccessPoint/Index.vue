<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import DeleteAction from '@/components/DeleteAction.vue';
import ShowAction from '@/components/ShowAction.vue';
import EditAction from '@/components/EditAction.vue';
import { create as apCreate, show as apShow, edit as apEdit, destroy as apDestroy } from '@/routes/active-device/access-point';

defineProps<{
    accessPoints: {
        data: Array<any>;
    };
}>();
</script>

<template>
    <Head title="Access Points" />

    <AppLayout :breadcrumbs="[{ title: 'Access Points', href: '#' }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Access Points</h1>
                    <p class="text-muted-foreground">Manage Wireless Radios / Access Points.</p>
                </div>
                <Button as-child>
                    <Link :href="apCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add AP
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Access Points</CardTitle>
                    <CardDescription>
                        List of all registered APs.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[120px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Frequency</TableHead>
                                <TableHead>SSIDs</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="item in accessPoints.data" :key="item.id">
                                <TableCell class="font-medium">{{ item.code }}</TableCell>
                                <TableCell>{{ item.name }}</TableCell>
                                <TableCell>{{ item.area?.name || '-' }}</TableCell>
                                <TableCell>{{ item.frequency || '-' }}</TableCell>
                                <TableCell>{{ item.ssid_count }}</TableCell>
                                <TableCell>
                                    <Badge :variant="item.is_active ? 'default' : 'destructive'">
                                        {{ item.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <ShowAction :href="apShow({ access_point: item.id }).url" title="View Detail" />
                                        <EditAction :href="apEdit({ access_point: item.id }).url" title="Edit" />
                                        <DeleteAction :href="apDestroy({ access_point: item.id }).url" />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="accessPoints.data.length === 0">
                                <TableCell colspan="7" class="h-24 text-center">
                                    No Access Points found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
