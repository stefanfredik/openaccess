<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ChevronLeft, Pencil, Building2, Globe, Phone, Mail, MapPin } from 'lucide-vue-next';
import { index as companiesIndex, edit as companiesEdit } from '@/routes/companies';

const props = defineProps<{
    company: {
        id: number;
        code: string;
        name: string;
        address: string;
        phone: string;
        email: string;
        website: string | null;
        logo: string | null;
        is_active: boolean;
        created_at: string;
        updated_at: string;
    };
}>();
</script>

<template>
    <Head :title="company.name" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Companies', href: companiesIndex().url },
            { title: company.name, href: '' },
        ]"
    >
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="companiesIndex().url">
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold tracking-tight">{{ company.name }}</h1>
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <span class="font-mono text-sm bg-muted px-2 py-0.5 rounded">{{ company.code }}</span>
                        <Badge :variant="company.is_active ? 'default' : 'secondary'">
                            {{ company.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="companiesEdit({ company: company.id }).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit Company
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Main Info Card -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Company Information</CardTitle>
                        <CardDescription>Basic details about the company.</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-6">
                        <div class="flex items-start gap-4">
                            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg border bg-muted flex items-center justify-center">
                                <img
                                    v-if="company.logo"
                                    :src="`/storage/${company.logo}`"
                                    :alt="company.name"
                                    class="h-full w-full object-cover"
                                />
                                <Building2 v-else class="h-10 w-10 text-muted-foreground" />
                            </div>
                            <div class="space-y-4 flex-1">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-sm font-medium text-muted-foreground mb-1">Company Name</div>
                                        <div class="font-medium">{{ company.name }}</div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-muted-foreground mb-1">Company Code</div>
                                        <div class="font-medium">{{ company.code }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t">
                             <div class="space-y-3">
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <Mail class="h-4 w-4" />
                                    <span class="text-sm font-medium">Contact Email</span>
                                </div>
                                <div class="pl-6">
                                    <a :href="`mailto:${company.email}`" class="hover:underline text-primary">
                                        {{ company.email }}
                                    </a>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <Phone class="h-4 w-4" />
                                    <span class="text-sm font-medium">Phone Number</span>
                                </div>
                                <div class="pl-6">
                                    <a :href="`tel:${company.phone}`" class="hover:underline text-primary">
                                        {{ company.phone }}
                                    </a>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <Globe class="h-4 w-4" />
                                    <span class="text-sm font-medium">Website</span>
                                </div>
                                <div class="pl-6">
                                    <a 
                                        v-if="company.website" 
                                        :href="company.website" 
                                        target="_blank" 
                                        rel="noopener noreferrer" 
                                        class="hover:underline text-primary"
                                    >
                                        {{ company.website }}
                                    </a>
                                    <span v-else class="text-muted-foreground text-sm italic">
                                        Not provided
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center gap-2 text-muted-foreground">
                                    <MapPin class="h-4 w-4" />
                                    <span class="text-sm font-medium">Address</span>
                                </div>
                                <div class="pl-6 text-sm leading-relaxed whitespace-pre-wrap">
                                    {{ company.address }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Sidebar Metadata Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>System Metadata</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                        <div class="space-y-1">
                            <div class="text-sm text-muted-foreground">Created At</div>
                            <div class="font-medium text-sm">
                                {{ new Date(company.created_at).toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) }}
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="text-sm text-muted-foreground">Last Updated</div>
                            <div class="font-medium text-sm">
                                {{ new Date(company.updated_at).toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) }}
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
