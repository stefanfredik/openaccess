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
import { Plus, Pencil, Trash2, Eye, Server as ServerIcon } from 'lucide-vue-next';
import { index as serverIndex, create as serverCreate, edit as serverEdit, show as serverShow, destroy as serverDestroy } from '@/routes/server';
import DeleteAction from '@/components/DeleteAction.vue';

defineProps<{
    servers: Array<any>;
}>();

const getUiStatus = (status: string) => {
    switch (status) {
        case 'Active':
            return 'default';
        case 'Inactive':
            return 'destructive';
        case 'Planned':
            return 'secondary';
        default:
            return 'outline';
    }
};

const getFunctionBadge = (func: string) => {
    switch (func) {
        case 'OLT':
            return 'secondary';
        case 'Core Network':
            return 'default';
        case 'NOC':
            return 'destructive';
        default:
            return 'outline';
    }
};
</script>

<template>
    <Head title="Servers" />

    <AppLayout :breadcrumbs="[{ title: 'Servers', href: serverIndex().url }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Servers & Core Infrastructure</h1>
                    <p class="text-muted-foreground">Manage servers, OLTs, and core network nodes.</p>
                </div>
                <Button as-child>
                    <Link :href="serverCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Server
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Servers</CardTitle>
                    <CardDescription>
                        List of all core infrastructure devices.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Function</TableHead>
                                <TableHead>Location</TableHead>
                                <TableHead>Area / POP</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="server in servers" :key="server.id">
                                <TableCell class="font-medium text-xs">{{ server.code }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-2">
                                        <ServerIcon class="h-4 w-4 text-muted-foreground" />
                                        <span>{{ server.name }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getFunctionBadge(server.function)">{{ server.function }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="text-xs">
                                        {{ server.building || '-' }}
                                        <span v-if="server.floor" class="text-muted-foreground ml-1">F{{ server.floor }}</span>
                                        <div v-if="server.area_location" class="text-muted-foreground">{{ server.area_location }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div class="text-xs">
                                        <div class="font-medium">{{ server.area?.name || '-' }}</div>
                                        <div class="text-muted-foreground italic" v-if="server.pop">{{ server.pop?.name }}</div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getUiStatus(server.status)">{{ server.status }}</Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child title="View Details">
                                            <Link :href="serverShow({ server: server.id }).url">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child title="Edit">
                                            <Link :href="serverEdit({ server: server.id }).url">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <DeleteAction :href="serverDestroy({ server: server.id }).url" />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="servers.length === 0">
                                <TableCell colspan="7" class="h-24 text-center">
                                    No servers found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
