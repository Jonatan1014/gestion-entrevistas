<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';

interface Test {
    id: number;
    name: string;
    type: string;
    max_score: number;
    pivot: { weight: number };
}

interface Vacancy {
    id: number;
    position: string;
}

const props = defineProps<{
    vacancy: Vacancy;
    tests: Test[];
    availableTests: Test[];
    canManageTests: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vacantes', href: '/vacancies' },
    { title: props.vacancy.position, href: `/vacancies/${props.vacancy.id}` },
    { title: 'Pruebas', href: `/vacancies/${props.vacancy.id}/tests` },
];

const attachForm = useForm({ test_id: '', weight: '' as string | number });
const submitAttach = () => attachForm.post(route('vacancies.tests.attach', props.vacancy.id));

const weightForms = props.tests.map((test) => useForm({ weight: test.pivot.weight }));
const updateWeight = (test: Test, index: number) => weightForms[index].put(route('vacancies.tests.update-weight', [props.vacancy.id, test.id]));

const typeLabel = (type: string) => ({ numeric: 'Numérica', text: 'Texto', multiple_choice: 'Opción múltiple' }[type] ?? type);
</script>

<template>
    <Head :title="`Pruebas de ${vacancy.position}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Pruebas de {{ vacancy.position }}</h1>
                <Button as-child variant="outline">
                    <Link :href="route('vacancies.show', vacancy.id)">Volver a la vacante</Link>
                </Button>
            </div>

            <Card v-if="canManageTests">
                <CardHeader>
                    <CardTitle>Asociar prueba</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitAttach" class="space-y-4" novalidate>
                        <div class="grid gap-2">
                            <Label for="test_id">Prueba</Label>
                            <Select v-model="attachForm.test_id">
                                <SelectTrigger id="test_id">
                                    <SelectValue placeholder="Seleccionar prueba" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="test in availableTests" :key="test.id" :value="String(test.id)">
                                        {{ test.name }} ({{ typeLabel(test.type) }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="attachForm.errors.test_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="weight">Peso (%)</Label>
                            <Input id="weight" v-model="attachForm.weight" type="number" step="0.01" min="0" max="100" required placeholder="Ej. 40" />
                            <InputError :message="attachForm.errors.weight" />
                        </div>

                        <Button type="submit" :disabled="attachForm.processing">Asociar</Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Pruebas asociadas</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="tests.length === 0" class="py-8 text-center text-muted-foreground">
                        No hay pruebas asociadas a esta vacante.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="(test, index) in tests" :key="test.id" class="rounded-lg border p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-medium">{{ test.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ typeLabel(test.type) }} · Puntaje máx: {{ test.max_score }}</p>
                                </div>
                                <Button v-if="canManageTests" as-child variant="destructive" size="sm">
                                    <Link :href="route('vacancies.tests.detach', [vacancy.id, test.id])" method="delete">Quitar</Link>
                                </Button>
                            </div>

                            <form v-if="canManageTests" @submit.prevent="updateWeight(test, index)" class="mt-4 flex items-end gap-4">
                                <div class="grid gap-2">
                                    <Label :for="`weight-${test.id}`">Peso (%)</Label>
                                    <Input :id="`weight-${test.id}`" v-model="weightForms[index].weight" type="number" step="0.01" min="0" max="100" required />
                                    <InputError :message="weightForms[index].errors.weight" />
                                </div>
                                <Button type="submit" :disabled="weightForms[index].processing" size="sm">Actualizar peso</Button>
                            </form>
                            <p v-else class="mt-2 text-sm text-muted-foreground">Peso: {{ test.pivot.weight }}%</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
