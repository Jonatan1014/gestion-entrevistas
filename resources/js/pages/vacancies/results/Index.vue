<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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
import { ref } from 'vue';

interface Test {
    id: number;
    name: string;
    type: string;
    max_score: number;
    pivot: {
        weight: number;
    };
}

interface Result {
    id: number;
    test_id: number;
    score: number;
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
    results: Result[];
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
    { title: 'Vacancies', href: '/vacancies' },
    { title: props.vacancy.position, href: `/vacancies/${props.vacancy.id}` },
    { title: 'Results', href: '#' },
];

const selectedApplicant = ref<ApplicantResult | null>(null);
const dialogOpen = ref(false);

const statusForm = useForm({
    status: '',
    justification: '',
});

const openStatusDialog = (applicant: ApplicantResult) => {
    selectedApplicant.value = applicant;
    statusForm.status = applicant.status === 'apt' ? 'apt' : applicant.status === 'no_apt' ? 'no_apt' : '';
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

const getScoreForTest = (applicant: ApplicantResult, testId: number): string => {
    const result = applicant.results.find((r) => r.test_id === testId);

    return result ? `${result.score}` : '-';
};

const statusLabel = (status: string): string => {
    return {
        registered: 'Registered',
        in_interview: 'In Interview',
        evaluated: 'Evaluated',
        apt: 'Apt',
        no_apt: 'No Apt',
    }[status] ?? status;
};

const statusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    return {
        registered: 'secondary',
        in_interview: 'secondary',
        evaluated: 'default',
        apt: 'default',
        no_apt: 'destructive',
    }[status] as 'default' | 'secondary' | 'destructive' | 'outline' ?? 'outline';
};
</script>

<template>
    <Head :title="`Results - ${vacancy.position}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Results for {{ vacancy.position }}</h1>
                <Link :href="route('vacancies.show', vacancy.id)">
                    <Button variant="outline">Back to Vacancy</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Applicant Results</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="applicants.length === 0" class="py-8 text-center text-muted-foreground">
                        No applicants registered for this vacancy.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-2 text-left font-medium">Applicant</th>
                                    <th v-for="test in tests" :key="test.id" class="px-4 py-2 text-left font-medium">
                                        {{ test.name }} ({{ test.pivot.weight }}%)
                                    </th>
                                    <th class="px-4 py-2 text-left font-medium">Weighted Average</th>
                                    <th class="px-4 py-2 text-left font-medium">Final Status</th>
                                    <th class="px-4 py-2 text-left font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="applicant in applicants" :key="applicant.id" class="border-b">
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ applicant.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ applicant.email }}</p>
                                    </td>
                                    <td v-for="test in tests" :key="test.id" class="px-4 py-3">
                                        {{ getScoreForTest(applicant, test.id) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="{
                                                'font-medium text-green-600 dark:text-green-400': applicant.weighted_average.meets_min_grade,
                                                'font-medium text-red-600 dark:text-red-400': !applicant.weighted_average.meets_min_grade,
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
                                        <div class="flex items-center gap-2">
                                            <Button variant="outline" size="sm" @click="openStatusDialog(applicant)">
                                                Set Status
                                            </Button>
                                            <Link
                                                v-for="test in tests"
                                                :key="test.id"
                                                :href="route('test-results.create', [test.id, applicant.id, vacancy.id])"
                                            >
                                                <Button variant="outline" size="sm">Record {{ test.name }}</Button>
                                            </Link>
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

            <Dialog v-model:open="dialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Set Final Status</DialogTitle>
                        <DialogDescription>
                            Set the final status for {{ selectedApplicant?.name }}.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitStatus" class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="status">Status</Label>
                            <Select v-model="statusForm.status" required>
                                <SelectTrigger id="status">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="apt">Apt</SelectItem>
                                    <SelectItem value="no_apt">No Apt</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="statusForm.errors.status" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="justification">Justification</Label>
                            <Textarea
                                id="justification"
                                v-model="statusForm.justification"
                                placeholder="Provide a justification for this decision..."
                                rows="3"
                            />
                            <InputError :message="statusForm.errors.justification" />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="dialogOpen = false">Cancel</Button>
                            <Button type="submit" :disabled="statusForm.processing">Save Status</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
