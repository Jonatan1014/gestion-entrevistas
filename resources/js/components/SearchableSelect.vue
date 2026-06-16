<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Search, ChevronDown } from 'lucide-vue-next';
import { onClickOutside } from '@vueuse/core';
import { ref, computed } from 'vue';

interface Option {
    id: number;
    name: string;
    sub?: string;
}

const props = defineProps<{
    options: Option[];
    modelValue: string;
    placeholder?: string;
    label?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const open = ref(false);
const search = ref('');
const dropdownRef = ref<HTMLElement>();

onClickOutside(dropdownRef, () => {
    open.value = false;
    search.value = '';
});

const filteredOptions = computed(() => {
    if (!search.value) return props.options;
    const q = search.value.toLowerCase();
    return props.options.filter(
        (o) => o.name.toLowerCase().includes(q) || (o.sub?.toLowerCase().includes(q))
    );
});

const selectedOption = computed(() => {
    return props.options.find((o) => String(o.id) === props.modelValue);
});

const select = (option: Option) => {
    emit('update:modelValue', String(option.id));
    open.value = false;
    search.value = '';
};

const toggle = () => {
    open.value = !open.value;
    if (open.value) {
        search.value = '';
    }
};
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <button
            type="button"
            class="flex w-full items-center justify-between rounded-lg border border-input bg-transparent px-4 py-3 text-left text-sm transition-colors hover:border-[#51eead] hover:bg-[#51eead]/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#51eead]"
            @click="toggle"
        >
            <span v-if="selectedOption" class="font-medium truncate">
                {{ selectedOption.name }}
                <span v-if="selectedOption.sub" class="ml-1 text-xs text-muted-foreground">— {{ selectedOption.sub }}</span>
            </span>
            <span v-else class="text-muted-foreground">{{ placeholder || 'Seleccionar...' }}</span>
            <ChevronDown class="ml-2 h-4 w-4 shrink-0 text-muted-foreground transition-transform" :class="{ 'rotate-180': open }" />
        </button>

        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 -translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 -translate-y-1 scale-95"
        >
            <div
                v-if="open"
                class="absolute z-50 mt-1 w-full overflow-hidden rounded-lg border border-input bg-background shadow-lg"
            >
                <div class="flex items-center border-b px-3 py-2">
                    <Search class="mr-2 h-4 w-4 shrink-0 text-muted-foreground" />
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Buscar..."
                        class="h-8 border-0 bg-transparent p-0 text-sm shadow-none focus-visible:ring-0"
                        @keydown.escape="open = false; search = ''"
                    />
                </div>
                <div class="max-h-48 overflow-y-auto p-1">
                    <button
                        v-for="option in filteredOptions"
                        :key="option.id"
                        type="button"
                        class="flex w-full items-center rounded-md px-3 py-2 text-left text-sm transition-colors hover:bg-[#51eead]/10"
                        :class="{ 'bg-[#51eead]/10 text-[#51eead]': String(option.id) === modelValue }"
                        @click="select(option)"
                    >
                        <span class="font-medium">{{ option.name }}</span>
                        <span v-if="option.sub" class="ml-2 text-xs text-muted-foreground truncate">{{ option.sub }}</span>
                    </button>
                    <div v-if="filteredOptions.length === 0" class="px-3 py-4 text-center text-sm text-muted-foreground">
                        Sin resultados.
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
