<script setup lang="ts">
import { SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Moon, Sun } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const isDark = ref(false);

const toggleTheme = () => {
    isDark.value = !isDark.value;
    const html = document.documentElement;
    if (isDark.value) {
        html.classList.add('dark');
        html.classList.remove('light');
        localStorage.setItem('theme', 'dark');
    } else {
        html.classList.remove('dark');
        html.classList.add('light');
        localStorage.setItem('theme', 'light');
    }
};

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark');
});
</script>

<template>
    <SidebarMenuItem>
        <SidebarMenuButton class="cursor-pointer" @click="toggleTheme">
            <Sun v-if="isDark" class="size-4" />
            <Moon v-else class="size-4" />
            <span>{{ isDark ? 'Modo claro' : 'Modo oscuro' }}</span>
        </SidebarMenuButton>
    </SidebarMenuItem>
</template>
