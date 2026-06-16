<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Users, ClipboardCheck, TrendingUp } from 'lucide-vue-next';
import { watch } from 'vue';
import { useToast } from '@/composables/useToast';
import { Toaster } from 'vue-sonner';

defineProps<{
    title?: string;
    description?: string;
}>();

const features = [
    { icon: Users, label: 'Gestión de postulantes' },
    { icon: ClipboardCheck, label: 'Pruebas y evaluación' },
    { icon: TrendingUp, label: 'Reportes en tiempo real' },
];

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
    <div class="grid min-h-svh lg:grid-cols-2">
        <!-- Brand Panel — Black with green accent -->
        <div class="relative hidden flex-col justify-between bg-[#0d0d0d] p-10 text-white lg:flex">
            <!-- Gradient accent -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(81,238,173,0.12),transparent_70%)]" />

            <div class="relative z-10">
                <Link :href="route('home')" class="flex items-center gap-3 font-medium">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-[#51eead]/10 ring-1 ring-[#51eead]/20">
                        <AppLogoIcon class="size-6 fill-current text-[#51eead]" />
                    </div>
                    <span class="text-lg font-semibold tracking-tight">Sistema de Selección</span>
                </Link>
            </div>

            <div class="relative z-10 space-y-4">
                <blockquote class="space-y-2">
                    <p class="text-lg leading-relaxed text-white/70">
                        "El talento adecuado transforma organizaciones. Nuestra plataforma te ayuda a encontrarlo con precisión y transparencia."
                    </p>
                </blockquote>

                <div class="flex gap-6 pt-6">
                    <div v-for="feature in features" :key="feature.label" class="flex items-center gap-2 text-sm text-white/50">
                        <component :is="feature.icon" class="size-4 text-[#51eead]" />
                        <span>{{ feature.label }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="flex flex-col items-center justify-center bg-background p-6 md:p-10">
            <!-- Mobile logo -->
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <div class="flex size-10 items-center justify-center rounded-xl bg-[#51eead]/10 ring-1 ring-[#51eead]/20">
                    <AppLogoIcon class="size-6 fill-current text-[#0d0d0d] dark:text-[#51eead]" />
                </div>
                <span class="text-lg font-semibold tracking-tight">Sistema de Selección</span>
            </div>

            <div class="w-full max-w-sm">
                <div class="flex flex-col gap-8">
                    <div class="space-y-2 text-center lg:text-left">
                        <h1 class="text-2xl font-semibold tracking-tight">{{ title }}</h1>
                        <p class="text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
    <Toaster position="bottom-right" richColors closeButton />
</template>
