<script setup lang="ts">
  import { Eye, FileText, MoreVertical, Settings, Trash } from 'lucide-vue-next'
  import { Link } from '@inertiajs/vue3'
  import { Button } from '@/components/ui/button'
  import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
  import DeleteAction from '@/components/DeleteAction.vue'

  interface Props {
    detailRoute?: string
    editRoute?: string
    deleteRoute?: string
    deleteMessage?: string
    quickPreview?: boolean
  }

  const props = withDefaults(defineProps<Props>(), {
    deleteMessage: 'Hapus Perangkat',
    quickPreview: true,
  })

  // We emit an event for Quick Preview so the parent can handle the drawer/modal
  const emit = defineEmits(['preview'])
</script>

<template>
  <div class="flex items-center justify-end gap-1">
    <!-- Quick Preview Button -->
    <Button
      v-if="quickPreview"
      variant="ghost"
      size="icon"
      class="h-8 w-8 text-primary opacity-60 transition-opacity hover:bg-muted/50 hover:opacity-100"
      @click="emit('preview')"
      title="Pratinjau Cepat">
      <Eye class="h-4 w-4" />
    </Button>

    <!-- Dropdown Menu -->
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="ghost" size="icon" class="h-8 w-8 p-0 opacity-60 transition-opacity hover:opacity-100">
          <MoreVertical class="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" class="w-48">
        <!-- View Detail -->
        <DropdownMenuItem v-if="detailRoute" as-child class="cursor-pointer">
          <Link :href="detailRoute">
            <FileText class="mr-2 h-4 w-4" />
            Detail Lengkap
          </Link>
        </DropdownMenuItem>

        <!-- Edit -->
        <DropdownMenuItem v-if="editRoute" as-child class="cursor-pointer">
          <Link :href="editRoute">
            <Settings class="mr-2 h-4 w-4" />
            Edit Perangkat
          </Link>
        </DropdownMenuItem>

        <!-- Custom Actions Slot -->
        <slot name="custom-actions"></slot>

        <!-- Delete -->
        <template v-if="deleteRoute">
          <DropdownMenuSeparator />
          <div @click.stop class="w-full">
            <DeleteAction
              :href="deleteRoute"
              :title="deleteMessage">
              <template #trigger>
                <div class="relative flex w-full cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-destructive/10 hover:text-destructive focus:bg-destructive/10 focus:text-destructive">
                  <Trash class="mr-2 h-4 w-4" />
                  {{ deleteMessage }}
                </div>
              </template>
            </DeleteAction>
          </div>
        </template>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>
</template>
