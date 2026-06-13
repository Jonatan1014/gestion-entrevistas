<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    type: string;
}>();

const variantClass = computed(() => {
    switch (props.type) {
        case 'virtual':
            return 'bg-blue-100 text-blue-800 hover:bg-blue-100 dark:bg-blue-900 dark:text-blue-300';
        case 'presencial':
            return 'bg-purple-100 text-purple-800 hover:bg-purple-100 dark:bg-purple-900 dark:text-purple-300';
        default:
            return '';
    }
});

const label = computed(() => {
    return props.type.charAt(0).toUpperCase() + props.type.slice(1);
});
</script>

<template>
    <Badge :class="variantClass" variant="outline">
        {{ label }}
    </Badge>
</template>
