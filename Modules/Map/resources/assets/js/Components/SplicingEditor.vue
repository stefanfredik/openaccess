<script setup>
  import { ref, onMounted, computed } from 'vue'
  import { router } from '@inertiajs/vue3'
  import axios from 'axios'
  import { toast } from 'vue-sonner'

  const props = defineProps({ enclosureType: String, enclosureId: Number, isOpen: Boolean })
  const emit = defineEmits(['close'])

  const loading = ref(false)
  const enclosure = ref(null)
  const cables = ref([])

  // Staged Cables for Splicing (Left Side vs Right Side)
  const leftCableId = ref(null)
  const rightCableId = ref(null)

  const leftCable = computed(() => cables.value.find((c) => c.id === leftCableId.value))
  const rightCable = computed(() => cables.value.find((c) => c.id === rightCableId.value))

  // Fetch Data
  const fetchData = async () => {
    loading.value = true
    try {
      const response = await axios.get(route('passive-device.enclosure-details'), {
        params: { enclosure_type: props.enclosureType, enclosure_id: props.enclosureId },
      })
      enclosure.value = response.data.enclosure
      cables.value = response.data.cables
    } catch (error) {
      console.error('Failed to load splicing data', error)
      toast.error('Failed to load enclosure details')
    } finally {
      loading.value = false
    }
  }

  // Drag and Drop Logic
  const draggedCore = ref(null)

  const onDragStart = (event, core, side) => {
    draggedCore.value = { ...core, side }
    event.dataTransfer.effectAllowed = 'link'
  }

  const onDrop = async (event, targetCore, side) => {
    if (!draggedCore.value) return
    if (draggedCore.value.side === side) return // Can't splice to same side (logic choice)

    const coreA = draggedCore.value
    const coreB = targetCore

    // Call API to splice
    try {
      // Prepare splice request (assuming many-to-many or simple link)
      await axios.post(route('passive-device.splice'), {
        incoming_core_id: coreA.id,
        outgoing_core_id: coreB.id,
        enclosure_type: props.enclosureType,
        enclosure_id: props.enclosureId,
      })
      toast.success('Splice Created!')
      fetchData() // Refresh
    } catch (e) {
      console.error(e)
      toast.error('Splice Failed')
    }
    draggedCore.value = null
  }

  onMounted(() => {
    if (props.isOpen && props.enclosureId) {
      fetchData()
    }
  })

  // Utilities
  const getStatusColor = (status, color) => {
    if (status === 'Used') return 'opacity-50'
    return `bg-${color.toLowerCase()}-500`
  }
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-900 w-[90vw] h-[85vh] rounded-xl shadow-2xl flex flex-col overflow-hidden">
      <!-- Header -->
      <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800">
        <div>
          <h2 class="text-xl font-bold">Splicing Editor</h2>
          <p class="text-sm text-gray-500" v-if="enclosure">{{ enclosure.name }} ({{ enclosure.code }})</p>
        </div>
        <button @click="emit('close')" class="p-2 hover:bg-gray-200 rounded-full">✕</button>
      </div>

      <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar: Cable Selector -->
        <div class="w-64 border-r border-gray-200 dark:border-gray-700 p-4 overflow-y-auto bg-gray-50 dark:bg-gray-800">
          <h3 class="font-semibold mb-4">Connected Cables</h3>
          <div v-if="loading">Loading...</div>
          <div v-else class="space-y-2">
            <div v-for="cable in cables" :key="cable.id" class="p-3 bg-white dark:bg-gray-700 rounded shadow-sm">
              <div class="font-medium truncate" :title="cable.name">{{ cable.name }}</div>
              <div class="text-xs text-gray-500">{{ cable.core_count }} Cores</div>
              <div class="flex gap-2 mt-2">
                <button
                  @click="leftCableId = cable.id"
                  :class="{ 'bg-blue-600 text-white': leftCableId === cable.id }"
                  class="text-xs px-2 py-1 border rounded w-full">
                  Left
                </button>
                <button
                  @click="rightCableId = cable.id"
                  :class="{ 'bg-blue-600 text-white': rightCableId === cable.id }"
                  class="text-xs px-2 py-1 border rounded w-full">
                  Right
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Workspace: Splicing Matrix -->
        <div class="flex-1 flex bg-gray-100 dark:bg-gray-900 overflow-hidden relative">
          <!-- Left Panel -->
          <div class="flex-1 overflow-y-auto p-4 border-r border-gray-300 dark:border-gray-700">
            <h4 class="font-bold mb-4 text-center">Left Side</h4>
            <div v-if="leftCable">
              <h5 class="font-semibold text-blue-600 mb-2">{{ leftCable.name }}</h5>
              <template v-for="tube in leftCable.tubes" :key="tube.id">
                <div class="mb-2 border rounded bg-white dark:bg-gray-800">
                  <div class="px-2 py-1 text-xs font-bold uppercase" :style="{ color: tube.color }">
                    Tube {{ tube.tube_number }} ({{ tube.color }})
                  </div>
                  <div
                    v-for="core in tube.cores"
                    :key="core.id"
                    class="flex items-center p-2 border-t hover:bg-gray-50 cursor-grab active:cursor-grabbing"
                    draggable="true"
                    @dragstart="onDragStart($event, core, 'left')"
                    @drop="onDrop($event, core, 'left')"
                    @dragover.prevent>
                    <div class="w-3 h-3 rounded-full mr-2 border border-gray-300" :style="{ backgroundColor: core.color }"></div>
                    <span class="text-xs flex-1">Core {{ core.core_number }}</span>
                    <!-- Connection Indicator -->
                    <span v-if="core.outgoing_splices || core.incoming_splices" class="text-xs text-green-600 font-bold">●</span>
                  </div>
                </div>
              </template>
            </div>
            <div v-else class="text-center text-gray-400 mt-20">Select a cable on the left</div>
          </div>

          <!-- Center Connector (Visual Only for now) -->
          <div class="w-12 bg-gray-200 dark:bg-gray-800 flex flex-col items-center justify-center">
            <div class="rotate-90 text-gray-400">SPLICE</div>
          </div>

          <!-- Right Panel -->
          <div class="flex-1 overflow-y-auto p-4">
            <h4 class="font-bold mb-4 text-center">Right Side</h4>
            <div v-if="rightCable">
              <h5 class="font-semibold text-blue-600 mb-2">{{ rightCable.name }}</h5>
              <template v-for="tube in rightCable.tubes" :key="tube.id">
                <div class="mb-2 border rounded bg-white dark:bg-gray-800">
                  <div class="px-2 py-1 text-xs font-bold uppercase" :style="{ color: tube.color }">
                    Tube {{ tube.tube_number }} ({{ tube.color }})
                  </div>
                  <div
                    v-for="core in tube.cores"
                    :key="core.id"
                    class="flex items-center p-2 border-t hover:bg-gray-50 cursor-grab active:cursor-grabbing"
                    draggable="true"
                    @dragstart="onDragStart($event, core, 'right')"
                    @drop="onDrop($event, core, 'right')"
                    @dragover.prevent>
                    <!-- Inverse order for standard "Mirror" feel -->
                    <span class="text-xs flex-1 text-right">Core {{ core.core_number }}</span>
                    <div class="w-3 h-3 rounded-full ml-2 border border-gray-300" :style="{ backgroundColor: core.color }"></div>
                    <span v-if="core.outgoing_splices || core.incoming_splices" class="text-xs text-green-600 font-bold ml-1">●</span>
                  </div>
                </div>
              </template>
            </div>
            <div v-else class="text-center text-gray-400 mt-20">Select a cable on the right</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
