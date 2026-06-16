<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/InputError.vue';
import WeightedAverageDisplay from '@/components/test-result/WeightedAverageDisplay.vue';
import { BarChart, Plus, History, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';

interface Attempt {
    id: number;
    score: number;
    observations: string | null;
    created_at: string;
    is_manual_override: boolean;
}

interface Test {
    id: number;
    name: string;
    type: string;
    max_score: number;
    pivot: { weight: number };
}

interface WeightedAverage {
    score: number;
    meets_min_grade: boolean;
    breakdown: Array<{
        score: number;
        max_score: number;
        weight: number;
        normalized: number;
        contribution: number;
        test_name?: string;
    }>;
}

interface ApplicantResult {
    id: number;
    name: string;
    email: string;
    status: string;
    results_by_test: Record<string, Attempt[]>;
    weighted_average: WeightedAverage;
}

interface Vacancy {
    id: number;
    position: string;
    min_grade: number | null;
}

const props = defineProps<{
    vacancy: Vacancy;
    applicants: ApplicantResult[];
    tests: Test[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vacantes', href: '/vacancies' },
    { title: props.vacancy.position, href: `/vacancies/${props.vacancy.id}` },
    { title: 'Resultados', href: '#' },
];

const selectedApplicant = ref<ApplicantResult | null>(null);
const dialogOpen = ref(false);

const statusForm = useForm({
    status: '',
    justification: '',
});

const openStatusDialog = (applicant: ApplicantResult) => {
    selectedApplicant.value = applicant;
    statusForm.status = '';
    statusForm.justification = '';
    dialogOpen.value = true;
};

const submitStatus = () => {
    if (!selectedApplicant.value) return;
    statusForm.put(route('vacancies.results.final-status', [props.vacancy.id, selectedApplicant.value.id]), {
        onSuccess: () => {
            dialogOpen.value = false;
            selectedApplicant.value = null;
        },
    });
};

const getAttempts = (applicant: ApplicantResult, testId: number): Attempt[] => {
    return applicant.results_by_test?.[String(testId)] ?? [];
};

const getLatestScore = (applicant: ApplicantResult, testId: number): string => {
    const attempts = getAttempts(applicant, testId);
    if (attempts.length === 0) return '—';
    return String(attempts[0].score);
};

const getAttemptCount = (applicant: ApplicantResult, testId: number): number => {
    return getAttempts(applicant, testId).length;
};

const statusLabel = (status: string): string => {
    return {
        registered: 'Registrado',
        in_interview: 'En entrevista',
        evaluated: 'Evaluado',
        apt: 'Apto',
        no_apt: 'No apto',
    }[status] ?? status;
};

const statusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    const map: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        registered: 'secondary',
        in_interview: 'secondary',
        evaluated: 'default',
        apt: 'default',
        no_apt: 'destructive',
    };
    return map[status] ?? 'outline';
};
</script>

<template>
    <Head :title="`Resultados — ${vacancy.position}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Resultados: {{ vacancy.position }}</h1>
                <Button as-child variant="outline">
                    <Link :href="route('vacancies.show', vacancy.id)">Volver a la vacante</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Resultados por postulante</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="applicants.length === 0" class="py-8 text-center text-muted-foreground">
                        No hay postulantes registrados en esta vacante.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-2 text-left font-medium">Postulante</th>
                                    <th v-for="test in tests" :key="test.id" class="px-4 py-2 text-left font-medium">
                                        <div>{{ test.name }}</div>
                                        <div class="text-xs font-normal text-muted-foreground">({{ test.pivot.weight }}%)</div>
                                    </th>
                                    <th class="px-4 py-2 text-left font-medium">Promedio</th>
                                    <th class="px-4 py-2 text-left font-medium">Estado</th>
                                    <th class="px-4 py-2 text-left font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="applicant in applicants" :key="applicant.id" class="border-b hover:bg-muted/30">
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ applicant.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ applicant.email }}</p>
                                    </td>
                                    <td v-for="test in tests" :key="test.id" class="px-4 py-3">
                                        <Link
                                            v-if="getAttemptCount(applicant, test.id) > 0"
                                            :href="route('test-results.create', [test.id, applicant.id, vacancy.id])"
                                            class="text-left hover:text-[#51eead] transition-colors"
                                        >
                                            <span class="font-medium">{{ getLatestScore(applicant, test.id) }}</span>
                                            <span v-if="getAttemptCount(applicant, test.id) > 1" class="ml-1 text-xs text-muted-foreground">
                                                ({{ getAttemptCount(applicant, test.id) }} intentos)
                                            </span>
                                        </Link>
                                        <div v-else class="flex flex-col gap-1">
                                            <span class="text-muted-foreground text-xs">Sin puntaje</span>
                                            <Link
                                                :href="route('test-results.create', [test.id, applicant.id, vacancy.id])"
                                                class="text-xs text-[#51eead] hover:underline"
                                            >
                                                + Registrar
                                            </Link>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="{
                                                'font-medium text-green-600': applicant.weighted_average.meets_min_grade,
                                                'font-medium text-red-600': !applicant.weighted_average.meets_min_grade,
                                            }"
                                        >
                                            {{ applicant.weighted_average.score.toFixed(2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge :variant="statusVariant(applicant.status)">
                                            {{ statusLabel(applicant.status) }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1">
                                            <Button variant="outline" size="sm" @click="openStatusDialog(applicant)">
                                                <Pencil class="mr-1 h-3 w-3" />
                                                Estado
                                            </Button>
                                            <Button
                                                v-for="test in tests"
                                                :key="test.id"
                                                as-child
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Link :href="route('test-results.create', [test.id, applicant.id, vacancy.id])">
                                                    <Plus class="mr-1 h-3 w-3" />
                                                    {{ test.name.split(' ')[0] }}
                                                </Link>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <WeightedAverageDisplay
                v-if="applicants.length > 0"
                :weighted-average="applicants[0].weighted_average"
                :min-grade="vacancy.min_grade"
            />

            <!-- Set Final Status Dialog -->
            <Dialog v-model:open="dialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Definir estado final</DialogTitle>
                        <DialogDescription>
                            Establecé el estado final para {{ selectedApplicant?.name }}.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitStatus" class="space-y-4" novalidate>
                        <div class="grid gap-2">
                            <Label for="status">Estado</Label>
                            <Select v-model="statusForm.status" required>
                                <SelectTrigger id="status">
                                    <SelectValue placeholder="Seleccionar estado" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="apt">Apto</SelectItem>
                                    <SelectItem value="no_apt">No apto</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="statusForm.errors.status" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="justification">Justificación</Label>
                            <Textarea
                                id="justification"
                                v-model="statusForm.justification"
                                placeholder="Justificá la decisión…"
                                rows="3"
                            />
                            <InputError :message="statusForm.errors.justification" />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="dialogOpen = false">Cancelar</Button>
                            <Button type="submit" :disabled="statusForm.processing">Guardar estado</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
