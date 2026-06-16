<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    applicant: {
        id: number;
        name: string;
        phone: string;
        email: string;
        address: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Postulantes', href: '/applicants' },
    { title: props.applicant.name, href: `/applicants/${props.applicant.id}` },
    { title: 'Editar', href: `/applicants/${props.applicant.id}/edit` },
];

const form = useForm({
    name: props.applicant.name,
    phone: props.applicant.phone,
    email: props.applicant.email,
    address: props.applicant.address ?? '',
});

const submit = () => {
    form.put(route('applicants.update', props.applicant.id));
};
</script>

<template>
    <Head title="Editar postulante" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Editar postulante</h1>
                <Button as-child variant="outline">
                    <Link :href="route('applicants.show', applicant.id)">Volver al postulante</Link>
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
                            <Input id="name" type="text" v-model="form.name" required placeholder="Nombre del postulante" />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone">Teléfono</Label>
                            <Input id="phone" type="text" v-model="form.phone" required placeholder="+54..." />
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" required placeholder="email@ejemplo.com" />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="address">Dirección</Label>
                            <Textarea id="address" v-model="form.address" rows="3" placeholder="Dirección (opcional)" />
                            <InputError :message="form.errors.address" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">Guardar cambios</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
