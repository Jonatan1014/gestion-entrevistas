<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import MultipleChoiceGrader from '@/components/test-result/MultipleChoiceGrader.vue';

interface Question {
    id: number;
    question_text: string;
    options: string[];
    correct_answer_indices: number[];
    points: number;
}

interface Test {
    id: number;
    name: string;
    type: string;
    max_score: number;
    questions: Question[];
}

interface Applicant {
    id: number;
    name: string;
}

interface Vacancy {
    id: number;
    position: string;
}

interface Answer {
    id: number;
    test_question_id: number;
    selected_indices: number[];
    is_correct: boolean;
    test_question: Question;
}

interface Result {
    id: number;
    score: number;
    observations: string | null;
    is_manual_override: boolean;
    override_justification: string | null;
    answers: Answer[];
}

const props = defineProps<{
    test: Test;
    applicant: Applicant;
    vacancy: Vacancy;
    result: Result;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tests', href: '/tests' },
    { title: props.test.name, href: `/tests/${props.test.id}` },
    { title: 'Result', href: '#' },
];

const selectedAnswers = props.result.answers.reduce<Record<number, number[]>>((acc, answer) => {
    acc[answer.test_question_id] = answer.selected_indices;
    return acc;
}, {});
</script>

<template>
    <Head :title="`Result - ${test.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Test Result</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ applicant.name }} · {{ vacancy.position }}
                    </p>
                </div>
                <Link :href="route('vacancies.results.index', vacancy.id)">
                    <Button variant="outline">Back to Results</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Score</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex items-center gap-4">
                        <p class="text-3xl font-bold">{{ result.score }} / {{ test.max_score }}</p>
                        <Badge v-if="result.is_manual_override" variant="outline">Manual Override</Badge>
                    </div>
                    <p v-if="result.observations" class="text-sm text-muted-foreground">
                        {{ result.observations }}
                    </p>
                    <p v-if="result.override_justification" class="text-sm text-muted-foreground">
                        <span class="font-medium">Justification:</span> {{ result.override_justification }}
                    </p>
                </CardContent>
            </Card>

            <Card v-if="test.type === 'multiple_choice'">
                <CardHeader>
                    <CardTitle>Answers</CardTitle>
                </CardHeader>
                <CardContent>
                    <MultipleChoiceGrader :questions="test.questions" :model-value="selectedAnswers" read-only />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
