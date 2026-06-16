<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const deleteUser = (e: Event) => {
    e.preventDefault();

    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall title="Eliminar cuenta" description="Eliminá tu cuenta y todos sus datos de forma permanente" />
        <div class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Advertencia</p>
                <p class="text-sm">Esta acción no se puede deshacer. Todos tus datos se eliminarán permanentemente.</p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive">Eliminar cuenta</Button>
                </DialogTrigger>
                <DialogContent>
                    <form class="space-y-6" @submit="deleteUser" novalidate>
                        <DialogHeader class="space-y-3">
                            <DialogTitle>¿Estás seguro de que querés eliminar tu cuenta?</DialogTitle>
                            <DialogDescription>
                                Una vez eliminada, todos los datos asociados a tu cuenta se perderán permanentemente. Ingresá tu contraseña para confirmar.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="sr-only">Contraseña</Label>
                            <Input
                                id="password"
                                type="password"
                                ref="passwordInput"
                                v-model="form.password"
                                placeholder="Contraseña"
                                required
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <DialogFooter>
                            <DialogClose as-child>
                                <Button variant="secondary" @click="closeModal">Cancelar</Button>
                            </DialogClose>

                            <Button variant="destructive" type="submit" :disabled="form.processing">
                                Eliminar cuenta
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
