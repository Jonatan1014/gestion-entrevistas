<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, MailCheck } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <AuthLayout title="Verificá tu email" description="Revisá tu casilla de correo y hacé clic en el enlace de verificación que te enviamos.">
        <Head title="Verificar email" />

        <div v-if="status === 'verification-link-sent'" role="status" aria-live="polite" class="mb-4 rounded-lg border border-[#51eead]/30 bg-[#51eead]/10 px-4 py-3 text-sm font-medium text-[#0d0d0d] dark:text-[#51eead]">
            Se envió un nuevo enlace de verificación a tu email.
        </div>

        <form @submit.prevent="submit" class="space-y-6 text-center">
            <Button :disabled="form.processing" variant="secondary" size="lg">
                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                <MailCheck v-else class="mr-2 h-4 w-4" />
                {{ form.processing ? 'Enviando…' : 'Reenviar email de verificación' }}
            </Button>

            <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm">
                Cerrar sesión
            </TextLink>
        </form>
    </AuthLayout>
</template>
