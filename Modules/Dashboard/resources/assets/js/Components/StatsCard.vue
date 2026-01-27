<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type LucideIcon } from 'lucide-vue-next';

defineProps<{
    title: string;
    value: string | number;
    description?: string;
    icon?: any;
    variant?: 'default' | 'primary' | 'success' | 'warning' | 'info';
}>();

const variantClasses = {
    default: 'border-border bg-card',
    primary: 'border-primary/20 bg-primary/5 text-primary',
    success: 'border-emerald-500/20 bg-emerald-500/5 text-emerald-600',
    warning: 'border-amber-500/20 bg-amber-500/5 text-amber-600',
    info: 'border-blue-500/20 bg-blue-500/5 text-blue-600',
};

const iconClasses = {
    default: 'text-muted-foreground',
    primary: 'text-primary',
    success: 'text-emerald-500',
    warning: 'text-amber-500',
    info: 'text-blue-500',
};
</script>

<template>
    <Card 
        class="transition-all duration-300 hover:shadow-lg hover:-translate-y-1 overflow-hidden relative group"
        :class="variantClasses[variant || 'default']"
    >
        <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
             <component :is="icon" v-if="icon" class="h-12 w-12" />
        </div>
        
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium tracking-wide uppercase opacity-70">
                {{ title }}
            </CardTitle>
            <component 
                :is="icon" 
                v-if="icon" 
                class="h-4 w-4 transition-transform group-hover:scale-110" 
                :class="iconClasses[variant || 'default']" 
            />
        </CardHeader>
        <CardContent>
            <div class="text-3xl font-bold tracking-tight mb-1">{{ value }}</div>
            <p v-if="description" class="text-xs font-medium opacity-60">
                {{ description }}
            </p>
        </CardContent>
    </Card>
</template>
