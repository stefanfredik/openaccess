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
import { route } from '@/routes/active-device/olt'; // Assuming wayfinder routes

defineProps<{
    olts: {
        data: Array<any>;
    };
}>();
</script>

<template>
    <Head title="OLTs" />

    <AppLayout :breadcrumbs="[{ title: 'OLTs', href: '#' }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">OLTs</h1>
                    <p class="text-muted-foreground">Manage Optical Line Terminal devices.</p>
                </div>
                <Button as-child>
                    <Link href="/active-devices/olt/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add OLT
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>OLTs</CardTitle>
                    <CardDescription>
                        List of all registered OLTs.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[120px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Ports (PON/Up)</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="olt in olts.data" :key="olt.id">
                                <TableCell class="font-medium">{{ olt.code }}</TableCell>
                                <TableCell>{{ olt.name }}</TableCell>
                                <TableCell>{{ olt.area?.name || '-' }}</TableCell>
                                <TableCell>{{ olt.pon_port_count }} / {{ olt.uplink_port_count }}</TableCell>
                                <TableCell>
                                    <Badge :variant="olt.is_active ? 'default' : 'destructive'">
                                        {{ olt.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child title="View Detail">
                                            <Link :href="`/active-devices/olt/${olt.id}`">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child title="Edit">
                                            <Link :href="`/active-devices/olt/${olt.id}/edit`">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <DeleteAction :href="`/active-devices/olt/${olt.id}`" />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="olts.data.length === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    No OLTs found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
