<script setup lang="ts">
  import AppLayout from '@/layouts/app/AppSidebarLayout.vue'
  import type { BreadcrumbItem } from '@/types'

  type Props = {
    breadcrumbs?: BreadcrumbItem[]
  }

  import { usePage } from '@inertiajs/vue3'
  import { watch } from 'vue'
  import { Toaster, toast } from 'vue-sonner'

  const page = usePage()

  withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
  })

  watch(
    () => page.props.flash,
    (flash: any) => {
      if (flash?.success) {
        toast.success(flash.success)
      }
      if (flash?.error) {
        toast.error(flash.error)
      }
    },
    { deep: true, immediate: true },
  )
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <slot />
    <Toaster position="top-right" :duration="5000" richColors />
  </AppLayout>
</template>
