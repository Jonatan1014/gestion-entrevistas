<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    vacancy: {
        id: number;
        position: string;
        location: string;
        requirements: string;
        min_grade: number | null;
        status: string;
    };
    tests: Array<{ id: number; name: string; type: string }>;
    attachedTestIds: number[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vacantes', href: '/vacancies' },
    { title: 'Editar', href: `/vacancies/${props.vacancy.id}/edit` },
];

const form = useForm({
    position: props.vacancy.position,
    location: props.vacancy.location,
    requirements: props.vacancy.requirements,
    min_grade: props.vacancy.min_grade ?? ('' as string | number),
    test_ids: [...props.attachedTestIds] as number[],
});

const typeLabel = (type: string) => {
    return { numeric: 'Numérica', text: 'Texto', multiple_choice: 'Opción múltiple' }[type] ?? type;
};

const toggleTest = (id: number) => {
    const idx = form.test_ids.indexOf(id);
    if (idx === -1) {
        form.test_ids.push(id);
    } else {
        form.test_ids.splice(idx, 1);
    }
};

const submit = () => {
    form.put(route('vacancies.update', props.vacancy.id));
};
</script>

<template>
    <Head title="Editar vacante" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Editar vacante</h1>
                <Button as-child variant="outline">
                    <Link :href="route('vacancies.show', vacancy.id)">Cancelar</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Datos de la vacante</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6" novalidate>
                        <div class="grid gap-2">
                            <Label for="position">Cargo</Label>
                            <Input id="position" type="text" v-model="form.position" required />
                            <InputError class="mt-2" :message="form.errors.position" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="location">Ubicación</Label>
                            <Input id="location" type="text" v-model="form.location" required />
                            <InputError class="mt-2" :message="form.errors.location" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="requirements">Requisitos</Label>
                            <Textarea id="requirements" v-model="form.requirements" required rows="4" />
                            <InputError class="mt-2" :message="form.errors.requirements" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="min_grade">Nota mínima (opcional)</Label>
                            <Input id="min_grade" type="number" step="0.01" v-model="form.min_grade" />
                            <InputError class="mt-2" :message="form.errors.min_grade" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Pruebas requeridas</Label>
                            <p class="text-xs text-muted-foreground">Seleccioná las pruebas que se deben realizar para esta vacante. Los pesos se configuran desde la página de la vacante.</p>
                            <div class="space-y-2 rounded-lg border p-4">
                                <div v-if="tests.length === 0" class="py-4 text-center text-sm text-muted-foreground">
                                    No hay pruebas disponibles.
                                </div>
                                <div v-for="test in tests" :key="test.id" class="flex items-center gap-3 rounded-md p-2 transition-colors hover:bg-muted/50">
                                    <Checkbox
                                        :id="`test-${test.id}`"
                                        :checked="form.test_ids.includes(test.id)"
                                        @update:checked="toggleTest(test.id)"
                                    />
                                    <Label :for="`test-${test.id}`" class="flex-1 cursor-pointer">
                                        <span class="font-medium">{{ test.name }}</span>
                                    </Label>
                                    <Badge variant="outline" class="text-xs">{{ typeLabel(test.type) }}</Badge>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.test_ids" />
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
