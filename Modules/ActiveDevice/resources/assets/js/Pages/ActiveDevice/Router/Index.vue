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
    routers: {
        data: Array<any>;
    };
}>();
</script>

<template>
    <Head title="Routers" />

    <AppLayout :breadcrumbs="[{ title: 'Routers', href: '#' }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Routers</h1>
                    <p class="text-muted-foreground">Manage IP Routers.</p>
                </div>
                <Button as-child>
                    <Link href="/pendataan/active-devices/router/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Router
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Routers</CardTitle>
                    <CardDescription>
                        List of all registered Routers.
                    </CardDescription>
                </CardHeader>
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
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="item in routers.data" :key="item.id">
                                <TableCell class="font-medium">{{ item.code }}</TableCell>
                                <TableCell>{{ item.name }}</TableCell>
                                <TableCell>{{ item.area?.name || '-' }}</TableCell>
                                <TableCell>{{ item.port_count }}</TableCell>
                                <TableCell class="font-mono">{{ item.ip_address || '-' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="item.is_active ? 'default' : 'destructive'">
                                        {{ item.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child title="View Detail">
                                            <Link :href="`/pendataan/active-devices/router/${item.id}`">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child title="Edit">
                                            <Link :href="`/pendataan/active-devices/router/${item.id}/edit`">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <DeleteAction :href="`/pendataan/active-devices/router/${item.id}`" />
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
