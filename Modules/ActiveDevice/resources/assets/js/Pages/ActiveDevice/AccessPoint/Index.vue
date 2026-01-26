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
                    <Link href="/active-devices/access-point/create">
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
                                        <Button variant="ghost" size="icon" as-child title="View Detail">
                                            <Link :href="`/active-devices/access-point/${item.id}`">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child title="Edit">
                                            <Link :href="`/active-devices/access-point/${item.id}/edit`">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <DeleteAction :href="`/active-devices/access-point/${item.id}`" />
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
