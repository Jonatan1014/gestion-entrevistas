<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Configuración del perfil',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Configuración del perfil" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Información del perfil" description="Actualizá tu nombre y dirección de email" />

                <form @submit.prevent="submit" class="space-y-6" novalidate>
                    <div class="grid gap-2">
                        <Label for="name">Nombre</Label>
                        <Input id="name" v-model="form.name" required autocomplete="name" placeholder="Tu nombre completo" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autocomplete="email"
                            placeholder="email@ejemplo.com"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="text-sm text-muted-foreground">
                            Tu email no está verificado.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="rounded-md text-sm text-[#0d0d0d] underline hover:text-[#51eead] dark:text-[#51eead] dark:hover:text-white focus:ring-2 focus:ring-[#51eead]"
                            >
                                Hacé clic acá para reenviar el email de verificación.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-[#51eead]">
                            Se envió un nuevo enlace de verificación a tu email.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Guardar cambios</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-muted-foreground">Guardado.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
