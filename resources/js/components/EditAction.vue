<script setup lang="ts">
  import { Link } from '@inertiajs/vue3'
  import { Pencil } from 'lucide-vue-next'
  import { Button } from '@/components/ui/button'

  defineProps<{
    href: string
    title?: string
  }>()
</script>

<template>
  <Button variant="ghost" size="icon" as-child :title="title || 'Edit'">
    <Link :href="href">
      <Pencil class="h-4 w-4" />
    </Link>
  </Button>
</template>
