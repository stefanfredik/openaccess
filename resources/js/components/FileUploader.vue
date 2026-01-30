<script setup lang="ts">
  import { UploadCloud, X } from 'lucide-vue-next'
  import { ref, useTemplateRef } from 'vue'
  import { cn } from '@/lib/utils'

  const props = defineProps<{
    modelValue: File | null
    accept?: string
    maxSize?: number // in MB
    description?: string
    error?: string
    class?: string
    initialImage?: string | null
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: File | null): void
    (e: 'change', value: File | null): void
  }>()

  const fileInput = useTemplateRef<HTMLInputElement>('file-input')
  const isDragging = ref(false)
  const previewUrl = ref<string | null>(props.initialImage || null)

  const handleDragOver = (e: DragEvent) => {
    e.preventDefault()
    isDragging.value = true
  }

  const handleDragLeave = (e: DragEvent) => {
    e.preventDefault()
    isDragging.value = false
  }

  const handleDrop = (e: DragEvent) => {
    e.preventDefault()
    isDragging.value = false

    const files = e.dataTransfer?.files
    if (files && files.length > 0) {
      validateAndEmit(files[0])
    }
  }

  const handleChange = (e: Event) => {
    const files = (e.target as HTMLInputElement).files
    if (files && files.length > 0) {
      validateAndEmit(files[0])
    }
  }

  const triggerBrowse = () => {
    fileInput.value?.click()
  }

  const validateAndEmit = (file: File) => {
    // Basic validation logic here if needed (size, type)
    if (props.maxSize && file.size > props.maxSize * 1024 * 1024) {
      alert(`File too large. Max size is ${props.maxSize}MB.`)
      return
    }

    emit('update:modelValue', file)
    emit('change', file)

    // Create preview if it's an image
    if (file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => {
        previewUrl.value = e.target?.result as string
      }
      reader.readAsDataURL(file)
    } else {
      previewUrl.value = null
    }
  }

  const removeFile = (e: Event) => {
    e.stopPropagation() // Prevent triggering browse
    emit('update:modelValue', null)
    emit('change', null)

    // If we have an initial image, revert to it. Otherwise clear.
    previewUrl.value = props.initialImage || null

    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
</script>

<template>
  <div :class="cn('w-full', props.class)">
    <div
      @dragover="handleDragOver"
      @dragleave="handleDragLeave"
      @drop="handleDrop"
      @click="triggerBrowse"
      :class="
        cn(
          'relative flex h-48 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed bg-white transition-colors duration-200 dark:bg-zinc-950',
          isDragging ? 'border-primary bg-primary/5' : 'border-emerald-400 hover:bg-emerald-50/10',
          error ? 'border-destructive' : '',
        )
      ">
      <input ref="file-input" type="file" class="hidden" :accept="accept" @change="handleChange" />

      <!-- Preview State -->
      <div v-if="previewUrl || modelValue" class="group relative flex h-full w-full items-center justify-center p-2">
        <img v-if="previewUrl" :src="previewUrl" class="h-full rounded-md object-contain" alt="Preview" />
        <div v-else class="flex flex-col items-center">
          <UploadCloud class="mb-2 h-10 w-10 text-emerald-500" />
          <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ modelValue?.name }}</span>
          <span class="text-xs text-gray-500" v-if="modelValue">({{ (modelValue.size / 1024).toFixed(1) }} KB)</span>
        </div>

        <button
          @click="removeFile"
          class="absolute top-2 right-2 rounded-full bg-destructive p-1 text-destructive-foreground opacity-0 transition-opacity group-hover:opacity-100"
          title="Remove file">
          <X class="h-4 w-4" />
        </button>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
        <div class="mb-4 text-emerald-500">
          <UploadCloud class="h-10 w-10" />
        </div>
        <p class="mb-2 text-sm text-gray-700 dark:text-gray-300">
          <span class="font-semibold text-emerald-500">Browse</span>
        </p>
        <p v-if="description" class="text-xs text-gray-500 dark:text-gray-400">
          {{ description }}
        </p>
        <p v-else class="text-xs text-gray-500 dark:text-gray-400">Format: PNG, JPG& Max size: {{ maxSize || 5 }} MB</p>
      </div>
    </div>
    <p v-if="error" class="mt-2 text-sm font-medium text-destructive">
      {{ error }}
    </p>
  </div>
</template>
