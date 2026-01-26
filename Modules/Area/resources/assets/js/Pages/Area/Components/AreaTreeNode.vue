<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ChevronRight, ChevronDown, Pencil, Trash2, MapPin } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { edit as areaEdit, destroy as areaDestroy } from '@/routes/area';

const props = defineProps<{
    area: any;
}>();

const isOpen = ref(false);
const hasChildren = props.area.children && props.area.children.length > 0;

const toggle = () => {
    if (hasChildren) {
        isOpen.value = !isOpen.value;
    }
};

const deleteArea = () => {
    if (confirm('Are you sure you want to delete this area?')) {
        router.delete(areaDestroy({ area: props.area.id }).url);
    }
};
</script>

<template>
    <div class="ml-4 border-l pl-4 relative">
        <div class="flex items-center gap-2 py-1 group">
            <button 
                v-if="hasChildren" 
                @click="toggle" 
                class="p-1 hover:bg-muted rounded-md"
            >
                <component :is="isOpen ? ChevronDown : ChevronRight" class="h-4 w-4" />
            </button>
            <div v-else class="w-6"></div>

            <div class="flex flex-1 items-center justify-between rounded-md border p-2 hover:bg-muted/50 transition-colors">
                <div class="flex items-center gap-2">
                    <MapPin class="h-4 w-4 text-muted-foreground" />
                    <span class="font-medium">{{ area.name }}</span>
                    <Badge variant="outline" class="text-xs">{{ area.type }}</Badge>
                </div>
                
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                        <Link :href="areaEdit({ area: area.id }).url">
                            <Pencil class="h-4 w-4" />
                        </Link>
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-destructive" @click="deleteArea">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>

        <div v-if="isOpen && hasChildren" class="mt-1">
            <AreaTreeNode 
                v-for="child in area.children" 
                :key="child.id" 
                :area="child" 
            />
        </div>
    </div>
</template>
