<script setup lang="ts">
  import { onMounted, ref } from 'vue'

  const mapContainer = ref<HTMLElement | null>(null)
  const status = ref<string>('Initializing...')
  const errorMsg = ref<string | null>(null)
  const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY

  // Add global error listener for Google Maps authentication failures
  if (typeof window !== 'undefined') {
    ;(window as any).gm_auth_failure = () => {
      status.value = 'Auth Failed'
      errorMsg.value =
        'Google Maps authentication failed. This usually means: \n1. Your API Key is invalid or restricted incorrectly. \n2. Billing is not enabled on your Google Cloud Project. \n3. Maps JavaScript API is not enabled in the Cloud Console.'
    }
  }

  onMounted(() => {
    if (!apiKey) {
      status.value = 'Failed'
      errorMsg.value = 'VITE_GOOGLE_MAPS_API_KEY is missing in .env file.'
      return
    }

    // Manual script injection to match googlemaps.html
    const script = document.createElement('script')
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=initMapDebug&loading=async`
    script.async = true
    script.defer = true

    script.onerror = () => {
      status.value = 'Failed'
      errorMsg.value = 'Gagal memuat script Google Maps.'
    }

    document.head.appendChild(script)
    ;(window as any).initMapDebug = () => {
      try {
        if (mapContainer.value) {
          new (window as any).google.maps.Map(mapContainer.value, {
            center: { lat: -6.2088, lng: 106.8456 },
            zoom: 12,
          })
          status.value = 'Success: Map Loaded! (Manual Script)'
        }
      } catch (e: any) {
        status.value = 'Failed'
        errorMsg.value = e.message || 'Unknown error occurred.'
      }
    }
  })
</script>

<template>
  <div class="p-10 space-y-5">
    <h1 class="text-2xl font-bold">Google Maps API Test (Manual Sync)</h1>

    <div
      class="p-4 rounded border"
      :class="{
        'bg-green-100 border-green-400': status.includes('Success'),
        'bg-red-100 border-red-400': status === 'Failed' || status === 'Auth Failed',
        'bg-gray-100': status === 'Initializing...',
      }">
      <p class="font-bold">Status: {{ status }}</p>
      <p v-if="errorMsg" class="text-red-600 mt-2 whitespace-pre-line">{{ errorMsg }}</p>
      <div class="mt-4 p-2 bg-black/5 rounded font-mono text-xs break-all">
        <strong>Current API Key in Code:</strong><br />
        {{ apiKey || 'NOT FOUND' }}
      </div>
    </div>

    <div ref="mapContainer" class="w-full h-[500px] bg-gray-200 rounded border border-gray-400"></div>

    <div class="mt-8 p-6 bg-slate-50 rounded-xl border border-slate-200">
      <h2 class="text-lg font-bold mb-4">Diagnostic Checklist (Google Cloud Console)</h2>
      <ul class="space-y-3 text-sm text-slate-700">
        <li class="flex items-start gap-2">
          <span class="font-bold text-blue-600">1.</span>
          <span
            >Buka
            <a
              href="https://console.cloud.google.com/google/maps-apis/api/maps-backend.googleapis.com"
              target="_blank"
              class="text-blue-600 underline"
              >Maps JavaScript API</a
            >
            dan pastikan statusnya <strong>"Enabled"</strong>.</span
          >
        </li>
        <li class="flex items-start gap-2">
          <span class="font-bold text-blue-600">2.</span>
          <span
            >Buka menu <a href="https://console.cloud.google.com/billing" target="_blank" class="text-blue-600 underline">Billing</a> dan pastikan
            Project Anda sudah terhubung ke <strong>Billing Account</strong> yang aktif.</span
          >
        </li>
        <li class="flex items-start gap-2">
          <span class="font-bold text-blue-600">3.</span>
          <span
            >Di menu <strong>Credentials</strong>, coba hapus semua <strong>"Website restrictions"</strong> untuk sementara untuk memastikan bukan
            masalah format URL.</span
          >
        </li>
      </ul>
    </div>
  </div>
</template>
