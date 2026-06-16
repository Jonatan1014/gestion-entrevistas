<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, LogIn } from 'lucide-vue-next';
import { ref, nextTick } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const emailInput = ref<HTMLInputElement>();
const passwordInput = ref<HTMLInputElement>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onError: () => {
            // Focus first field with an error
            nextTick(() => {
                if (form.errors.email) {
                    emailInput.value?.focus();
                } else if (form.errors.password) {
                    passwordInput.value?.focus();
                }
            });
        },
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Iniciar sesión" description="Ingresá tu email y contraseña para acceder al sistema">
        <Head title="Iniciar sesión" />

        <div
            v-if="status"
            role="status"
            aria-live="polite"
            class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6" novalidate>
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        ref="emailInput"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        v-model="form.email"
                        placeholder="email@ejemplo.com"
                        :aria-invalid="!!form.errors.email"
                        :aria-describedby="form.errors.email ? 'email-error' : undefined"
                    />
                    <InputError id="email-error" :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Contraseña</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs text-muted-foreground hover:text-foreground"
                        >
                            ¿Olvidaste tu contraseña?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        ref="passwordInput"
                        type="password"
                        required
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="••••••••"
                        :aria-invalid="!!form.errors.password"
                        :aria-describedby="form.errors.password ? 'password-error' : undefined"
                    />
                    <InputError id="password-error" :message="form.errors.password" />
                </div>

                <div class="flex items-center gap-3">
                    <Checkbox id="remember" v-model:checked="form.remember" />
                    <Label for="remember" class="text-sm font-normal cursor-pointer">
                        Recordarme
                    </Label>
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing" size="lg">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    <LogIn v-else class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Ingresando…' : 'Ingresar' }}
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                ¿No tenés cuenta?
                <TextLink :href="route('register')" class="font-medium">Registrate</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
