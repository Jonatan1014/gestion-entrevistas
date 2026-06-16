<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    status: string;
}>();

const variantClass = computed(() => {
    switch (props.status) {
        case 'open':
            return 'bg-green-100 text-green-800 hover:bg-green-100 dark:bg-green-900 dark:text-green-300';
        case 'closed':
            return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-300';
        case 'cancelled':
            return 'bg-red-100 text-red-800 hover:bg-red-100 dark:bg-red-900 dark:text-red-300';
        default:
            return '';
    }
});

const label = computed(() => {
    const labels: Record<string, string> = {
        open: 'Abierta',
        closed: 'Cerrada',
        cancelled: 'Cancelada',
    };
    return labels[props.status] ?? props.status;
});
</script>

<template>
    <Badge :class="variantClass" variant="outline">
        {{ label }}
    </Badge>
</template>
