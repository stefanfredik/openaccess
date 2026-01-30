<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link } from '@inertiajs/vue3'
  import { ArrowRight, Building2, Database, LayoutDashboard, Map as MapIcon, MapPin, Plus, Server, Users } from 'lucide-vue-next'
  import StatsCard from '../../Components/StatsCard.vue'

  defineProps<{
    stats: any
    isSuperAdmin: boolean
  }>()
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: route('dashboard') }]">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-8 p-6 md:p-10">
      <!-- Hero Section -->
      <div
        class="relative overflow-hidden rounded-3xl bg-primary px-8 py-12 text-primary-foreground shadow-2xl transition-all dark:border dark:border-white/5 dark:bg-zinc-900">
        <div class="relative z-10 max-w-2xl space-y-4">
          <h1 class="text-4xl font-extrabold tracking-tight md:text-5xl dark:text-white">
            {{ isSuperAdmin ? 'Admin Control Center' : 'Network Intelligence' }}
          </h1>
          <p class="text-lg leading-relaxed font-medium opacity-90 dark:text-zinc-300">
            {{
              isSuperAdmin
                ? 'Manage your global company infrastructure and user permissions from a single unified interface.'
                : 'Monitor your ISP infrastructure, active devices, and passive network components in real-time.'
            }}
          </p>
          <div class="flex flex-wrap gap-3 pt-4">
            <Link :href="isSuperAdmin ? route('companies.index') : route('map.index')">
              <Button variant="secondary" size="lg" class="gap-2 font-bold">
                <component :is="isSuperAdmin ? Building2 : MapIcon" class="h-5 w-5" />
                {{ isSuperAdmin ? 'Kelola Perusahaan' : 'Buka GIS Map' }}
                <ArrowRight class="h-4 w-4" />
              </Button>
            </Link>
            <Link v-if="!isSuperAdmin" :href="route('pendataan')">
              <Button variant="outline" size="lg" class="gap-2 border-white/20 bg-white/10 font-bold text-white hover:bg-white/20 active:bg-white/30">
                <Database class="h-5 w-5" />
                Pendataan Aset
              </Button>
            </Link>
          </div>
        </div>
        <!-- Abstract visual element -->
        <div class="absolute -top-20 -right-20 h-80 w-80 rounded-full bg-white/10 blur-3xl dark:bg-primary/20"></div>
        <div class="absolute -right-10 -bottom-10 rotate-12 transform opacity-20">
          <LayoutDashboard class="h-64 w-64" />
        </div>
      </div>

      <!-- Quick Stats Section -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="flex items-center gap-2 text-xl font-bold tracking-tight text-foreground">
            <LayoutDashboard class="h-5 w-5 text-primary dark:text-foreground" />
            Statistik Utama
          </h2>
        </div>

        <div v-if="isSuperAdmin" class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
          <StatsCard title="Total Companies" :value="stats.companies" :icon="Building2" variant="primary" description="Registered entities" />
          <StatsCard title="System Users" :value="stats.users" :icon="Users" variant="info" description="Total active accounts" />
        </div>

        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
          <StatsCard title="Coverage Areas" :value="stats.areas" :icon="MapPin" variant="primary" description="Operations coverage" />
          <StatsCard title="POPs" :value="stats.pops" :icon="MapPin" variant="info" description="Points of Presence" />
          <StatsCard title="Network Devices" :value="stats.active_devices" :icon="Server" variant="warning" description="Active hardware" />
        </div>
      </div>

      <!-- Dashboard Grid for future widgets -->
      <div class="grid gap-6 md:grid-cols-2">
        <div
          class="flex flex-col items-center justify-center space-y-4 rounded-3xl border border-dashed border-border bg-muted/30 p-8 text-center dark:bg-muted/10">
          <div class="rounded-full bg-muted p-4 dark:bg-muted/20">
            <Plus class="h-8 w-8 text-muted-foreground" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-foreground">Halaman Monitoring</h3>
            <p class="text-sm text-muted-foreground">Status jaringan real-time dan log aktivitas akan muncul di sini.</p>
          </div>
        </div>
        <div
          class="flex flex-col items-center justify-center space-y-4 rounded-3xl border border-dashed border-border bg-muted/30 p-8 text-center dark:bg-muted/10">
          <div class="rounded-full bg-muted p-4 dark:bg-muted/20">
            <MapIcon class="h-8 w-8 text-muted-foreground" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-foreground">Status Geografis</h3>
            <p class="text-sm text-muted-foreground">Widget sebaran perangkat berdasarkan wilayah segera hadir.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
