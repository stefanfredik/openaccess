<script setup lang="ts">
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
import {
    create as userCreate,
    destroy as userDestroy,
    edit as userEdit,
    index as userIndex,
} from '@/routes/user';
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    users: Array<any>;
}>();

const page = usePage();
const isSuperAdmin = computed(
    () => (page.props.auth as any)?.roles?.includes('superadmin') || false,
);
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="[{ title: 'Users', href: userIndex().url }]">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Users</h1>
                    <p class="text-muted-foreground">
                        Manage system users and roles.
                    </p>
                </div>
                <Button v-if="isSuperAdmin" as-child>
                    <Link :href="userCreate().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add User
                    </Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Users</CardTitle>
                    <CardDescription>
                        List of all registered users.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Company</TableHead>
                                <TableHead>Joined</TableHead>
                                <TableHead
                                    v-if="isSuperAdmin"
                                    class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users" :key="user.id">
                                <TableCell class="font-medium">
                                    <div class="flex flex-col">
                                        <span>{{ user.name }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="role in user.roles"
                                            :key="role"
                                            variant="secondary"
                                        >
                                            {{ role }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell>{{
                                    user.company?.name || '-'
                                }}</TableCell>
                                <TableCell
                                    class="text-sm text-muted-foreground"
                                    >{{ user.created_at }}</TableCell
                                >
                                <TableCell
                                    v-if="isSuperAdmin"
                                    class="text-right"
                                >
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                        >
                                            <Link
                                                :href="
                                                    userEdit({ user: user.id })
                                                        .url
                                                "
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Link
                                            :href="
                                                userDestroy({ user: user.id })
                                                    .url
                                            "
                                            method="delete"
                                            as="button"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-md text-sm font-medium whitespace-nowrap text-destructive ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground hover:text-destructive focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="users.length === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    No users found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
