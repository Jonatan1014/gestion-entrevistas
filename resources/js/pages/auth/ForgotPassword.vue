<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Mail } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout title="Recuperar contraseña" description="Ingresá tu email y te enviaremos un enlace para restablecerla">
        <Head title="Recuperar contraseña" />

        <div v-if="status" role="status" aria-live="polite" class="mb-4 rounded-lg border border-[#51eead]/30 bg-[#51eead]/10 px-4 py-3 text-sm font-medium text-[#0d0d0d] dark:text-[#51eead]">
            {{ status }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit" novalidate>
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autocomplete="email"
                        v-model="form.email"
                        autofocus
                        placeholder="email@ejemplo.com"
                        :aria-invalid="!!form.errors.email"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button class="w-full" :disabled="form.processing" size="lg">
                        <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Mail v-else class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Enviando…' : 'Enviar enlace de recuperación' }}
                    </Button>
                </div>
            </form>

            <div class="text-center text-sm text-muted-foreground">
                <span>¿Recordaste tu contraseña?</span>
                <TextLink :href="route('login')" class="ml-1 font-medium">Volver al inicio</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
