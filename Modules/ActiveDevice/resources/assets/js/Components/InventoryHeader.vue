<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Plus, Search } from 'lucide-vue-next';

defineProps<{
    title: string;
    description?: string;
    searchPlaceholder?: string;
    addButtonText: string;
    addRoute: string;
    modelValue?: string;
}>();

defineEmits(['update:modelValue']);
</script>

<template>
    <div
        class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
    >
        <div>
            <h1
                class="font-inter text-2xl font-bold tracking-tight text-slate-900"
            >
                {{ title }}
            </h1>
            <p v-if="description" class="text-sm text-muted-foreground italic">
                {{ description }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative hidden sm:block">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <Search class="h-4 w-4 text-gray-400" />
                </span>
                <input
                    type="text"
                    :value="modelValue"
                    @input="
                        $emit(
                            'update:modelValue',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                    class="block w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pr-3 pl-10 text-sm transition focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    :placeholder="searchPlaceholder || 'Search...'"
                />
            </div>
            <Button
                as-child
                class="bg-blue-600 shadow-sm transition-all duration-200 hover:bg-blue-700"
            >
                <Link :href="addRoute">
                    <Plus class="mr-2 h-4 w-4" />
                    {{ addButtonText }}
                </Link>
            </Button>
        </div>
    </div>
</template>

<style scoped>
.font-inter {
    font-family: 'Inter', sans-serif;
}
</style>
