<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useToast } from '@/composables/useToast';
import { Toaster } from 'vue-sonner';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { success, error, info } = useToast();
const page = usePage();

watch(
    () => page.props.flash,
    (flash: any) => {
        if (!flash) return;
        if (flash.success) success(flash.success);
        if (flash.error) error(flash.error);
        if (flash.info) info(flash.info);
    },
    { immediate: true, deep: true }
);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
    <Toaster position="bottom-right" richColors closeButton />
</template>
