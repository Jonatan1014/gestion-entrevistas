<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, ShieldCheck } from 'lucide-vue-next';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthLayout title="Confirmar contraseña" description="Esta es un área segura. Confirmá tu contraseña para continuar.">
        <Head title="Confirmar contraseña" />

        <form @submit.prevent="submit" class="flex flex-col gap-6" novalidate>
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="password">Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        v-model="form.password"
                        autofocus
                        placeholder="••••••••"
                        :aria-invalid="!!form.errors.password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing" size="lg">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    <ShieldCheck v-else class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Confirmando…' : 'Confirmar' }}
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
