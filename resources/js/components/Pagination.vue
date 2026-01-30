<script setup lang="ts">
  import { Link } from '@inertiajs/vue3'
  import type { PaginationLinks } from '@/types'
  import { Button } from '@/components/ui/button'
  import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

  defineProps<{
    links: PaginationLinks[]
  }>()

  const cleanLabel = (label: string) => {
    return label.replace('&laquo; ', '').replace(' &raquo;', '')
  }
</script>

<template>
  <div v-if="links.length > 3" class="flex items-center justify-between px-4 py-3 sm:px-6">
    <div class="flex flex-1 justify-between sm:hidden">
      <Button
        variant="outline"
        size="sm"
        as-child
        :disabled="!links[0].url">
        <Link v-if="links[0].url" :href="links[0].url"> Previous </Link>
        <span v-else>Previous</span>
      </Button>
      <Button
        variant="outline"
        size="sm"
        as-child
        :disabled="!links[links.length - 1].url">
        <Link v-if="links[links.length - 1].url" :href="links[links.length - 1].url"> Next </Link>
        <span v-else>Next</span>
      </Button>
    </div>
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-center">
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-xs" aria-label="Pagination">
          <template v-for="(link, key) in links" :key="key">
            <!-- First and Last (Previous/Next) -->
            <Link
              v-if="key === 0 || key === links.length - 1"
              :href="link.url || '#'"
              class="relative inline-flex items-center px-2 py-2 text-muted-foreground ring-1 ring-inset ring-border hover:bg-muted focus:z-20 focus:outline-offset-0 first:rounded-l-md last:rounded-r-md"
              :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': !link.url }">
              <span class="sr-only">{{ link.label }}</span>
              <ChevronLeft v-if="key === 0" class="h-5 w-5" aria-hidden="true" />
              <ChevronRight v-else class="h-5 w-5" aria-hidden="true" />
            </Link>

            <!-- Numbered links -->
            <Link
              v-else
              :href="link.url || '#'"
              aria-current="page"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-border focus:z-20 focus:outline-offset-0"
              :class="[
                link.active
                  ? 'z-10 bg-primary text-primary-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary'
                  : 'text-foreground hover:bg-muted',
                !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : '',
              ]">
              {{ cleanLabel(link.label) }}
            </Link>
          </template>
        </nav>
      </div>
    </div>
  </div>
</template>
