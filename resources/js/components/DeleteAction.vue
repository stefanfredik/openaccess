<script setup lang="ts">
  import { router } from '@inertiajs/vue3'
  import { Trash2 } from 'lucide-vue-next'
  import { ref } from 'vue'
  import { Button } from '@/components/ui/button'
  import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
  } from '@/components/ui/dialog'

  const props = defineProps<{
    href: string
    title?: string
    description?: string
  }>()

  const isLoading = ref(false)
  const isOpen = ref(false)

  const handleDelete = () => {
    router.delete(props.href, {
      onBefore: () => {
        isLoading.value = true
      },
      onSuccess: () => {
        isOpen.value = false
      },
      onFinish: () => {
        isLoading.value = false
      },
    })
  }
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <slot name="trigger">
        <Button variant="ghost" size="icon" title="Delete">
          <Trash2 class="h-4 w-4 text-destructive" />
        </Button>
      </slot>
    </DialogTrigger>
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ title || 'Are you absolutely sure?' }}</DialogTitle>
        <DialogDescription>
          {{ description || 'This action cannot be undone. This will permanently delete the resource from our servers.' }}
        </DialogDescription>
      </DialogHeader>
      <DialogFooter class="gap-2">
        <DialogClose as-child>
          <Button variant="secondary" :disabled="isLoading"> Cancel </Button>
        </DialogClose>
        <Button variant="destructive" @click="handleDelete" :disabled="isLoading">
          {{ isLoading ? 'Deleting...' : 'Delete' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
