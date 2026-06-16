<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ReportFilters from '@/components/report/ReportFilters.vue';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { FileText, Table } from 'lucide-vue-next';

interface VacancyOption {
    id: number;
    position: string;
}

interface InterviewerOption {
    id: number;
    name: string;
}

interface ComparisonRow {
    applicant_id: number;
    applicant_name: string;
    vacancy_id: number;
    vacancy_position: string;
    test_id: number | null;
    test_name: string | null;
    score: number | null;
    max_score: number | null;
    weight: number | null;
    weighted_avg: number | null;
    status: string;
}

interface PipelineRow {
    vacancy_id: number;
    vacancy_position: string;
    status: string;
    count: number;
}

interface AverageRow {
    test_id: number;
    test_name: string;
    vacancy_id: number;
    vacancy_position: string;
    avg_score: number;
    scored_count: number;
}

interface InterviewRow {
    id: number;
    scheduled_at: string;
    completed_at: string | null;
    status: string;
    applicant_name: string;
    interviewer_name: string;
    vacancy_position: string;
    observations: string | null;
}

interface PaginatedData<T> {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = defineProps<{
    activeTab: string;
    comparison?: PaginatedData<ComparisonRow>;
    pipeline?: PaginatedData<PipelineRow>;
    averages?: PaginatedData<AverageRow>;
    interviews?: PaginatedData<InterviewRow>;
    vacancies: VacancyOption[];
    interviewers: InterviewerOption[];
    filters: {
        vacancy_id?: string;
        date_from?: string;
        date_to?: string;
        interviewer_id?: string;
    };
    isAdmin: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Reportes', href: '/reports' },
];

const formatDate = (value: string | null): string => {
    if (!value) return '-';
    return new Date(value).toLocaleString('es-AR');
};

const statusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        registered: 'Registrado',
        in_interview: 'En entrevista',
        evaluated: 'Evaluado',
        apt: 'Apto',
        no_apt: 'No apto',
    };
    return labels[status] ?? status;
};

const groupedComparison = computed(() => {
    const rows = props.comparison?.data ?? [];
    const grouped = new Map<number, ComparisonRow & { scores: { test_name: string; score: number | null; max_score: number | null; weight: number | null }[] }>();

    rows.forEach((row) => {
        if (!grouped.has(row.applicant_id)) {
            grouped.set(row.applicant_id, {
                ...row,
                scores: [],
            });
        }

        const entry = grouped.get(row.applicant_id)!;
        if (row.test_id) {
            entry.scores.push({
                test_name: row.test_name ?? '-',
                score: row.score,
                max_score: row.max_score,
                weight: row.weight,
            });
        }
    });

    return Array.from(grouped.values());
});
</script>

<template>
    <Head title="Reportes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-semibold">Reportes</h1>

            <ReportFilters
                :vacancies="vacancies"
                :interviewers="interviewers"
                :filters="filters"
                :is-admin="isAdmin"
            />

            <Tabs :default-value="activeTab" class="w-full">
                <TabsList class="grid w-full grid-cols-4 sm:w-fit">
                    <TabsTrigger value="comparison" as-child>
                        <Link :href="route('reports.comparison')" preserve-state preserve-scroll>
                            Comparación
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger value="pipeline" as-child>
                        <Link :href="route('reports.pipeline')" preserve-state preserve-scroll>
                            Pipeline
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger value="averages" as-child>
                        <Link :href="route('reports.averages')" preserve-state preserve-scroll>
                            Promedios
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger value="interviews" as-child>
                        <Link :href="route('reports.interviews')" preserve-state preserve-scroll>
                            Entrevistas
                        </Link>
                    </TabsTrigger>
                </TabsList>

                <!-- Comparison -->
                <TabsContent value="comparison">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Comparación de candidatos</CardTitle>
                            <div class="flex gap-2">
                                <Button as-child variant="outline" size="sm">
                                    <Link :href="route('reports.comparison.pdf', filters)">
                                        <FileText class="mr-1 h-3.5 w-3.5" />
                                        Exportar PDF
                                    </Link>
                                </Button>
                                <Button as-child variant="outline" size="sm">
                                    <Link :href="route('reports.comparison.excel', filters)">
                                        <Table class="mr-1 h-3.5 w-3.5" />
                                        Exportar Excel
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 font-medium">Postulante</th>
                                            <th class="px-4 py-3 font-medium">Vacante</th>
                                            <th class="px-4 py-3 font-medium">Puntajes</th>
                                            <th class="px-4 py-3 font-medium">Promedio</th>
                                            <th class="px-4 py-3 font-medium">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in groupedComparison" :key="row.applicant_id" class="border-b hover:bg-muted/50">
                                            <td class="px-4 py-3 font-medium">{{ row.applicant_name }}</td>
                                            <td class="px-4 py-3 text-muted-foreground">{{ row.vacancy_position }}</td>
                                            <td class="px-4 py-3">
                                                <div v-if="row.scores.length" class="space-y-1">
                                                    <div v-for="score in row.scores" :key="score.test_name">
                                                        {{ score.test_name }}: {{ score.score ?? '-' }} / {{ score.max_score ?? '-' }} ({{ score.weight }}%)
                                                    </div>
                                                </div>
                                                <span v-else class="text-muted-foreground">—</span>
                                            </td>
                                            <td class="px-4 py-3">{{ row.weighted_avg ? row.weighted_avg.toFixed(2) : '—' }}</td>
                                            <td class="px-4 py-3">
                                                <Badge variant="secondary">{{ statusLabel(row.status) }}</Badge>
                                            </td>
                                        </tr>
                                        <tr v-if="groupedComparison.length === 0">
                                            <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                                No se encontraron postulantes.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Pipeline -->
                <TabsContent value="pipeline">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Pipeline de selección</CardTitle>
                            <div class="flex gap-2">
                                <Button as-child variant="outline" size="sm">
                                    <Link :href="route('reports.pipeline.pdf', filters)">
                                        <FileText class="mr-1 h-3.5 w-3.5" />
                                        Exportar PDF
                                    </Link>
                                </Button>
                                <Button as-child variant="outline" size="sm">
                                    <Link :href="route('reports.pipeline.excel', filters)">
                                        <Table class="mr-1 h-3.5 w-3.5" />
                                        Exportar Excel
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 font-medium">Vacante</th>
                                            <th class="px-4 py-3 font-medium">Estado</th>
                                            <th class="px-4 py-3 font-medium">Postulantes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in pipeline?.data" :key="`${row.vacancy_id}-${row.status}`" class="border-b hover:bg-muted/50">
                                            <td class="px-4 py-3">{{ row.vacancy_position }}</td>
                                            <td class="px-4 py-3">
                                                <Badge variant="secondary">{{ statusLabel(row.status) }}</Badge>
                                            </td>
                                            <td class="px-4 py-3">{{ row.count }}</td>
                                        </tr>
                                        <tr v-if="!pipeline?.data?.length">
                                            <td colspan="3" class="px-4 py-8 text-center text-muted-foreground">
                                                Sin datos de pipeline.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Averages -->
                <TabsContent value="averages">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Promedios por prueba</CardTitle>
                            <div class="flex gap-2">
                                <Button as-child variant="outline" size="sm">
                                    <Link :href="route('reports.averages.pdf', filters)">
                                        <FileText class="mr-1 h-3.5 w-3.5" />
                                        Exportar PDF
                                    </Link>
                                </Button>
                                <Button as-child variant="outline" size="sm">
                                    <Link :href="route('reports.averages.excel', filters)">
                                        <Table class="mr-1 h-3.5 w-3.5" />
                                        Exportar Excel
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 font-medium">Prueba</th>
                                            <th class="px-4 py-3 font-medium">Vacante</th>
                                            <th class="px-4 py-3 font-medium">Promedio</th>
                                            <th class="px-4 py-3 font-medium">Evaluados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in averages?.data" :key="`${row.test_id}-${row.vacancy_id}`" class="border-b hover:bg-muted/50">
                                            <td class="px-4 py-3">{{ row.test_name }}</td>
                                            <td class="px-4 py-3 text-muted-foreground">{{ row.vacancy_position }}</td>
                                            <td class="px-4 py-3">{{ row.avg_score.toFixed(2) }}</td>
                                            <td class="px-4 py-3">{{ row.scored_count }}</td>
                                        </tr>
                                        <tr v-if="!averages?.data?.length">
                                            <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                                Sin datos de promedios.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Interviews -->
                <TabsContent value="interviews">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Entrevistas completadas</CardTitle>
                            <div class="flex gap-2">
                                <Button as-child variant="outline" size="sm">
                                    <Link :href="route('reports.interviews.pdf', filters)">
                                        <FileText class="mr-1 h-3.5 w-3.5" />
                                        Exportar PDF
                                    </Link>
                                </Button>
                                <Button as-child variant="outline" size="sm">
                                    <Link :href="route('reports.interviews.excel', filters)">
                                        <Table class="mr-1 h-3.5 w-3.5" />
                                        Exportar Excel
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 font-medium">Fecha</th>
                                            <th class="px-4 py-3 font-medium">Postulante</th>
                                            <th class="px-4 py-3 font-medium">Entrevistador</th>
                                            <th class="px-4 py-3 font-medium">Vacante</th>
                                            <th class="px-4 py-3 font-medium">Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in interviews?.data" :key="row.id" class="border-b hover:bg-muted/50">
                                            <td class="px-4 py-3">{{ formatDate(row.scheduled_at) }}</td>
                                            <td class="px-4 py-3 font-medium">{{ row.applicant_name }}</td>
                                            <td class="px-4 py-3 text-muted-foreground">{{ row.interviewer_name }}</td>
                                            <td class="px-4 py-3 text-muted-foreground">{{ row.vacancy_position }}</td>
                                            <td class="px-4 py-3">{{ row.observations ?? '—' }}</td>
                                        </tr>
                                        <tr v-if="!interviews?.data?.length">
                                            <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                                No hay entrevistas completadas.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
