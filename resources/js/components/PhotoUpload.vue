<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { X } from 'lucide-vue-next';

const emit = defineEmits(['update:modelValue']);

const previews = ref<{ url: string, file: File }[]>([]);

const onFileChange = (e: Event) => {
    const input = e.target as HTMLInputElement;
    if (input.files) {
        const newFiles = Array.from(input.files);
        
        newFiles.forEach(file => {
            const url = URL.createObjectURL(file);
            previews.value.push({ url, file });
        });

        // Emit all files
        emit('update:modelValue', previews.value.map(p => p.file));
    }
};

const removePhoto = (index: number) => {
    previews.value.splice(index, 1);
    emit('update:modelValue', previews.value.map(p => p.file));
};
</script>

<template>
    <div class="space-y-4">
        <Input 
            type="file" 
            multiple 
            accept="image/*" 
            @change="onFileChange" 
            class="cursor-pointer"
        />

        <div v-if="previews.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="(photo, index) in previews" :key="index" class="relative group aspect-square rounded-md overflow-hidden border">
                <img :src="photo.url" class="object-cover w-full h-full" />
                <Button 
                    variant="destructive" 
                    size="icon" 
                    class="absolute top-1 right-1 h-6 w-6 opacity-0 group-hover:opacity-100 transition-opacity"
                    @click.prevent="removePhoto(index)"
                >
                    <X class="h-3 w-3" />
                </Button>
            </div>
        </div>
    </div>
</template>
