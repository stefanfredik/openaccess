<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatsCard from '../../Components/StatsCard.vue';
import { Building2, MapPin, Server, Users, ArrowRight, LayoutDashboard, Database, Map as MapIcon, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';

defineProps<{
    stats: any;
    isSuperAdmin: boolean;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '#' }]">
        <div class="flex flex-col gap-8 p-6 md:p-10 max-w-7xl mx-auto w-full">
            
            <!-- Hero Section -->
            <div class="relative overflow-hidden rounded-3xl bg-primary px-8 py-12 text-primary-foreground shadow-2xl transition-all">
                <div class="relative z-10 space-y-4 max-w-2xl">
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">
                        {{ isSuperAdmin ? 'Admin Control Center' : 'Network Intelligence' }}
                    </h1>
                    <p class="text-lg opacity-90 font-medium leading-relaxed">
                        {{ isSuperAdmin 
                            ? 'Manage your global company infrastructure and user permissions from a single unified interface.' 
                            : 'Monitor your ISP infrastructure, active devices, and passive network components in real-time.' 
                        }}
                    </p>
                    <div class="flex flex-wrap gap-3 pt-4">
                        <Link :href="isSuperAdmin ? '/companies' : '/maps'">
                            <Button variant="secondary" size="lg" class="font-bold gap-2">
                                <component :is="isSuperAdmin ? Building2 : MapIcon" class="h-5 w-5" />
                                {{ isSuperAdmin ? 'Kelola Perusahaan' : 'Buka GIS Map' }}
                                <ArrowRight class="h-4 w-4" />
                            </Button>
                        </Link>
                        <Link v-if="!isSuperAdmin" href="/pendataan">
                            <Button variant="outline" size="lg" class="bg-white/10 border-white/20 hover:bg-white/20 text-white font-bold gap-2">
                                <Database class="h-5 w-5" />
                                Pendataan Aset
                            </Button>
                        </Link>
                    </div>
                </div>
                <!-- Abstract visual element -->
                <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-10 -right-10 opacity-20 transform rotate-12">
                   <LayoutDashboard class="h-64 w-64" />
                </div>
            </div>

            <!-- Quick Stats Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold tracking-tight flex items-center gap-2">
                        <LayoutDashboard class="h-5 w-5 text-primary" />
                        Statistik Utama
                    </h2>
                </div>
                
                <div v-if="isSuperAdmin" class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <StatsCard 
                        title="Total Companies" 
                        :value="stats.companies" 
                        :icon="Building2"
                        variant="primary"
                        description="Registered entities"
                    />
                    <StatsCard 
                        title="System Users" 
                        :value="stats.users" 
                        :icon="Users"
                        variant="info"
                        description="Total active accounts"
                    />
                </div>

                <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <StatsCard 
                        title="Coverage Areas" 
                        :value="stats.areas" 
                        :icon="MapPin"
                        variant="primary"
                        description="Operations coverage"
                    />
                    <StatsCard 
                        title="POPs" 
                        :value="stats.pops" 
                        :icon="MapPin"
                        variant="info"
                        description="Points of Presence"
                    />
                    <StatsCard 
                        title="Network Devices" 
                        :value="stats.active_devices" 
                        :icon="Server"
                        variant="warning"
                        description="Active hardware"
                    />
                </div>
            </div>

            <!-- Dashboard Grid for future widgets -->
            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-3xl border border-dashed border-border p-8 flex flex-col items-center justify-center text-center space-y-4 bg-muted/30">
                    <div class="p-4 rounded-full bg-muted">
                        <Plus class="h-8 w-8 text-muted-foreground" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Halaman Monitoring</h3>
                        <p class="text-sm text-muted-foreground">Status jaringan real-time dan log aktivitas akan muncul di sini.</p>
                    </div>
                </div>
                <div class="rounded-3xl border border-dashed border-border p-8 flex flex-col items-center justify-center text-center space-y-4 bg-muted/30">
                    <div class="p-4 rounded-full bg-muted">
                        <MapIcon class="h-8 w-8 text-muted-foreground" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Status Geografis</h3>
                        <p class="text-sm text-muted-foreground">Widget sebaran perangkat berdasarkan wilayah segera hadir.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
