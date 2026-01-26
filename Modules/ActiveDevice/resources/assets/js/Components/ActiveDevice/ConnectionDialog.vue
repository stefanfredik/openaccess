<script setup lang="ts">
import { ref, watch } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Loader2 } from 'lucide-vue-next';

const props = defineProps<{
    open: boolean;
    sourceNode: any;
    targetNode: any;
    processing?: boolean;
}>();

const emit = defineEmits(['update:open', 'save']);

const form = ref({
    connection_type: 'Fiber',
    source_port: '',
    destination_port: '',
    description: '',
});

watch(() => props.open, (newVal) => {
    if (newVal) {
        form.value = {
            connection_type: 'Fiber',
            source_port: '',
            destination_port: '',
            description: '',
        };
    }
});

const handleSave = () => {
    emit('save', {
        ...form.value,
        source_id: props.sourceNode.id,
        source_type: props.sourceNode.type, // Make sure this is the full class name
        destination_id: props.targetNode.id,
        destination_type: props.targetNode.type,
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-[425px] bg-[#0a0b0f] border border-white/10 text-white">
            <DialogHeader>
                <DialogTitle>Create Connection</DialogTitle>
                <DialogDescription class="text-white/60">
                    Connect <span class="text-white font-bold">{{ sourceNode?.name }}</span> to <span class="text-white font-bold">{{ targetNode?.name }}</span>.
                </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label>Connection Type</Label>
                    <Select v-model="form.connection_type">
                        <SelectTrigger class="bg-white/5 border-white/10 text-white">
                            <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent class="bg-[#1a1b26] border-white/10 text-white">
                            <SelectItem value="Fiber">Fiber</SelectItem>
                            <SelectItem value="Uplink">Uplink (Ethernet)</SelectItem>
                            <SelectItem value="Downlink">Downlink (Ethernet)</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label>Source Port</Label>
                        <Input v-model="form.source_port" placeholder="e.g. ether1" class="bg-white/5 border-white/10 text-white" />
                        <p class="text-[10px] text-white/40">On {{ sourceNode?.name }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label>Dest. Port</Label>
                        <Input v-model="form.destination_port" placeholder="e.g. ether1" class="bg-white/5 border-white/10 text-white" />
                        <p class="text-[10px] text-white/40">On {{ targetNode?.name }}</p>
                    </div>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="$emit('update:open', false)" class="border-white/10 text-white hover:bg-white/10 hover:text-white">
                    Cancel
                </Button>
                <Button @click="handleSave" :disabled="processing" class="bg-primary hover:bg-primary/90 text-white">
                    <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                    Connect Devices
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
