<script setup lang="ts">
  import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue'
  import DeleteAction from '@/components/DeleteAction.vue'
  import LocationPicker from '@/components/LocationPicker.vue'
  import { Badge } from '@/components/ui/badge'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
  import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
  import AppLayout from '@/layouts/AppLayout.vue'
  import { Head, Link, useForm, router } from '@inertiajs/vue3'
  import { Cpu, Image as ImageIcon, Info, Layers, MapPin, Pencil, Plus, Server as ServerIcon, Trash2, Settings2, Loader2 } from 'lucide-vue-next'
  import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Textarea } from '@/components/ui/textarea'
  import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
  import { Switch } from '@/components/ui/switch'
  import axios from 'axios'

  const props = defineProps<{
    server: any
  }>()

  const selectedRackId = ref(props.server.racks?.[0]?.id || null)
  const selectedRack = computed(() => props.server.racks?.find((r: any) => r.id === selectedRackId.value))

  const isCreateRackOpen = ref(false)
  const rackForm = useForm({
    name: '',
    code: '',
    u_capacity: 42,
    width_mm: 600,
    depth_mm: 1000,
    brand: '',
    description: '',
    status: 'Active',
  })

  const submitRack = () => {
    rackForm.post(route('server.racks.store', props.server.id), {
      onSuccess: () => {
        isCreateRackOpen.value = false
        rackForm.reset()
        if (!selectedRackId.value && props.server.racks.length > 0) {
            selectedRackId.value = props.server.racks[props.server.racks.length - 1].id
        }
      },
    })
  }

  const isMountDeviceOpen = ref(false)
  const availableDevices = ref<any[]>([])
  const isLoadingDevices = ref(false)
  
  const mountForm = useForm({
    device_id: '',
    device_type: '',
    unit_start: 1,
    unit_size: 1,
    color: 'bg-blue-500',
  })

  const fetchAvailableDevices = async () => {
    isLoadingDevices.value = true
    try {
      const response = await axios.get(route('server.available-devices', props.server.id))
      availableDevices.value = response.data
    } catch (error) {
      console.error('Failed to fetch devices', error)
    } finally {
      isLoadingDevices.value = false
    }
  }

  const isOverlapDetected = computed(() => {
    if (!selectedRack.value) return false
    const start = mountForm.unit_start
    const end = start + mountForm.unit_size - 1
    
    if (end > selectedRack.value.u_capacity) return true
    
    // Check if any part of the new device range overlaps with existing devices
    return selectedRack.value.contents?.some((c: any) => {
        const cStart = c.unit_start
        const cEnd = cStart + c.unit_size - 1
        return (start <= cEnd && end >= cStart)
    })
  })

  const openMountModal = (u?: number) => {
    if (u) {
        mountForm.unit_start = u
    }
    fetchAvailableDevices()
    isMountDeviceOpen.value = true
  }

  const submitMount = () => {
    if (isOverlapDetected.value) return
    
    mountForm.post(route('racks.mount', selectedRackId.value), {
      onSuccess: () => {
        isMountDeviceOpen.value = false
        mountForm.reset()
      },
    })
  }


  const getDeviceAtU = (rack: any, u: number) => {
    return rack?.contents?.find((c: any) => c.unit_start === u)
  }

  const isUSlotOccupied = (rack: any, u: number) => {
    return rack?.contents?.some((c: any) => u >= c.unit_start && u < c.unit_start + c.unit_size)
  }

  const getUiStatus = (status: string) => {
    switch (status) {
      case 'Active': return 'default'
      case 'Inactive': return 'destructive'
      case 'Planned': return 'secondary'
      case 'Maintenance': return 'outline'
      default: return 'outline'
    }
  }

  const getPhotosByCategory = (category: string) => {
    return props.server.photos?.filter((p: any) => p.category === category) || []
  }

  // --- Connection Visualization Logic ---
  const showConnections = ref(false)
  const rackContainerRef = ref<HTMLElement | null>(null)
  const portRefs = ref<Map<string, HTMLElement>>(new Map())
  const deviceRefs = ref<Map<string, HTMLElement>>(new Map())

  const deviceColors = [
    { name: 'Blue', value: 'bg-blue-500' },
    { name: 'Emerald', value: 'bg-emerald-500' },
    { name: 'Orange', value: 'bg-orange-500' },
    { name: 'Red', value: 'bg-red-500' },
    { name: 'Indigo', value: 'bg-indigo-500' },
    { name: 'Slate', value: 'bg-slate-500' },
  ]

  // Get all connections from all devices in the current rack
  const allConnections = computed(() => {
    if (!selectedRack.value?.contents) return []
    
    const connections: any[] = []
    const seenIds = new Set<number>()
    
    selectedRack.value.contents.forEach((content: any) => {
        const device = content.device
        if (!device) return
        
        // Add source connections
        ;(device.source_connections || []).forEach((conn: any) => {
            if (!seenIds.has(conn.id)) {
                seenIds.add(conn.id)
                connections.push(conn)
            }
        })
        
        // Add destination connections
        ;(device.destination_connections || []).forEach((conn: any) => {
            if (!seenIds.has(conn.id)) {
                seenIds.add(conn.id)
                connections.push(conn)
            }
        })
    })
    
    return connections
  })

  const connectionLines = ref<any[]>([])

  const updateConnectionLines = async () => {
    await nextTick()
    if (!showConnections.value || !rackContainerRef.value) {
        connectionLines.value = []
        return
    }

    const rackRect = rackContainerRef.value.getBoundingClientRect()
    const lines: any[] = []

    // Get all devices in current rack for lookup
    const devicesInRack = new Map<string, any>()
    selectedRack.value?.contents?.forEach((c: any) => {
        devicesInRack.set(`${c.device_type}:${c.device_id}`, c)
    })

    allConnections.value.forEach((conn: any) => {
        // Use interface IDs as keys - these match what we store in port refs
        const sourceInterfaceId = conn.source_port
        const destInterfaceId = conn.destination_port

        // Look up elements using interface ID keys
        let sourceEl = portRefs.value.get(`interface:${sourceInterfaceId}`)
        let destEl = portRefs.value.get(`interface:${destInterfaceId}`)

        // Fallback to device element if interface not found
        if (!sourceEl) {
            sourceEl = deviceRefs.value.get(`${conn.source_type}:${conn.source_id}`)
        }
        if (!destEl) {
            destEl = deviceRefs.value.get(`${conn.destination_type}:${conn.destination_id}`)
        }

        // Only draw line if both endpoints are found and both devices are in the current rack
        const sourceInRack = devicesInRack.has(`${conn.source_type}:${conn.source_id}`)
        const destInRack = devicesInRack.has(`${conn.destination_type}:${conn.destination_id}`)

        if (sourceEl && destEl && sourceInRack && destInRack) {
            const sRect = sourceEl.getBoundingClientRect()
            const dRect = destEl.getBoundingClientRect()

            lines.push({
                x1: sRect.left + sRect.width / 2 - rackRect.left,
                y1: sRect.top + sRect.height / 2 - rackRect.top,
                x2: dRect.left + dRect.width / 2 - rackRect.left,
                y2: dRect.top + dRect.height / 2 - rackRect.top,
                type: conn.connection_type
            })
        }
    })

    connectionLines.value = lines
  }

  const toggleConnections = () => {
    showConnections.value = !showConnections.value
    updateConnectionLines()
  }

  // Handle window resize to keep lines accurate
  onMounted(() => {
    window.addEventListener('resize', updateConnectionLines)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', updateConnectionLines)
  })

  // Handle window resize to keep lines accurate
  onMounted(() => {
    window.addEventListener('resize', updateConnectionLines)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', updateConnectionLines)
  })
</script>

<template>
  <Head :title="server.name" />

  <AppLayout
    :breadcrumbs="[
      { title: 'Servers', href: route('server.index') },
      { title: server.name, href: route('server.show', server.id) },
    ]">
    <div class="flex flex-col gap-6 p-4 md:p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="rounded-lg bg-primary/10 p-3">
            <ServerIcon class="h-6 w-6 text-primary" />
          </div>
          <div>
            <h1 class="text-3xl font-bold tracking-tight">
              {{ server.name }}
            </h1>
            <div class="mt-1 flex items-center gap-2">
              <Badge variant="outline">{{ server.code }}</Badge>
              <Badge variant="secondary">{{ server.function }}</Badge>
              <Badge :variant="getUiStatus(server.status)">{{ server.status }}</Badge>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Button as-child variant="outline">
            <Link :href="route('server.edit', server.id)">
              <Pencil class="mr-2 h-4 w-4" />
              Edit Server
            </Link>
          </Button>
          <DeleteAction :href="route('server.destroy', server.id)" />
        </div>
      </div>

      <!-- Desktop View: Vertical Tabs Layout -->
      <Tabs default-value="overview" class="w-full flex-col gap-8 md:flex md:flex-row">
        <TabsList class="h-auto w-full flex-col justify-start space-y-1 bg-transparent p-0 md:w-64">
          <TabsTrigger
            value="overview"
            class="w-full justify-start gap-3 px-4 py-2.5 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary">
            <Info class="h-4 w-4" />
            Overview
          </TabsTrigger>
          <TabsTrigger
            value="photos"
            class="w-full justify-start gap-3 px-4 py-2.5 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary">
            <ImageIcon class="h-4 w-4" />
            Photos
          </TabsTrigger>
          <TabsTrigger
            value="rack"
            class="w-full justify-start gap-3 px-4 py-2.5 text-left transition-all data-[state=active]:bg-primary/10 data-[state=active]:text-primary">
            <Layers class="h-4 w-4" />
            Rack Management
          </TabsTrigger>
        </TabsList>

        <div class="flex-1">
          <!-- Overview Tab -->
          <TabsContent value="overview" class="mt-0 space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
              <Card>
                <CardHeader>
                  <CardTitle>Placement Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                  <div class="grid grid-cols-2 gap-y-4 text-sm">
                    <div class="text-muted-foreground">Area</div>
                    <div class="font-medium text-primary">{{ server.area?.name || '-' }}</div>

                    <div class="text-muted-foreground">Linked POP</div>
                    <div>{{ server.pop?.name || 'No direct POP link' }}</div>

                    <div class="text-muted-foreground">Building</div>
                    <div>{{ server.building || '-' }}</div>

                    <div class="text-muted-foreground">Floor / Level</div>
                    <div>{{ server.floor || '-' }}</div>

                    <div class="text-muted-foreground">Internal Room/Area</div>
                    <div>{{ server.area_location || '-' }}</div>
                  </div>
                  <div class="border-t pt-4">
                    <p class="mb-1 text-sm text-muted-foreground">Description</p>
                    <p class="text-sm prose dark:prose-invert italic">
                      {{ server.description || 'No description provided.' }}
                    </p>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Geographical Location</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                  <div class="pointer-events-none overflow-hidden rounded-md border">
                    <LocationPicker :latitude="server.latitude" :longitude="server.longitude" />
                  </div>
                  <div class="grid grid-cols-2 gap-2 pt-2 text-sm">
                    <div class="flex items-center gap-1 text-muted-foreground"><MapPin class="h-3 w-3" /> Latitude</div>
                    <div class="font-mono">{{ server.latitude || '-' }}</div>

                    <div class="flex items-center gap-1 text-muted-foreground"><MapPin class="h-3 w-3" /> Longitude</div>
                    <div class="font-mono">{{ server.longitude || '-' }}</div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <!-- Photos Tab -->
          <TabsContent value="photos" class="mt-0 space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
              <Card v-for="category in ['Room', 'Rack', 'Installation', 'Cabling']" :key="category">
                <CardHeader class="pb-3">
                  <CardTitle class="text-lg">{{ category }} Documentation</CardTitle>
                </CardHeader>
                <CardContent>
                  <div v-if="getPhotosByCategory(category).length > 0" class="grid grid-cols-2 gap-4">
                    <div v-for="photo in getPhotosByCategory(category)" :key="photo.id" class="aspect-video overflow-hidden rounded-md border">
                      <a :href="'/storage/' + photo.path" target="_blank">
                        <img :src="'/storage/' + photo.path" class="h-full w-full object-cover transition-transform duration-300 hover:scale-105" />
                      </a>
                    </div>
                  </div>
                  <div v-else class="flex h-40 flex-col items-center justify-center rounded-md border border-dashed bg-muted/30">
                    <ImageIcon class="mb-2 h-8 w-8 text-muted-foreground/50" />
                    <p class="text-xs text-muted-foreground">No photos for {{ category.toLowerCase() }}</p>
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          <!-- Rack Management Tab -->
          <TabsContent value="rack" class="mt-0 space-y-6">
            <div class="flex flex-col gap-6">
                <!-- Rack Selector & Actions -->
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <template v-for="rack in server.racks" :key="rack.id">
                            <Button
                                :variant="selectedRackId === rack.id ? 'default' : 'outline'"
                                size="sm"
                                @click="selectedRackId = rack.id">
                                <Layers class="mr-2 h-4 w-4" />
                                {{ rack.name }}
                            </Button>
                        </template>
                        
                        <!-- Create Rack Button -->
                        <Dialog v-model:open="isCreateRackOpen">
                            <DialogTrigger as-child>
                                <Button variant="outline" size="sm" class="border-dashed">
                                    <Plus class="mr-2 h-4 w-4" />
                                    New Rack
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="sm:max-w-[425px]">
                                <form @submit.prevent="submitRack">
                                    <DialogHeader>
                                        <DialogTitle>Create New Rack</DialogTitle>
                                        <DialogDescription>Add a new server rack to this node.</DialogDescription>
                                    </DialogHeader>
                                    <div class="grid gap-4 py-4">
                                        <div class="grid grid-cols-4 items-center gap-4">
                                            <Label for="name" class="text-right">Name</Label>
                                            <Input id="name" v-model="rackForm.name" class="col-span-3" placeholder="Rack A-01" required />
                                        </div>
                                        <div class="grid grid-cols-4 items-center gap-4">
                                            <Label for="code" class="text-right">Code</Label>
                                            <Input id="code" v-model="rackForm.code" class="col-span-3" placeholder="RCK-001" required />
                                        </div>
                                        <div class="grid grid-cols-4 items-center gap-4">
                                            <Label for="u_capacity" class="text-right">Size (U)</Label>
                                            <Input id="u_capacity" type="number" v-model="rackForm.u_capacity" class="col-span-3" required />
                                        </div>
                                        <div class="grid grid-cols-4 items-center gap-4">
                                            <Label for="status" class="text-right">Status</Label>
                                            <Select v-model="rackForm.status">
                                                <SelectTrigger class="col-span-3 w-full">
                                                    <SelectValue placeholder="Select status" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="Active">Active</SelectItem>
                                                    <SelectItem value="Maintenance">Maintenance</SelectItem>
                                                    <SelectItem value="Planned">Planned</SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                    <DialogFooter>
                                        <Button type="submit" :disabled="rackForm.processing">Create Rack</Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>

                    <div v-if="selectedRack" class="flex items-center gap-2">
                        <!-- Connection Toggle -->
                        <div class="flex items-center gap-2 rounded-md border px-3 py-1.5 bg-muted/30">
                            <Label for="connection-toggle" class="text-xs font-medium text-muted-foreground cursor-pointer">Show Connections</Label>
                            <Switch id="connection-toggle" :checked="showConnections" @update:checked="toggleConnections" />
                        </div>
                        <Button variant="ghost" size="sm">
                            <Settings2 class="mr-2 h-4 w-4" />
                            Settings
                        </Button>
                        <DeleteAction :href="route('racks.destroy', selectedRack.id)" title="Delete Rack" description="Are you sure you want to delete this rack? All mounted devices will be unlinked.">
                            <template #trigger>
                                <Button variant="ghost" size="sm" class="text-destructive hover:bg-destructive/10">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Delete Rack
                                </Button>
                            </template>
                        </DeleteAction>
                    </div>
                </div>

                <!-- Rack Content Display -->
                <div v-if="selectedRack" class="grid gap-6 lg:grid-cols-3">
                    <!-- Rack Visual Section -->
                    <div class="lg:col-span-1">
                        <Card class="sticky top-6">
                            <CardHeader class="pb-3">
                                <CardTitle class="text-lg">Visual View ({{ selectedRack.u_capacity }}U)</CardTitle>
                                <CardDescription>{{ selectedRack.code }} - {{ selectedRack.brand || 'Universal' }}</CardDescription>
                            </CardHeader>
                            <CardContent class="px-3">
                                <div class="relative mx-auto w-full rounded-t-lg border-x-[12px] border-t-[12px] border-slate-800 bg-slate-900 shadow-2xl">
                                    <!-- Rack Interior -->
                                    <div ref="rackContainerRef" class="flex flex-col-reverse gap-px bg-slate-800 overflow-hidden relative">
                                        <!-- SVG Overlay for connections -->
                                        <svg v-if="connectionLines.length > 0" class="absolute inset-0 z-30 pointer-events-none w-full h-full" style="filter: drop-shadow(0 0 8px rgba(255,255,255,0.3))">
                                            <path v-for="(line, idx) in connectionLines" :key="idx"
                                                :d="`M ${line.x1} ${line.y1} C ${line.x1 + 40} ${line.y1}, ${line.x2 - 40} ${line.y2}, ${line.x2} ${line.y2}`"
                                                fill="none"
                                                stroke="white"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-dasharray="4 2"
                                                class="animate-[dash_20s_linear_infinite]"
                                            />
                                        </svg>

                                        <div v-for="u in selectedRack.u_capacity" :key="u" class="group relative flex h-[40px] w-full items-center bg-slate-900/50">
                                            <!-- U Number -->
                                            <div class="flex w-10 h-full items-center justify-center border-r border-slate-800 text-[10px] font-bold text-slate-500 bg-slate-950/40">
                                                {{ u }}
                                            </div>
                                            
                                            <!-- Slot Content -->
                                            <div class="relative flex-1 h-full">
                                                <template v-if="getDeviceAtU(selectedRack, u)">
                                                    <TooltipProvider>
                                                        <Tooltip>
                                                            <TooltipTrigger as-child>
                                                                    <div
                                                                        :ref="(el) => { if (el) deviceRefs.set(getDeviceAtU(selectedRack, u).device_type + ':' + getDeviceAtU(selectedRack, u).device_id, el as HTMLElement) }"
                                                                        @click="handleDeviceClick(getDeviceAtU(selectedRack, u).device_id, getDeviceAtU(selectedRack, u).device_type)"
                                                                        :class="[
                                                                            'absolute inset-x-0 bottom-0 z-10 flex items-center justify-between border-l-4 px-3 text-white shadow-2xl transition-all hover:brightness-110 cursor-pointer overflow-hidden',
                                                                            getDeviceAtU(selectedRack, u).color,
                                                                            'border-white/20',
                                                                            activeDeviceId === getDeviceAtU(selectedRack, u).device_id && activeDeviceType === getDeviceAtU(selectedRack, u).device_type ? 'ring-2 ring-white ring-inset brightness-125' : ''
                                                                        ]"
                                                                        :style="{ height: `calc(${getDeviceAtU(selectedRack, u).unit_size} * 40px + (${getDeviceAtU(selectedRack, u).unit_size} - 1) * 1px)` }">
                                                                        <div class="flex flex-col min-w-0 z-10">
                                                                            <span class="truncate text-[11px] font-bold leading-tight">{{ getDeviceAtU(selectedRack, u).device?.name }}</span>
                                                                            <span class="text-[9px] uppercase tracking-wider opacity-80 leading-tight font-medium">
                                                                                {{ getDeviceAtU(selectedRack, u).device_type.split('\\').pop() }}
                                                                            </span>
                                                                        </div>
                                                                        
                                                                        <!-- Hardware Ports Visualization -->
                                                                        <div class="absolute right-12 inset-y-1 flex items-center justify-end pointer-events-none">
                                                                            <div class="bg-black/10 backdrop-blur-[2px] rounded border border-white/10 p-1.5 flex items-center justify-center h-[calc(100%-8px)] my-auto">
                                                                                <div class="grid grid-flow-col gap-x-1.5 gap-y-1" 
                                                                                    :style="{ 
                                                                                        gridTemplateRows: `repeat(${getDeviceAtU(selectedRack, u).unit_size > 1 ? 4 : 2}, minmax(0, 1fr))`,
                                                                                        gridTemplateColumns: `repeat(${Math.ceil((getDeviceAtU(selectedRack, u).device?.interfaces?.length || getDeviceAtU(selectedRack, u).device?.port_count || 0) / (getDeviceAtU(selectedRack, u).unit_size > 1 ? 4 : 2))}, minmax(0, 1fr))`
                                                                                    }">
                                                                                    <!-- Use actual interfaces if available, otherwise fallback to port_count -->
                                                                                    <template v-if="getDeviceAtU(selectedRack, u).device?.interfaces?.length">
                                                                                        <div v-for="iface in getDeviceAtU(selectedRack, u).device.interfaces" 
                                                                                            :key="iface.id" 
                                                                                            :ref="(el) => { if (el) portRefs.set(`interface:${iface.id}`, el as HTMLElement) }"
                                                                                            :title="iface.name"
                                                                                            class="h-[5px] w-[5px] rounded-full bg-white/70 shadow-[0_0_4px_rgba(255,255,255,0.4)] ring-[0.5px] ring-white/20 transition-all hover:bg-white hover:scale-125">
                                                                                        </div>
                                                                                    </template>
                                                                                    <template v-else>
                                                                                        <div v-for="p in (getDeviceAtU(selectedRack, u).device?.port_count || 0)" 
                                                                                            :key="p" 
                                                                                            class="h-[5px] w-[5px] rounded-full bg-white/70 shadow-[0_0_4px_rgba(255,255,255,0.4)] ring-[0.5px] ring-white/20 transition-all hover:bg-white hover:scale-125">
                                                                                        </div>
                                                                                    </template>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="flex-shrink-0 h-1.5 w-1.5 rounded-full bg-white/40 ring-4 ring-white/5 ml-2 z-10"></div>
                                                                    </div>
                                                            </TooltipTrigger>
                                                            <TooltipContent side="right" :side-offset="15" class="w-64 p-0 overflow-hidden border-slate-800 bg-slate-950 shadow-2xl">
                                                                <div :class="['h-1', getDeviceAtU(selectedRack, u).color]"></div>
                                                                <div class="p-4 space-y-3">
                                                                    <div class="flex items-center justify-between border-b border-white/10 pb-2">
                                                                        <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Device Details</span>
                                                                        <Badge variant="outline" class="text-[10px] h-4 border-white/20 text-white">{{ getDeviceAtU(selectedRack, u).device_type.split('\\').pop() }}</Badge>
                                                                    </div>
                                                                    
                                                                    <div class="space-y-2">
                                                                        <div class="flex flex-col">
                                                                            <span class="text-[10px] text-slate-400 uppercase">Name</span>
                                                                            <span class="text-sm font-semibold text-white">{{ getDeviceAtU(selectedRack, u).device?.name }}</span>
                                                                        </div>
                                                                        
                                                                        <div class="grid grid-cols-2 gap-2">
                                                                            <div class="flex flex-col">
                                                                                <span class="text-[10px] text-slate-400 uppercase">Model</span>
                                                                                <span class="text-xs font-medium text-slate-200">{{ getDeviceAtU(selectedRack, u).device?.model || '-' }}</span>
                                                                            </div>
                                                                            <div class="flex flex-col">
                                                                                <span class="text-[10px] text-slate-400 uppercase">Code</span>
                                                                                <span class="text-xs font-medium text-slate-200">{{ getDeviceAtU(selectedRack, u).device?.code || '-' }}</span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="flex flex-col border-t border-white/5 pt-2">
                                                                            <span class="text-[10px] text-slate-400 uppercase">Hardware Configuration</span>
                                                                            <span class="text-xs font-medium text-slate-200">{{ getDeviceAtU(selectedRack, u).device?.port_count || 0 }} Total Ports</span>
                                                                        </div>

                                                                        <div class="flex flex-col border-t border-white/5 pt-2">
                                                                            <span class="text-[10px] text-slate-400 uppercase">Rack Allocation</span>
                                                                            <span class="text-xs font-medium text-slate-200">U{{ getDeviceAtU(selectedRack, u).unit_start }} ({{ getDeviceAtU(selectedRack, u).unit_size }} Unit)</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </TooltipContent>
                                                        </Tooltip>
                                                    </TooltipProvider>
                                                </template>
                                                <template v-else-if="!isUSlotOccupied(selectedRack, u)">
                                                    <div
                                                        @click="openMountModal(u)"
                                                        class="flex h-full w-full cursor-pointer items-center justify-center text-[10px] font-medium text-slate-700 opacity-0 transition-opacity hover:bg-primary/5 hover:text-primary hover:opacity-100">
                                                        MOUNT HERE
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="absolute -bottom-4 -left-3 -right-3 h-4 rounded-b-md bg-slate-800"></div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Inventory Section -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold tracking-tight">Rack Inventory</h2>
                            <Button size="sm" @click="openMountModal()">
                                <Plus class="mr-2 h-4 w-4" />
                                Mount Device
                            </Button>
                        </div>

                        <div class="grid gap-1">
                            <Card v-for="item in selectedRack.contents" :key="item.id" class="group overflow-hidden transition-all hover:border-primary/50 shadow-sm border-slate-100 dark:border-white/5">
                                <div class="flex h-full">
                                    <div :class="['w-1 transition-all group-hover:w-1.5', item.color]"></div>
                                    <CardContent class="flex flex-1 items-center justify-between py-1.5 px-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded bg-slate-100 dark:bg-slate-800 text-slate-500">
                                                <Cpu class="h-4 w-4" />
                                            </div>
                                            <div>
                                                <div class="font-semibold text-sm leading-tight flex items-center gap-2">
                                                    {{ item.device?.name || 'Unknown Device' }}
                                                    <Badge variant="outline" class="text-[10px] h-4 px-1.5 font-normal opacity-70 border-slate-200 dark:border-white/10">{{ item.device_type.split('\\').pop() }}</Badge>
                                                </div>
                                                <div class="flex items-center gap-2 text-xs text-muted-foreground mt-0.5">
                                                    <span class="font-mono bg-muted/70 px-1.5 rounded text-primary">U{{ item.unit_start }}-{{ item.unit_start - item.unit_size + 1 }}</span>
                                                    <span>{{ item.unit_size }}U</span>
                                                    <span v-if="item.device?.code" class="opacity-60">• {{ item.device.code }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <DeleteAction 
                                                :href="route('racks.unmount', item.id)" 
                                                title="Unmount Device"
                                                description="Are you sure you want to remove this device from the rack? This will free up the occupied slots."
                                            >
                                                <template #trigger>
                                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive hover:bg-destructive/10">
                                                        <Trash2 class="h-4 w-4" />
                                                    </Button>
                                                </template>
                                            </DeleteAction>
                                        </div>
                                    </CardContent>
                                </div>
                            </Card>

                            <div v-if="!selectedRack.contents?.length" class="flex h-40 flex-col items-center justify-center rounded-lg border-2 border-dashed bg-muted/20">
                                <Layers class="mb-2 h-8 w-8 text-muted-foreground/30" />
                                <p class="text-sm text-muted-foreground">No devices mounted in this rack.</p>
                                <Button variant="link" size="sm" @click="openMountModal()">Mount your first device</Button>
                            </div>
                        </div>

                        <!-- Stats Summary -->
                        <div class="grid grid-cols-3 gap-4 font-mono">
                            <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-900 border">
                                <div class="text-[10px] uppercase text-muted-foreground mb-1">Capacity</div>
                                <div class="text-2xl font-bold">{{ selectedRack.u_capacity }}U</div>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-900 border">
                                <div class="text-[10px] uppercase text-muted-foreground mb-1">Occupied</div>
                                <div class="text-2xl font-bold text-primary">
                                    {{ selectedRack.contents?.reduce((acc: number, c: any) => acc + c.unit_size, 0) || 0 }}U
                                </div>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-900 border">
                                <div class="text-[10px] uppercase text-muted-foreground mb-1">Available</div>
                                <div class="text-2xl font-bold text-emerald-500">
                                    {{ selectedRack.u_capacity - (selectedRack.contents?.reduce((acc: number, c: any) => acc + c.unit_size, 0) || 0) }}U
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="flex h-64 flex-col items-center justify-center rounded-xl border-2 border-dashed bg-muted/20">
                    <Layers class="mb-4 h-12 w-12 text-muted-foreground/20" />
                    <h3 class="text-xl font-semibold text-muted-foreground">No Racks Configured</h3>
                    <p class="mb-4 text-sm text-muted-foreground">Start by creating a rack to organize your devices.</p>
                    <Button @click="isCreateRackOpen = true">
                        <Plus class="mr-2 h-4 w-4" />
                        Create First Rack
                    </Button>
                </div>
            </div>
          </TabsContent>
        </div>
      </Tabs>
    </div>

    <!-- Mount Device Modal -->
    <Dialog v-model:open="isMountDeviceOpen">
        <DialogContent class="sm:max-w-2xl">
            <form @submit.prevent="submitMount">
                <DialogHeader>
                    <DialogTitle>Mount Device to Rack</DialogTitle>
                    <DialogDescription>Assign a device to a specific position in {{ selectedRack?.name }}.</DialogDescription>
                </DialogHeader>
                
                <div class="grid grid-cols-1 md:grid-cols-[180px_1fr] gap-6 py-6">
                    <!-- Left: Mini Rack View (Reduced Size) -->
                    <div class="hidden md:flex flex-col">
                        <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-2 px-1">Rack Preview</span>
                        <div class="flex-1 border-2 border-slate-800 rounded-lg bg-slate-950 p-1.5 overflow-y-auto max-h-[350px] scrollbar-thin">
                            <div class="flex flex-col-reverse gap-px bg-slate-800 overflow-hidden">
                                <div v-for="u in selectedRack?.u_capacity" :key="u" 
                                    class="relative h-[28px] w-full border-b border-slate-900 flex items-center px-1.5 cursor-pointer transition-colors hover:bg-white/5"
                                    @click="mountForm.unit_start = u">
                                    <span class="text-[9px] font-mono text-slate-600 w-4">{{ u }}</span>
                                    <div class="flex-1 h-full relative">
                                        <!-- Proposed Selection -->
                                        <div v-if="u >= mountForm.unit_start && u < mountForm.unit_start + mountForm.unit_size"
                                            :class="[
                                                'absolute inset-x-0 inset-y-0.5 z-20 border flex items-center justify-center transition-all',
                                                isOverlapDetected 
                                                    ? 'bg-destructive/40 border-destructive' 
                                                    : [mountForm.color.replace('bg-', 'bg-') + '/40', mountForm.color.replace('bg-', 'border-'), 'shadow-lg']
                                            ]">
                                            <span v-if="u === mountForm.unit_start" class="text-[8px] font-bold text-white uppercase truncate px-1">New</span>
                                        </div>
                                        <!-- Existing Content -->
                                        <div v-if="isUSlotOccupied(selectedRack, u)" class="absolute inset-x-0 inset-y-0.5 z-10 bg-slate-800/80 border-x border-slate-700 flex items-center px-1">
                                            <div class="h-1 w-1 rounded-full bg-slate-600 mr-1"></div>
                                            <span class="text-[7px] text-slate-600 uppercase font-medium">Full</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Form Details -->
                    <div class="flex flex-col h-full">
                        <div v-if="isLoadingDevices" class="flex flex-col items-center justify-center flex-1 py-12">
                            <Loader2 class="h-8 w-8 animate-spin text-primary" />
                            <p class="mt-2 text-sm text-muted-foreground">Fetching available devices...</p>
                        </div>
                        
                        <div v-else-if="availableDevices.length === 0" class="flex flex-col items-center justify-center flex-1 py-12 text-center">
                            <Cpu class="h-10 w-10 text-muted-foreground/30 mb-3" />
                            <p class="text-sm font-semibold">No Available Devices</p>
                            <p class="text-xs text-muted-foreground mt-1 max-w-[200px]">
                                Devices must be in the same Area and POP as this server.
                            </p>
                        </div>

                        <div v-else class="space-y-6">
                            <div class="space-y-2">
                                <Label class="text-xs uppercase tracking-wider text-muted-foreground">Select Device</Label>
                                <Select v-model="mountForm.device_id" @update:model-value="(val) => {
                                    const d = availableDevices.find(ad => ad.id === parseInt(val));
                                    mountForm.device_type = d.class;
                                }">
                                    <SelectTrigger class="w-full h-11">
                                        <SelectValue placeholder="Identify device to mount..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="device in availableDevices" :key="device.id" :value="device.id.toString()">
                                            <div class="flex flex-col py-0.5">
                                                <span class="font-medium">{{ device.name }}</span>
                                                <span class="text-[10px] text-muted-foreground">{{ device.type }} • {{ device.code }}</span>
                                            </div>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label class="text-xs uppercase tracking-wider text-muted-foreground">Starting U</Label>
                                    <Input type="number" v-model="mountForm.unit_start" :max="selectedRack?.u_capacity" min="1" class="h-11 font-mono" />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-xs uppercase tracking-wider text-muted-foreground">Size (Units)</Label>
                                    <Input type="number" v-model="mountForm.unit_size" min="1" max="10" class="h-11 font-mono" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label class="text-xs uppercase tracking-wider text-muted-foreground">Identification Color</Label>
                                <div class="flex flex-wrap gap-3 p-3 bg-muted/30 rounded-lg border border-dashed">
                                    <button
                                        v-for="color in deviceColors"
                                        :key="color.value"
                                        type="button"
                                        @click="mountForm.color = color.value"
                                        :class="[
                                            'h-7 w-7 rounded-full border-2 transition-transform hover:scale-110 active:scale-95',
                                            color.value,
                                            mountForm.color === color.value ? 'ring-2 ring-primary ring-offset-2 border-white' : 'border-transparent'
                                        ]"
                                        :title="color.name"
                                    ></button>
                                </div>
                            </div>

                            <!-- Alert Banner -->
                            <div v-if="isOverlapDetected" class="flex gap-3 bg-destructive/10 border border-destructive/20 p-3 rounded-lg animate-in fade-in slide-in-from-top-1">
                                <Trash2 class="h-5 w-5 text-destructive shrink-0" />
                                <div class="space-y-1">
                                    <p class="text-xs font-bold text-destructive uppercase">Collision Detected</p>
                                    <p class="text-[11px] text-destructive/80 leading-tight">
                                        The selected units are already occupied by another device. Please pick a different slot.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter class="border-t pt-4">
                    <Button type="button" variant="ghost" @click="isMountDeviceOpen = false">Cancel</Button>
                    <Button type="submit" :disabled="mountForm.processing || !mountForm.device_id || isOverlapDetected">
                        {{ mountForm.processing ? 'Mounting...' : 'Mount Device' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<style scoped>
@keyframes dash {
  from {
    stroke-dashoffset: 200;
  }
  to {
    stroke-dashoffset: 0;
  }
}
</style>
