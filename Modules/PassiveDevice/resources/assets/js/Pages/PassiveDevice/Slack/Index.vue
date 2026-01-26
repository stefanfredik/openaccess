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
import { index as slackIndex, create as slackCreate, edit as slackEdit, show as slackShow, destroy as slackDestroy } from '@/routes/passive-device/slack';

defineProps<{
    slacks: {
        data: Array<any>;
    };
}>();
</script>

<template>
    <Head title="Slacks" />

    <AppLayout :breadcrumbs="[{ title: 'Slacks', href: slackIndex().url }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Slacks</h1>
                    <p class="text-muted-foreground">Manage fiber optic Slack (storage coil) assets.</p>
                </div>
                <Button as-child>
                    <Link :href="slackCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Slack
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Slacks</CardTitle>
                    <CardDescription>
                        List of all Slack storage coils.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[100px]">Code</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Area</TableHead>
                                <TableHead>Length</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="slack in slacks.data" :key="slack.id">
                                <TableCell class="font-medium">{{ slack.code || '-' }}</TableCell>
                                <TableCell>{{ slack.name }}</TableCell>
                                <TableCell>{{ slack.area?.name || '-' }}</TableCell>
                                <TableCell>{{ slack.length }} m</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child title="View Detail">
                                            <Link :href="slackShow(slack.id).url">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child title="Edit">
                                            <Link :href="slackEdit(slack.id).url">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Link
                                            :href="slackDestroy(slack.id).url"
                                            method="delete"
                                            as="button"
                                            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-accent hover:text-accent-foreground h-10 w-10 text-destructive hover:text-destructive"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="slacks.data.length === 0">
                                <TableCell colspan="5" class="h-24 text-center">
                                    No Slacks found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
