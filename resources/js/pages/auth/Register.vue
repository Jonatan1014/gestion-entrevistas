<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, UserPlus } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Crear cuenta" description="Completá tus datos para registrarte en el sistema">
        <Head title="Registrarse" />

        <form @submit.prevent="submit" class="flex flex-col gap-6" novalidate>
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="name">Nombre completo</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        v-model="form.name"
                        placeholder="Tu nombre"
                        :aria-invalid="!!form.errors.name"
                        :aria-describedby="form.errors.name ? 'name-error' : undefined"
                    />
                    <InputError id="name-error" :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@ejemplo.com"
                        :aria-invalid="!!form.errors.email"
                        :aria-describedby="form.errors.email ? 'email-error' : undefined"
                    />
                    <InputError id="email-error" :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Mínimo 8 caracteres"
                        :aria-invalid="!!form.errors.password"
                        :aria-describedby="form.errors.password ? 'password-error' : undefined"
                    />
                    <InputError id="password-error" :message="form.errors.password" />
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
                        :aria-invalid="!!form.errors.password_confirmation"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing" size="lg">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    <UserPlus v-else class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Creando cuenta…' : 'Crear cuenta' }}
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                ¿Ya tenés cuenta?
                <TextLink :href="route('login')" class="font-medium">Ingresar</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
