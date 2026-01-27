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
import { Plus } from 'lucide-vue-next';
import DeleteAction from '@/components/DeleteAction.vue';
import ShowAction from '@/components/ShowAction.vue';
import EditAction from '@/components/EditAction.vue';
import { index as oltIndex, create as oltCreate, show as oltShow, edit as oltEdit, destroy as oltDestroy } from '@/routes/active-device/olt';

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
                    <Link :href="oltCreate().url">
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
                                        <ShowAction :href="oltShow({ olt: olt.id }).url" title="View Detail" />
                                        <EditAction :href="oltEdit({ olt: olt.id }).url" title="Edit" />
                                        <DeleteAction :href="oltDestroy({ olt: olt.id }).url" />
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
