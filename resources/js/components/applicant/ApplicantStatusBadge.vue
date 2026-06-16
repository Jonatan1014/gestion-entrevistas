<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    status: string;
}>();

const variantClass = computed(() => {
    switch (props.status) {
        case 'registered':
            return 'bg-blue-100 text-blue-800 hover:bg-blue-100 dark:bg-blue-900 dark:text-blue-300';
        case 'in_interview':
            return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-300';
        case 'evaluated':
            return 'bg-purple-100 text-purple-800 hover:bg-purple-100 dark:bg-purple-900 dark:text-purple-300';
        case 'apt':
            return 'bg-green-100 text-green-800 hover:bg-green-100 dark:bg-green-900 dark:text-green-300';
        case 'no_apt':
            return 'bg-red-100 text-red-800 hover:bg-red-100 dark:bg-red-900 dark:text-red-300';
        default:
            return '';
    }
});

const label = computed(() => {
    const labels: Record<string, string> = {
        registered: 'Registrado',
        in_interview: 'En entrevista',
        evaluated: 'Evaluado',
        apt: 'Apto',
        no_apt: 'No apto',
    };
    return labels[props.status] ?? props.status;
});
</script>

<template>
    <Badge :class="variantClass" variant="outline">
        {{ label }}
    </Badge>
</template>
