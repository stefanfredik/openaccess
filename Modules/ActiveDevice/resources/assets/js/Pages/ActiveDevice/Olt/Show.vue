```vue
<script setup lang="ts">
  import { Head, Link } from '@inertiajs/vue3'
  import { ArrowLeft, Pencil } from 'lucide-vue-next'
  import { ref } from 'vue'
  import ConnectionManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ConnectionManager.vue'
  import InterfaceManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/InterfaceManager.vue'
  import OltOverview from '@/../../Modules/ActiveDevice/resources/assets/js/Components/OltOverview.vue'
  import ServicePortsManager from '@/../../Modules/ActiveDevice/resources/assets/js/Components/ServicePortsManager.vue'
  import { Badge } from '@/components/ui/badge'
  import { Button } from '@/components/ui/button'
  import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
  import AppLayout from '@/layouts/AppLayout.vue'

  const props = defineProps<{
    olt: any
    availableDevices: Array<any>
  }>()

  const interfaceManagerRef = ref<InstanceType<typeof InterfaceManager> | null>(null)
  const connectionManagerRef = ref<InstanceType<typeof ConnectionManager> | null>(null)
</script>

<template>
  <Head :title="`OLT: ${olt.name}`" />

  <AppLayout
    :breadcrumbs="[
      { title: 'OLT', href: route('active-device.olt.index') },
      { title: olt.name, href: '#' },
    ]">
    <div class="max-w-7xl space-y-6 p-4 md:p-6">
      <!-- Page Header -->
      <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div class="space-y-1">
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight">
              {{ olt.name }}
            </h1>
            <Badge :variant="olt.status === 'Active' ? 'default' : olt.status === 'Planned' ? 'secondary' : 'destructive'" class="capitalize">
              {{ olt.status }}
            </Badge>
          </div>
          <p class="flex items-center gap-2 text-sm text-muted-foreground">
            <span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-xs dark:bg-slate-800">{{ olt.code }}</span>
            <span>&bull;</span>
            <span>{{ olt.brand }} {{ olt.model }}</span>
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm" as-child>
            <Link :href="route('active-device.olt.index')">
              <ArrowLeft class="mr-2 h-4 w-4" />
              Kembali
            </Link>
          </Button>
          <Button size="sm" as-child>
            <Link :href="route('active-device.olt.edit', olt.id)">
              <Pencil class="mr-2 h-4 w-4" />
              Edit OLT
            </Link>
          </Button>
        </div>
      </div>

      <!-- Mobile View: Stacked Layout -->
      <div class="flex flex-col space-y-8 md:hidden">
        <!-- Overview -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold">Overview</h3>
          <OltOverview :olt="olt" />
        </div>

        <!-- Service Ports -->
        <ServicePortsManager :device="olt" device-type="Modules\ActiveDevice\Models\Olt" />

        <!-- Physical Interfaces -->
        <InterfaceManager
          ref="interfaceManagerRef"
          :device-id="olt.id"
          :device-type="'Modules\\ActiveDevice\\Models\\Olt'"
          :interfaces="olt.interfaces || []" />

        <!-- Connectivity -->
        <ConnectionManager
          ref="connectionManagerRef"
          :device="olt"
          device-type="Modules\ActiveDevice\Models\Olt"
          :connections="olt.source_connections"
          :incoming-connections="olt.destination_connections"
          :available-devices="availableDevices" />
      </div>

      <!-- Desktop View: Tabs Layout -->
      <Tabs default-value="overview" class="hidden w-full flex-col gap-8 md:flex md:flex-row">
        <TabsList class="h-auto w-full flex-col justify-start space-y-1 bg-transparent p-0 md:w-64">
          <TabsTrigger
            value="overview"
            class="w-full justify-start px-4 py-2 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary">
            Overview
          </TabsTrigger>
          <TabsTrigger
            value="services"
            class="w-full justify-start px-4 py-2 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary">
            Service Ports
          </TabsTrigger>
          <TabsTrigger
            value="interfaces"
            class="w-full justify-start px-4 py-2 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary">
            Physical Interfaces
          </TabsTrigger>
          <TabsTrigger
            value="connectivity"
            class="w-full justify-start px-4 py-2 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary">
            Connectivity
          </TabsTrigger>
        </TabsList>

        <div class="flex-1">
          <!-- Tab 1: Overview -->
          <TabsContent value="overview" class="mt-0 space-y-6">
            <OltOverview :olt="olt" />
          </TabsContent>

          <!-- Tab 2: Service Ports -->
          <TabsContent value="services" class="mt-0 space-y-6">
            <ServicePortsManager :device="olt" device-type="Modules\ActiveDevice\Models\Olt" />
          </TabsContent>

          <!-- Tab 3: Physical Interfaces -->
          <TabsContent value="interfaces" class="mt-0 space-y-6">
            <InterfaceManager
              ref="interfaceManagerRef"
              :device-id="olt.id"
              :device-type="'Modules\\ActiveDevice\\Models\\Olt'"
              :interfaces="olt.interfaces || []" />
          </TabsContent>

          <!-- Tab 3: Connectivity -->
          <TabsContent value="connectivity" class="mt-0 space-y-6">
            <ConnectionManager
              ref="connectionManagerRef"
              :device="olt"
              device-type="Modules\ActiveDevice\Models\Olt"
              :connections="olt.source_connections"
              :incoming-connections="olt.destination_connections"
              :available-devices="availableDevices" />
          </TabsContent>
        </div>
      </Tabs>
    </div>
  </AppLayout>
</template>
