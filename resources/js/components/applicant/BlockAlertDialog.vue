<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

defineProps<{
    open: boolean;
    blockReason: string;
}>();

defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Blocked Applicant</DialogTitle>
                <DialogDescription>
                    This applicant is blocked and cannot be assigned to a vacancy.
                </DialogDescription>
            </DialogHeader>

            <div class="py-4">
                <p class="text-sm font-medium text-muted-foreground">Block reason:</p>
                <p class="text-sm">{{ blockReason }}</p>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="$emit('update:open', false)">Close</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
