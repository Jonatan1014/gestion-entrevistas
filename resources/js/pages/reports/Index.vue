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
    { title: 'Reports', href: '/reports' },
];

const formatDate = (value: string | null): string => {
    if (! value) return '-';
    return new Date(value).toLocaleString();
};

const groupedComparison = computed(() => {
    const rows = props.comparison?.data ?? [];
    const grouped = new Map<number, ComparisonRow & { scores: { test_name: string; score: number | null; max_score: number | null; weight: number | null }[] }>();

    rows.forEach((row) => {
        if (! grouped.has(row.applicant_id)) {
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
    <Head title="Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-semibold">Reports</h1>

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
                            Comparison
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger value="pipeline" as-child>
                        <Link :href="route('reports.pipeline')" preserve-state preserve-scroll>
                            Pipeline
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger value="averages" as-child>
                        <Link :href="route('reports.averages')" preserve-state preserve-scroll>
                            Averages
                        </Link>
                    </TabsTrigger>
                    <TabsTrigger value="interviews" as-child>
                        <Link :href="route('reports.interviews')" preserve-state preserve-scroll>
                            Interviews
                        </Link>
                    </TabsTrigger>
                </TabsList>

                <TabsContent value="comparison">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Candidate Comparison</CardTitle>
                            <div class="flex gap-2">
                                <Link :href="route('reports.comparison.pdf', filters)">
                                    <Button variant="outline" size="sm">Export PDF</Button>
                                </Link>
                                <Link :href="route('reports.comparison.excel', filters)">
                                    <Button variant="outline" size="sm">Export Excel</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 font-medium">Applicant</th>
                                            <th class="px-4 py-3 font-medium">Vacancy</th>
                                            <th class="px-4 py-3 font-medium">Test Scores</th>
                                            <th class="px-4 py-3 font-medium">Weighted Avg</th>
                                            <th class="px-4 py-3 font-medium">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in groupedComparison" :key="row.applicant_id" class="border-b hover:bg-muted/50">
                                            <td class="px-4 py-3">{{ row.applicant_name }}</td>
                                            <td class="px-4 py-3">{{ row.vacancy_position }}</td>
                                            <td class="px-4 py-3">
                                                <div v-if="row.scores.length" class="space-y-1">
                                                    <div v-for="score in row.scores" :key="score.test_name">
                                                        {{ score.test_name }}: {{ score.score ?? 'N/A' }} / {{ score.max_score ?? '-' }} ({{ score.weight }}%)
                                                    </div>
                                                </div>
                                                <span v-else class="text-muted-foreground">N/A</span>
                                            </td>
                                            <td class="px-4 py-3">{{ row.weighted_avg ? row.weighted_avg.toFixed(2) : 'N/A' }}</td>
                                            <td class="px-4 py-3">
                                                <Badge variant="secondary">{{ row.status }}</Badge>
                                            </td>
                                        </tr>
                                        <tr v-if="groupedComparison.length === 0">
                                            <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                                No applicants found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="pipeline">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Selection Pipeline</CardTitle>
                            <div class="flex gap-2">
                                <Link :href="route('reports.pipeline.pdf', filters)">
                                    <Button variant="outline" size="sm">Export PDF</Button>
                                </Link>
                                <Link :href="route('reports.pipeline.excel', filters)">
                                    <Button variant="outline" size="sm">Export Excel</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 font-medium">Vacancy</th>
                                            <th class="px-4 py-3 font-medium">Status</th>
                                            <th class="px-4 py-3 font-medium">Applicants</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in pipeline?.data" :key="`${row.vacancy_id}-${row.status}`" class="border-b hover:bg-muted/50">
                                            <td class="px-4 py-3">{{ row.vacancy_position }}</td>
                                            <td class="px-4 py-3">
                                                <Badge variant="secondary">{{ row.status }}</Badge>
                                            </td>
                                            <td class="px-4 py-3">{{ row.count }}</td>
                                        </tr>
                                        <tr v-if="!pipeline?.data?.length">
                                            <td colspan="3" class="px-4 py-8 text-center text-muted-foreground">
                                                No pipeline data found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="averages">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Score Averages</CardTitle>
                            <div class="flex gap-2">
                                <Link :href="route('reports.averages.pdf', filters)">
                                    <Button variant="outline" size="sm">Export PDF</Button>
                                </Link>
                                <Link :href="route('reports.averages.excel', filters)">
                                    <Button variant="outline" size="sm">Export Excel</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 font-medium">Test</th>
                                            <th class="px-4 py-3 font-medium">Vacancy</th>
                                            <th class="px-4 py-3 font-medium">Average Score</th>
                                            <th class="px-4 py-3 font-medium">Scored Applicants</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in averages?.data" :key="`${row.test_id}-${row.vacancy_id}`" class="border-b hover:bg-muted/50">
                                            <td class="px-4 py-3">{{ row.test_name }}</td>
                                            <td class="px-4 py-3">{{ row.vacancy_position }}</td>
                                            <td class="px-4 py-3">{{ row.avg_score.toFixed(2) }}</td>
                                            <td class="px-4 py-3">{{ row.scored_count }}</td>
                                        </tr>
                                        <tr v-if="!averages?.data?.length">
                                            <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                                No average data found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="interviews">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Completed Interviews</CardTitle>
                            <div class="flex gap-2">
                                <Link :href="route('reports.interviews.pdf', filters)">
                                    <Button variant="outline" size="sm">Export PDF</Button>
                                </Link>
                                <Link :href="route('reports.interviews.excel', filters)">
                                    <Button variant="outline" size="sm">Export Excel</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="px-4 py-3 font-medium">Date</th>
                                            <th class="px-4 py-3 font-medium">Applicant</th>
                                            <th class="px-4 py-3 font-medium">Interviewer</th>
                                            <th class="px-4 py-3 font-medium">Vacancy</th>
                                            <th class="px-4 py-3 font-medium">Observations</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in interviews?.data" :key="row.id" class="border-b hover:bg-muted/50">
                                            <td class="px-4 py-3">{{ formatDate(row.scheduled_at) }}</td>
                                            <td class="px-4 py-3">{{ row.applicant_name }}</td>
                                            <td class="px-4 py-3">{{ row.interviewer_name }}</td>
                                            <td class="px-4 py-3">{{ row.vacancy_position }}</td>
                                            <td class="px-4 py-3">{{ row.observations ?? '-' }}</td>
                                        </tr>
                                        <tr v-if="!interviews?.data?.length">
                                            <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                                No completed interviews found.
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
