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
import { create as ontCreate, show as ontShow, edit as ontEdit, destroy as ontDestroy } from '@/routes/active-device/ont';

defineProps<{
    onts: {
        data: Array<any>;
    };
}>();
</script>

<template>
    <Head title="ONTs" />

    <AppLayout :breadcrumbs="[{ title: 'ONTs', href: '#' }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">ONTs</h1>
                    <p class="text-muted-foreground">Manage Optical Network Terminal devices.</p>
                </div>
                <Button as-child>
                    <Link :href="ontCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add ONT
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>ONTs</CardTitle>
                    <CardDescription>
                        List of all registered ONTs.
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
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="ont in onts.data" :key="ont.id">
                                <TableCell class="font-medium">{{ ont.code }}</TableCell>
                                <TableCell>{{ ont.name }}</TableCell>
                                <TableCell>{{ ont.area?.name || '-' }}</TableCell>
                                <TableCell>{{ ont.onu_type || '-' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="ont.is_active ? 'default' : 'destructive'">
                                        {{ ont.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <ShowAction :href="ontShow({ ont: ont.id }).url" title="View Detail" />
                                        <EditAction :href="ontEdit({ ont: ont.id }).url" title="Edit" />
                                        <DeleteAction :href="ontDestroy({ ont: ont.id }).url" />
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="onts.data.length === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    No ONTs found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
