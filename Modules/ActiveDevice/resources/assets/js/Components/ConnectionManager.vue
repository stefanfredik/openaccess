<script setup lang="ts">
  import { router, useForm } from '@inertiajs/vue3'
  import axios from 'axios'
  import { ArrowRight, Link as LinkIcon, Plus, Server, Trash2 } from 'lucide-vue-next'
  import { computed, ref, watch } from 'vue'
  import { Badge } from '@/components/ui/badge'
  import { Button } from '@/components/ui/button'
  import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
  import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

  const props = defineProps<{
    device: any
    deviceType: string
    connections: Array<any>
    incomingConnections?: Array<any>
    availableDevices: Array<any>
  }>()

  const showAddForm = ref(false)
  const destinationInterfaces = ref<Array<any>>([])

  const form = useForm({
    source_id: props.device.id,
    source_type: props.deviceType,
    destination_id: '',
    destination_type: '',
    connection_type: 'Downlink',
    source_port: '',
    destination_port: '',
    description: '',
  })

  // Merge interfaces with connections
  const portTableData = computed(() => {
    const interfaces = props.device.interfaces || []
    return interfaces.map((inf: any) => {
      // Check Outbound (Source = This Port)
      const outbound = props.connections.find((c: any) => {
        const match = c.source_port == inf.id // Using loose equality to check for type mismatch
        if (c.source_port == inf.id && c.source_port !== inf.id) {
          // console.warn('Type mismatch detected:', {
          //     connPort: c.source_port,
          //     typeC: typeof c.source_port,
          //     infId: inf.id,
          //     typeI: typeof inf.id,
          // });
        }
        return match
      })
      // Check Inbound (Dest = This Port)
      const inbound = props.incomingConnections?.find(
        (c: any) => c.destination_port == inf.id, // Loose equality
      )

      return {
        interface: inf,
        status: outbound ? 'connected_out' : inbound ? 'connected_in' : 'available',
        connection: outbound || inbound,
        is_parent: !!inbound,
      }
    })
  })

  watch(
    () => form.destination_id,
    async (newId: string | number | null) => {
      if (!newId || !form.destination_type) {
        destinationInterfaces.value = []
        form.destination_port = ''
        return
      }

      try {
        const response = await axios.get(route('active-device.interfaces.list'), {
          params: {
            type: form.destination_type,
            id: newId,
          },
        })
        destinationInterfaces.value = response.data
        form.destination_port = '' // Reset port
      } catch (error) {
        console.error('Failed to fetch destination interfaces', error)
        destinationInterfaces.value = []
      }
    },
  )

  watch(
    () => form.destination_type,
    () => {
      form.destination_id = ''
      destinationInterfaces.value = []
      form.destination_port = ''
    },
  )

  const submit = () => {
    form.post('/topology/connections', {
      onSuccess: () => {
        form.reset('destination_id', 'destination_type', 'source_port', 'destination_port', 'description')
        showAddForm.value = false
      },
    })
  }

  const deleteConnection = (id: number) => {
    if (confirm('Are you sure you want to remove this connection?')) {
      router.delete(`/topology/connections/${id}`)
    }
  }

  const openAdd = (preselectedPort: any = '') => {
    if (preselectedPort) {
      form.source_port = preselectedPort
    }
    showAddForm.value = true
  }

  defineExpose({ openAdd })
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between pb-3">
      <CardTitle class="text-sm font-medium tracking-wider text-muted-foreground uppercase"> Connectivity </CardTitle>
      <Dialog v-model:open="showAddForm">
        <DialogTrigger as-child>
          <Button size="sm" class="h-8 gap-1">
            <Plus class="h-4 w-4" />
            <span class="sr-only sm:not-sr-only sm:whitespace-nowrap"> Add Link </span>
          </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[500px]">
          <DialogHeader>
            <DialogTitle>Add New Link</DialogTitle>
            <DialogDescription> Connect this device to a downstream target. </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="submit" class="space-y-6 pt-4">
            <div class="space-y-6">
              <!-- Step 1: Target Selection -->
              <div class="space-y-4 rounded-lg bg-muted/30 p-4">
                <h3 class="text-xs font-semibold tracking-wide text-primary uppercase">Perangkat Tujuan</h3>
                <div class="grid grid-cols-1 gap-x-4 gap-y-4 md:grid-cols-2">
                  <div class="space-y-2">
                    <Label class="text-xs font-medium">Device Type</Label>
                    <Select v-model="form.destination_type">
                      <SelectTrigger class="h-9 w-full">
                        <SelectValue placeholder="Select Type" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Modules\ActiveDevice\Models\Router">Router</SelectItem>
                        <SelectItem value="Modules\ActiveDevice\Models\AdSwitch">Switch</SelectItem>
                        <SelectItem value="Modules\ActiveDevice\Models\Olt">OLT</SelectItem>
                        <SelectItem value="Modules\ActiveDevice\Models\Ont">ONT</SelectItem>
                        <SelectItem value="Modules\ActiveDevice\Models\AccessPoint">AP</SelectItem>
                        <SelectItem value="Modules\Cpe\Models\Cpe">CPE</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="space-y-2">
                    <Label class="text-xs font-medium">Target Device</Label>
                    <Select v-model="form.destination_id" :disabled="!form.destination_type">
                      <SelectTrigger class="h-9 w-full">
                        <SelectValue placeholder="Select Device" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem
                          v-for="d in availableDevices.filter((d: any) => d.type === form.destination_type)"
                          :key="d.id"
                          :value="d.id.toString()">
                          {{ d.name }} ({{ d.code }})
                        </SelectItem>
                      </SelectContent>
                    </Select>
                    <div v-if="form.errors.destination_id" class="text-[10px] text-red-500">
                      {{ form.errors.destination_id }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 2: Port Mapping -->
              <div class="space-y-4 rounded-lg bg-muted/30 p-4">
                <h3 class="text-xs font-semibold tracking-wide text-primary uppercase">Mapping Por</h3>
                <div class="grid grid-cols-1 items-start gap-x-4 gap-y-4 md:grid-cols-[1fr,auto,1fr]">
                  <div class="space-y-2">
                    <Label class="text-xs font-medium">Local Port (Source)</Label>
                    <div v-if="device.interfaces && device.interfaces.length > 0">
                      <Select v-model="form.source_port">
                        <SelectTrigger class="h-9 w-full">
                          <SelectValue placeholder="Select Source" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem v-for="inf in device.interfaces" :key="inf.id" :value="inf.id">
                            {{ inf.name }}
                            <span v-if="inf.status === 'up'" class="ml-2 text-xs text-green-500">(UP)</span>
                            <span v-else-if="inf.status === 'idle'" class="ml-2 text-xs text-muted-foreground">(IDLE)</span>
                            <span v-else class="ml-2 text-xs text-red-500">({{ inf.status }})</span>
                          </SelectItem>
                        </SelectContent>
                      </Select>
                      <p class="mt-1 text-[10px] text-muted-foreground">From: {{ device.name }}</p>
                    </div>
                    <div v-else class="rounded border border-yellow-200 bg-yellow-50 p-2 text-xs text-yellow-800">No local interfaces.</div>
                  </div>
                  <div v-if="form.errors.source_port" class="text-[10px] text-red-500">
                    {{ form.errors.source_port }}
                  </div>

                  <div class="flex h-full items-center justify-center pt-6">
                    <ArrowRight class="h-4 w-4 text-muted-foreground" />
                  </div>

                  <div class="space-y-2">
                    <Label class="text-xs font-medium">Remote Port (Dest)</Label>
                    <div v-if="destinationInterfaces.length > 0">
                      <Select v-model="form.destination_port">
                        <SelectTrigger class="h-9 w-full">
                          <SelectValue placeholder="Select Dest" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem v-for="inf in destinationInterfaces" :key="inf.id" :value="inf.id">
                            {{ inf.name }}
                            <span v-if="inf.status === 'up'" class="ml-2 text-xs text-green-500">(UP)</span>
                            <span v-else-if="inf.status === 'idle'" class="ml-2 text-xs text-muted-foreground">(IDLE)</span>
                            <span v-else class="ml-2 text-xs text-red-500">({{ inf.status }})</span>
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    <div v-else-if="form.destination_id" class="rounded border border-yellow-200 bg-yellow-50 p-2 text-xs text-yellow-800">
                      No interfaces found.
                    </div>
                    <Input v-else v-model="form.destination_port" class="h-9 w-full" disabled placeholder="Select target first" />
                  </div>
                  <div v-if="form.errors.destination_port" class="text-[10px] text-red-500">
                    {{ form.errors.destination_port }}
                  </div>
                </div>
              </div>

              <!-- Step 3: Attributes -->
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-2">
                  <Label class="text-xs font-medium">Link Type</Label>
                  <Select v-model="form.connection_type">
                    <SelectTrigger class="h-9 w-full">
                      <SelectValue placeholder="Select Type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Uplink">Uplink</SelectItem>
                      <SelectItem value="Downlink">Downlink</SelectItem>
                      <SelectItem value="Fiber">Fiber</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div class="space-y-2">
                  <Label class="text-xs font-medium">Description</Label>
                  <Input v-model="form.description" class="h-9 w-full" placeholder="Optional description" />
                </div>
              </div>
            </div>

            <DialogFooter class="border-t pt-4">
              <Button type="button" variant="outline" @click="showAddForm = false">Cancel</Button>
              <Button type="submit" :disabled="form.processing || !form.destination_port || !form.source_port">Save Link</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </CardHeader>

    <CardContent>
      <div class="rounded-md border bg-card">
        <Table>
          <TableHeader class="bg-muted/50">
            <TableRow>
              <TableHead class="w-[100px]">Interface</TableHead>
              <TableHead class="w-[120px]">Status</TableHead>
              <TableHead>Connected To</TableHead>
              <TableHead class="w-[120px]">Type</TableHead>
              <TableHead class="w-[50px]"></TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(row, index) in portTableData" :key="index" class="hover:bg-muted/5">
              <TableCell>
                <div class="flex items-center gap-2">
                  <span class="font-mono font-bold">{{ row.interface.name }}</span>
                  <Badge v-if="row.interface.status === 'up'" variant="outline" class="border-green-500/50 text-[10px] text-green-600 uppercase">
                    UP
                  </Badge>
                  <Badge v-else variant="outline" class="text-[10px] text-muted-foreground uppercase">
                    {{ row.interface.status }}
                  </Badge>
                </div>
              </TableCell>
              <TableCell>
                <div v-if="row.status === 'connected_out'">
                  <Badge class="bg-green-500 hover:bg-green-600">
                    <ArrowRight class="mr-1 h-3 w-3" />
                    Uplink
                  </Badge>
                </div>
                <div v-else-if="row.status === 'connected_in'">
                  <Badge class="bg-blue-500 hover:bg-blue-600">
                    <LinkIcon class="mr-1 h-3 w-3" />
                    Downlink
                  </Badge>
                </div>
                <div v-else>
                  <Badge variant="outline" class="border-dashed text-muted-foreground"> Available </Badge>
                </div>
              </TableCell>
              <TableCell>
                <div v-if="row.connection" class="flex items-center gap-3">
                  <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-primary/10 text-primary">
                    <Server class="h-4 w-4" />
                  </div>
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-foreground">
                      {{ row.is_parent ? row.connection.source?.name : row.connection.destination?.name }}
                    </span>
                    <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground">
                      <span class="mr-1">
                        {{ row.is_parent ? 'Source Port:' : 'Dest Port:' }}
                      </span>
                      <span class="rounded border border-border/50 bg-muted/60 px-1 font-mono">
                        {{
                          row.is_parent
                            ? (row.connection.source_interface?.name ?? row.connection.source_port)
                            : (row.connection.destination_interface?.name ?? row.connection.destination_port)
                        }}
                      </span>
                    </div>
                  </div>
                </div>
                <span v-else class="text-xs text-muted-foreground">-</span>
              </TableCell>
              <TableCell>
                <Badge v-if="row.connection" variant="outline" class="font-mono text-xs">
                  {{ row.connection.connection_type }}
                </Badge>
                <span v-else class="text-xs text-muted-foreground">-</span>
              </TableCell>
              <TableCell>
                <Button
                  v-if="row.connection && !row.is_parent"
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 text-destructive opacity-70 hover:bg-destructive/10 hover:opacity-100"
                  @click="deleteConnection(row.connection.id)">
                  <Trash2 class="h-4 w-4" />
                </Button>
                <Button
                  v-else-if="!row.connection"
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 text-primary opacity-70 hover:bg-primary/10 hover:opacity-100"
                  @click="openAdd(row.interface.id)">
                  <Plus class="h-4 w-4" />
                </Button>
              </TableCell>
            </TableRow>
            <TableRow v-if="portTableData.length === 0">
              <TableCell colspan="5" class="py-8 text-center text-muted-foreground">
                No interfaces found. Please add Physical Interfaces first.
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </CardContent>
  </Card>
</template>
