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
    <Head title="OLT" />

    <AppLayout :breadcrumbs="[{ title: 'OLT', href: '#' }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">OLT</h1>
                    <p class="text-muted-foreground">Daftar OLT.</p>
                </div>
                <Button as-child>
                    <Link :href="oltCreate().url">
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
                                <TableHead>Brand / Model</TableHead>
                                <TableHead>IP Address</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="olt in olts.data" :key="olt.id">
                                <TableCell class="font-medium">{{ olt.code }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span>{{ olt.name }}</span>
                                        <span class="text-xs text-muted-foreground">{{ olt.pon_type || '-' }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>{{ olt.area?.name || '-' }}</TableCell>
                                <TableCell>{{ olt.brand }} {{ olt.model }}</TableCell>
                                <TableCell class="font-mono text-xs">{{ olt.ip_address }}</TableCell>
                                <TableCell>
                                    <Badge :variant="olt.status === 'Active' ? 'default' : (olt.status === 'Planned' ? 'secondary' : 'destructive')">
                                        {{ olt.status }}
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
