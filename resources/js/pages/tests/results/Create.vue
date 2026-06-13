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

const props = defineProps<{
    test: Test;
    applicant: Applicant;
    vacancy: Vacancy;
    existingResult?: {
        id: number;
        score: number;
        observations: string | null;
        answers: Array<{
            test_question_id: number;
            selected_indices: number[];
        }>;
    } | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tests', href: '/tests' },
    { title: props.test.name, href: `/tests/${props.test.id}` },
    { title: 'Record Result', href: '#' },
];

const initialAnswers = props.existingResult
    ? props.existingResult.answers.reduce<Record<number, number[]>>((acc, answer) => {
        acc[answer.test_question_id] = answer.selected_indices;
        return acc;
    }, {})
    : {};

const form = useForm({
    score: props.existingResult?.score ?? '' as string | number,
    observations: props.existingResult?.observations ?? '',
    answers: initialAnswers as Record<number, number[]>,
});

const submit = () => {
    form.post(route('test-results.store', [props.test.id, props.applicant.id, props.vacancy.id]));
};

const typeLabel = (type: string) => {
    return {
        numeric: 'Numeric',
        text: 'Text',
        multiple_choice: 'Multiple Choice',
    }[type] ?? type;
};
</script>

<template>
    <Head :title="`Record Result - ${test.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Record Result</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ applicant.name }} · {{ vacancy.position }} · {{ typeLabel(test.type) }}
                    </p>
                </div>
                <Link :href="route('vacancies.results.index', vacancy.id)">
                    <Button variant="outline">Back to Results</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Score</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="score">Score (0 to {{ test.max_score }})</Label>
                            <Input
                                id="score"
                                type="number"
                                step="0.01"
                                min="0"
                                :max="test.max_score"
                                v-model="form.score"
                                required
                            />
                            <InputError :message="form.errors.score" />
                        </div>

                        <div v-if="test.type !== 'multiple_choice'" class="grid gap-2">
                            <Label for="observations">Observations</Label>
                            <Textarea
                                id="observations"
                                v-model="form.observations"
                                placeholder="Add any observations about this result..."
                                rows="4"
                            />
                            <InputError :message="form.errors.observations" />
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="test.type === 'multiple_choice'">
                    <CardHeader>
                        <CardTitle>Answers</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <MultipleChoiceGrader v-model:modelValue="form.answers" :questions="test.questions" />
                        <InputError class="mt-2" :message="form.errors.answers" />
                    </CardContent>
                </Card>

                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="form.processing">Save Result</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
