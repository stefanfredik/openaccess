<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Eye } from 'lucide-vue-next';

defineProps<{
    href: string;
    title?: string;
}>();
</script>

<template>
    <Button variant="ghost" size="icon" as-child :title="title || 'Detail'">
        <Link :href="href">
            <Eye class="h-4 w-4" />
        </Link>
    </Button>
</template>
