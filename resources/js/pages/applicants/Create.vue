<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';

defineProps<{
    vacancies: Array<{ id: number; position: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Postulantes', href: '/applicants' },
    { title: 'Registrar', href: '/applicants/create' },
];

const form = useForm({
    name: '',
    phone: '',
    email: '',
    address: '',
    vacancy_ids: [] as number[],
});

const toggleVacancy = (id: number) => {
    const idx = form.vacancy_ids.indexOf(id);
    if (idx === -1) {
        form.vacancy_ids.push(id);
    } else {
        form.vacancy_ids.splice(idx, 1);
    }
};

const submit = () => {
    form.post(route('applicants.store'));
};
</script>

<template>
    <Head title="Registrar postulante" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Registrar postulante</h1>
                <Button as-child variant="outline">
                    <Link :href="route('applicants.index')">Volver a postulantes</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Datos del postulante</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6" novalidate>
                        <div class="grid gap-2">
                            <Label for="name">Nombre completo</Label>
                            <Input id="name" type="text" v-model="form.name" required placeholder="Ej. Juan Pérez" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone">Teléfono</Label>
                            <Input id="phone" type="text" v-model="form.phone" required placeholder="Ej. +5491112345678" />
                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" required placeholder="Ej. juan@email.com" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Vacantes a las que aplica</Label>
                            <p class="text-xs text-muted-foreground">Seleccioná una o más vacantes. Podés agregar o quitar después desde el perfil.</p>
                            <div class="space-y-2 rounded-lg border p-4">
                                <div v-if="vacancies.length === 0" class="py-4 text-center text-sm text-muted-foreground">
                                    No hay vacantes abiertas disponibles.
                                </div>
                                <div v-for="v in vacancies" :key="v.id" class="flex items-center gap-3 rounded-md p-2 transition-colors hover:bg-muted/50">
                                    <Checkbox
                                        :id="`vacancy-${v.id}`"
                                        :checked="form.vacancy_ids.includes(v.id)"
                                        @update:checked="toggleVacancy(v.id)"
                                    />
                                    <Label :for="`vacancy-${v.id}`" class="cursor-pointer">{{ v.position }}</Label>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.vacancy_ids" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="address">Dirección</Label>
                            <Textarea id="address" v-model="form.address" placeholder="Ej. Buenos Aires" rows="3" />
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">Registrar postulante</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
