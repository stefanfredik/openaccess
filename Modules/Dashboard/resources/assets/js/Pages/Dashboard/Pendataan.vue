<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatsCard from '../../Components/StatsCard.vue';
import { MapPin, Server, Network, HardDrive, Cpu } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineProps<{
    stats: {
        areas: number;
        sites: number;
        pops: number;
        active_devices: number;
        passive_devices: number;
    };
}>();
</script>

<template>
    <Head title="Pendataan Perangkat" />

    <AppLayout :breadcrumbs="[{ title: 'Pendataan', href: '#' }]">
        <div class="flex flex-col gap-8 p-4 md:p-8">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight">Pendataan Infrastruktur</h1>
                <p class="text-muted-foreground">Kelola data area, lokasi POP, dan semua perangkat jaringan Anda.</p>
            </div>
            
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Infrastructure Group -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                        <MapPin class="h-5 w-5 text-primary" />
                        Infrastruktur Dasar
                    </h2>
                    <div class="grid gap-4">
                        <StatsCard 
                            title="Areas" 
                            :value="stats.areas" 
                            :icon="MapPin"
                            description="Manajemen Area & Sub-Area"
                        />
                        <StatsCard 
                            title="POPs" 
                            :value="stats.pops" 
                            :icon="MapPin"
                            description="Point of Presence Locations"
                        />
                    </div>
                </div>

                <!-- Active Device Group -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                        <Cpu class="h-5 w-5 text-primary" />
                        Perangkat Aktif
                    </h2>
                    <div class="grid gap-4">
                        <StatsCard 
                            title="Active Devices" 
                            :value="stats.active_devices" 
                            :icon="Server"
                            description="OLT, ONT, Routers, etc."
                        />
                        <Link href="/active-devices/topology">
                            <Button variant="outline" class="w-full justify-start gap-2">
                                <Network class="h-4 w-4" />
                                Lihat Topologi Jaringan
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Passive Device Group -->
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                        <HardDrive class="h-5 w-5 text-primary" />
                        Perangkat Pasif
                    </h2>
                    <div class="grid gap-4">
                        <StatsCard 
                            title="Passive Devices" 
                            :value="stats.passive_devices" 
                            :icon="HardDrive"
                            description="ODP, ODF, Tiang, Kabel, etc."
                        />
                    </div>
                </div>
            </div>

            <div class="bg-muted/50 rounded-xl p-8 border border-dashed text-center">
                <p class="text-sm text-muted-foreground italic">
                    Gunakan sidebar di sebelah kiri untuk navigasi detail ke setiap modul pendataan.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
