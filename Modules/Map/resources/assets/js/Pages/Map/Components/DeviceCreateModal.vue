<script setup lang="ts">
  import { Button } from '@/components/ui/button'
  import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Textarea } from '@/components/ui/textarea'
  import { useForm } from '@inertiajs/vue3'
  import { Plus, Trash2 } from 'lucide-vue-next'
  import { computed, watch } from 'vue'
  import { toast } from 'vue-sonner'

  const props = defineProps<{
    open: boolean
    deviceType: string | null
    lat: number | null
    lng: number | null
    path?: Array<[number, number]> | null
    areas: Array<any>
    pops: Array<any>
    selectedAreaId?: string | null
    initialLength?: number
    // Cable node references (from snap feature)
    startNode?: { id: number; type: string; name: string } | null
    endNode?: { id: number; type: string; name: string } | null
    waypointPoles?: number[]
  }>()

  const emit = defineEmits(['update:open', 'success', 'cancel'])

  const form = useForm({
    infrastructure_area_id: '',
    pop_id: '',
    code: '',
    name: '',
    brand: '',
    model: '',
    latitude: '',
    longitude: '',
    path: [] as Array<[number, number]>,
    description: '',
    // specific fields
    port_count: 0,
    used_port_count: 0,
    input_count: 0,
    output_count: 0,
    core_capacity: 0,
    onu_type: '',
    frequency: '',
    length: 0,
    core_count: 0,
    type: 'Single Mode',
    // OLT fields
    pon_port_count: 8,
    uplink_port_count: 4,
    // pole fields
    height: '',
    material: 'Besi',
    ownership: 'Sendiri',
    antenna_capacity: '',
    service_ports: [] as Array<{
      name: string
      port: number | string
      status: string
    }>,
    // Cable node references
    start_node_id: null as number | null,
    start_node_type: '',
    end_node_id: null as number | null,
    end_node_type: '',
    waypoint_poles: [] as number[],
  })
  watch(
    () => props.open,
    (newVal: boolean) => {
      if (newVal) {
        form.reset()
        form.latitude = props.lat?.toString() || ''
        form.longitude = props.lng?.toString() || ''
        form.path = props.path || []
        form.length = props.initialLength ? Number(props.initialLength.toFixed(2)) : 0

        // Auto-populate area if selected
        if (props.selectedAreaId && props.selectedAreaId !== 'all') {
          form.infrastructure_area_id = props.selectedAreaId
        } else {
          form.infrastructure_area_id = ''
        }

        form.pop_id = ''

        // Default service ports for active devices
        if (isActiveDevice()) {
          form.service_ports = [
            { name: 'SSH', port: 22, status: 'Active' },
            { name: 'Winbox', port: 1098, status: 'Active' },
            { name: 'Telnet', port: 23, status: 'Active' },
            { name: 'Http', port: 80, status: 'Active' },
          ]
        } else {
          form.service_ports = []
        }

        // Cable node references (from snap feature)
        if (props.startNode) {
          form.start_node_id = props.startNode.id
          form.start_node_type = props.startNode.type
        }
        if (props.endNode) {
          form.end_node_id = props.endNode.id
          form.end_node_type = props.endNode.type
        }
        if (props.waypointPoles && props.waypointPoles.length > 0) {
          form.waypoint_poles = props.waypointPoles
        }
      }
    },
  )

  const addServicePort = () => {
    form.service_ports.push({ name: '', port: '', status: 'Active' })
  }

  const removeServicePort = (index: number) => {
    form.service_ports.splice(index, 1)
  }

  const getStoreUrl = () => {
    const urls: Record<string, string> = {
      olt: route('active-device.olt.store'),
      ont: route('active-device.ont.store'),
      router: route('active-device.router.store'),
      switch: route('active-device.switch.store'),
      'access-point': route('active-device.access-point.store'),
      odp: route('passive-device.odps.store'),
      odf: route('passive-device.odf.store'),
      pole: route('passive-device.pole.store'),
      tower: route('passive-device.tower.store'),
      'joint-box': route('passive-device.joint-box.store'),
      cable: route('passive-device.cable.store'),
    }
    return urls[props.deviceType || ''] || ''
  }

  const submit = () => {
    form.post(getStoreUrl(), {
      onSuccess: () => {
        emit('update:open', false)
        emit('success')
        toast.success('Device created successfully')
      },
      onError: (errors) => {
        toast.error('Failed to create device. Please check the form.')
        console.error(errors)
      },
    })
  }

  const isActiveDevice = () => {
    return ['olt', 'ont', 'router', 'switch', 'access-point'].includes(props.deviceType || '')
  }
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[500px]">
      <DialogHeader>
        <DialogTitle>Add New {{ deviceType?.toUpperCase().replace('-', ' ') }}</DialogTitle>
        <DialogDescription> Fill in the details for the new device at the selected location. </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submit" class="space-y-4 py-4">
        <div class="space-y-2">
          <Label>Area</Label>
          <Select v-model="form.infrastructure_area_id" :disabled="!!selectedAreaId && selectedAreaId !== 'all'">
            <SelectTrigger class="w-full">
              <SelectValue placeholder="Select Area" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="area in areas" :key="area.id" :value="area.id.toString()">
                {{ area.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p v-if="form.errors.infrastructure_area_id" class="text-xs text-red-500">
            {{ form.errors.infrastructure_area_id }}
          </p>
        </div>

        <div v-if="isActiveDevice()" class="space-y-2">
          <Label>POP</Label>
          <Select v-model="form.pop_id">
            <SelectTrigger class="w-full">
              <SelectValue placeholder="Select POP" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="pop in pops" :key="pop.id" :value="pop.id.toString()">
                {{ pop.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p v-if="form.errors.pop_id" class="text-xs text-red-500">
            {{ form.errors.pop_id }}
          </p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Code</Label>
            <Input v-model="form.code" placeholder="Device Code" />
            <p v-if="form.errors.code" class="text-xs text-red-500">
              {{ form.errors.code }}
            </p>
          </div>
          <div class="space-y-2">
            <Label>Name</Label>
            <Input v-model="form.name" placeholder="Device Name" />
            <p v-if="form.errors.name" class="text-xs text-red-500">
              {{ form.errors.name }}
            </p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Latitude</Label>
            <Input v-model="form.latitude" readonly class="bg-gray-50" />
          </div>
          <div class="space-y-2">
            <Label>Longitude</Label>
            <Input v-model="form.longitude" readonly class="bg-gray-50" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4" v-if="deviceType !== 'tower'">
          <div class="space-y-2">
            <Label>Brand</Label>
            <Input v-model="form.brand" />
          </div>
          <div class="space-y-2">
            <Label>Model</Label>
            <Input v-model="form.model" />
          </div>
        </div>

        <div v-if="deviceType === 'olt'" class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>PON Port Count</Label>
            <Input type="number" v-model="form.pon_port_count" />
            <p v-if="form.errors.pon_port_count" class="text-xs text-red-500">
              {{ form.errors.pon_port_count }}
            </p>
          </div>
          <div class="space-y-2">
            <Label>Uplink Port Count</Label>
            <Input type="number" v-model="form.uplink_port_count" />
            <p v-if="form.errors.uplink_port_count" class="text-xs text-red-500">
              {{ form.errors.uplink_port_count }}
            </p>
          </div>
        </div>

        <!-- Type Specific Fields -->
        <div v-if="deviceType === 'odp' || deviceType === 'odf' || deviceType === 'router' || deviceType === 'switch'" class="space-y-2">
          <Label>Port Count</Label>
          <Input type="number" v-model="form.port_count" />
        </div>

        <div v-if="deviceType === 'odp' || deviceType === 'odf'" class="space-y-2">
          <Label>Used Port Count</Label>
          <Input type="number" v-model="form.used_port_count" />
        </div>

        <div v-if="deviceType === 'joint-box'" class="grid grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label>Input Count</Label>
            <Input type="number" v-model="form.input_count" />
          </div>
          <div class="space-y-2">
            <Label>Output Count</Label>
            <Input type="number" v-model="form.output_count" />
          </div>
        </div>

        <div v-if="deviceType === 'odf'" class="space-y-2">
          <Label>Core Capacity</Label>
          <Input type="number" v-model="form.core_capacity" />
        </div>

        <div v-if="deviceType === 'ont'" class="space-y-2">
          <Label>ONU Type (GPON/EPON/XPON)</Label>
          <Input v-model="form.onu_type" />
        </div>

        <div v-if="deviceType === 'access-point'" class="space-y-2">
          <Label>Frequency</Label>
          <Input v-model="form.frequency" placeholder="2.4GHz / 5GHz" />
        </div>

        <!-- Pole Specific Fields -->
        <div v-if="deviceType === 'pole'" class="space-y-4 border-t pt-2">
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Material</Label>
              <Select v-model="form.material">
                <SelectTrigger>
                  <SelectValue placeholder="Select Material" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Beton">Beton</SelectItem>
                  <SelectItem value="Besi">Besi</SelectItem>
                  <SelectItem value="Kayu">Kayu</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label>Height (m)</Label>
              <Input type="number" v-model="form.height" />
            </div>
          </div>
          <div class="space-y-2">
            <Label>Ownership</Label>
            <Select v-model="form.ownership">
              <SelectTrigger>
                <SelectValue placeholder="Select Ownership" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Sendiri">Sendiri</SelectItem>
                <SelectItem value="PLN">PLN</SelectItem>
                <SelectItem value="Sewa">Sewa</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <!-- Tower Specific Fields -->
        <div v-if="deviceType === 'tower'" class="space-y-4 border-t pt-2">
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Tower Type</Label>
              <Select v-model="form.type">
                <SelectTrigger>
                  <SelectValue placeholder="Select Type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="SST">SST</SelectItem>
                  <SelectItem value="Monopole">Monopole</SelectItem>
                  <SelectItem value="Guyed">Guyed</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label>Height (m)</Label>
              <Input type="number" v-model="form.height" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Ownership</Label>
              <Select v-model="form.ownership">
                <SelectTrigger>
                  <SelectValue placeholder="Select Ownership" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Sendiri">Sendiri</SelectItem>
                  <SelectItem value="Sewa">Sewa</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label>Antenna Capacity</Label>
              <Input v-model="form.antenna_capacity" />
            </div>
          </div>
        </div>

        <!-- Cable Specific Fields -->
        <div v-if="deviceType === 'cable'" class="space-y-4 border-t pt-2">
          <!-- Connection Info Display -->
          <div class="rounded-lg bg-slate-50 dark:bg-slate-800/50 p-3 space-y-2">
            <Label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Informasi Koneksi</Label>
            <div class="grid grid-cols-2 gap-2 text-xs">
              <div class="flex items-center gap-2">
                <span
                  class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                  <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="5" /></svg>
                </span>
                <span class="text-slate-600 dark:text-slate-400">Awal:</span>
                <span class="font-medium text-slate-900 dark:text-slate-100">{{ startNode?.name || 'Tidak tersnap' }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">
                  <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="5" /></svg>
                </span>
                <span class="text-slate-600 dark:text-slate-400">Akhir:</span>
                <span class="font-medium text-slate-900 dark:text-slate-100">{{ endNode?.name || 'Tidak tersnap' }}</span>
              </div>
            </div>
            <div
              v-if="waypointPoles && waypointPoles.length > 0"
              class="flex items-center gap-2 text-xs pt-1 border-t border-slate-200 dark:border-slate-700">
              <span
                class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
              </span>
              <span class="text-slate-600 dark:text-slate-400">Melewati:</span>
              <span class="font-medium text-amber-600 dark:text-amber-400">{{ waypointPoles.length }} tiang</span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Cable Type</Label>
              <Select v-model="form.type">
                <SelectTrigger>
                  <SelectValue placeholder="Select Type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Single Mode">Single Mode</SelectItem>
                  <SelectItem value="Multi Mode">Multi Mode</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label>Core Count</Label>
              <Input type="number" v-model="form.core_count" />
            </div>
          </div>
          <div class="space-y-2">
            <Label>Approx. Length (m)</Label>
            <Input type="number" step="0.01" v-model="form.length" />
          </div>
        </div>

        <div class="space-y-2">
          <Label>Description</Label>
          <Textarea v-model="form.description" rows="2" />
        </div>

        <!-- Service Ports for Active Devices -->
        <div v-if="isActiveDevice()" class="space-y-4 border-t pt-4">
          <div class="flex items-center justify-between">
            <Label class="text-sm font-semibold">Service Ports</Label>
            <Button type="button" variant="outline" size="sm" class="h-7 px-2 text-xs" @click="addServicePort">
              <Plus class="mr-1 h-3 w-3" /> Add Port
            </Button>
          </div>

          <div v-for="(sp, index) in form.service_ports" :key="index" class="grid grid-cols-12 items-end gap-2">
            <div class="col-span-4 space-y-1">
              <Label v-if="index === 0" class="text-[10px]">Name</Label>
              <Input v-model="sp.name" placeholder="e.g. SSH" class="h-8 text-xs" />
            </div>
            <div class="col-span-3 space-y-1">
              <Label v-if="index === 0" class="text-[10px]">Port</Label>
              <Input type="number" v-model="sp.port" placeholder="22" class="h-8 text-xs" />
            </div>
            <div class="col-span-3 space-y-1">
              <Label v-if="index === 0" class="text-[10px]">Status</Label>
              <Select v-model="sp.status">
                <SelectTrigger class="h-8 text-xs">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Active">Active</SelectItem>
                  <SelectItem value="Inactive">Inactive</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="col-span-2">
              <Button
                type="button"
                variant="ghost"
                size="icon"
                @click="removeServicePort(index)"
                class="h-8 w-8 text-red-500 hover:bg-red-50 hover:text-red-600">
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>
          <p v-if="form.errors.service_ports" class="text-xs text-red-500">
            {{ form.errors.service_ports }}
          </p>
        </div>

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="
              () => {
                emit('update:open', false)
                emit('cancel')
              }
            ">
            Cancel
          </Button>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Saving...' : 'Save Device' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
