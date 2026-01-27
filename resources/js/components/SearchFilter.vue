<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Search, X, Filter } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
    route: string;
    filters: {
        search?: string;
        type?: string;
        [key: string]: any;
    };
    placeholder?: string;
    showTypeFilter?: boolean;
    typeOptions?: Array<{ label: string; value: string }>;
}>();

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || 'all');

const updateFilters = debounce(() => {
    router.get(
        props.route,
        {
            search: search.value || undefined,
            type: type.value === 'all' ? undefined : type.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
}, 300);

watch(search, () => {
    updateFilters();
});

const clearFilters = () => {
    search.value = '';
    type.value = 'all';
    updateFilters();
};

const handleTypeChange = (val: any) => {
    type.value = String(val);
    updateFilters();
};
</script>

<template>
    <div class="flex flex-col gap-4 md:flex-row md:items-center justify-between w-full">
        <div class="flex-1 flex items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                    v-model="search"
                    :placeholder="placeholder || 'Cari data...'"
                    class="pl-10 h-10 bg-background border-border/50 focus:ring-1 focus:ring-primary transition-all"
                />
                <button
                    v-if="search"
                    @click="search = ''"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
            
            <Select v-if="showTypeFilter" :model-value="type" @update:model-value="handleTypeChange">
                <SelectTrigger class="w-fit min-w-[140px] h-10 bg-background border-border/50">
                    <div class="flex items-center gap-2 pr-2">
                        <div class="p-1 bg-muted rounded">
                            <Filter class="h-3.5 w-3.5 text-foreground" />
                        </div>
                        <SelectValue placeholder="Filter Tipe" />
                    </div>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Semua Tipe</SelectItem>
                    <SelectItem v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </SelectItem>
                </SelectContent>
            </Select>

            <Button
                v-if="search || (type !== 'all' && showTypeFilter)"
                variant="ghost"
                size="sm"
                @click="clearFilters"
                class="text-xs h-10 px-3 font-semibold text-destructive hover:bg-destructive/5"
            >
                Reset
            </Button>
        </div>

        <div class="flex items-center gap-2">
            <slot name="actions"></slot>
        </div>
    </div>
</template>
