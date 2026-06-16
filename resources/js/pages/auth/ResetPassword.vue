<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Lock } from 'lucide-vue-next';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout title="Restablecer contraseña" description="Elegí una nueva contraseña para tu cuenta">
        <Head title="Restablecer contraseña" />

        <form @submit.prevent="submit" class="flex flex-col gap-6" novalidate>
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        autocomplete="email"
                        v-model="form.email"
                        readonly
                        class="bg-muted"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Nueva contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        v-model="form.password"
                        autofocus
                        placeholder="Mínimo 8 caracteres"
                        :aria-invalid="!!form.errors.password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmar contraseña</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Repetí tu contraseña"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing" size="lg">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    <Lock v-else class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Restableciendo…' : 'Restablecer contraseña' }}
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
