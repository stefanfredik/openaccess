<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

defineProps<{
    status: string;
}>();
</script>

<template>
    <Badge
        :variant="
            status === 'Active' || status === 'up'
                ? 'default'
                : status === 'Planned'
                  ? 'secondary'
                  : 'destructive'
        "
        class="rounded-md px-2 py-0.5 text-[10px] font-bold tracking-tight capitalize"
    >
        {{ status }}
    </Badge>
</template>
