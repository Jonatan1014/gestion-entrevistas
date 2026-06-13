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
import InputError from '@/components/InputError.vue';
import TestQuestionEditor from '@/components/test/TestQuestionEditor.vue';

interface Question {
    id?: number;
    question_text: string;
    options: string[];
    correct_answer_indices: number[];
    points: number;
    order: number;
}

interface Test {
    id: number;
    name: string;
    description: string;
    type: string;
    max_score: number;
    evaluation_criteria: string | null;
    questions: Question[];
}

const props = defineProps<{
    test: Test;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tests', href: '/tests' },
    { title: 'Edit', href: `/tests/${props.test.id}/edit` },
];

const form = useForm({
    name: props.test.name,
    description: props.test.description,
    type: props.test.type,
    max_score: props.test.max_score,
    evaluation_criteria: props.test.evaluation_criteria ?? '',
    questions: props.test.questions.map((q) => ({
        question_text: q.question_text,
        options: q.options,
        correct_answer_indices: q.correct_answer_indices,
        points: q.points,
        order: q.order,
    })),
});

const submit = () => {
    form.put(route('tests.update', props.test.id));
};

const handleTypeChange = (value: string) => {
    form.type = value;
    if (value === 'multiple_choice' && form.questions.length === 0) {
        form.questions.push({
            question_text: '',
            options: ['', ''],
            correct_answer_indices: [],
            points: 0,
            order: 1,
        });
    }
};
</script>

<template>
    <Head title="Edit Test" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Edit Test</h1>
                <Link :href="route('tests.show', test.id)">
                    <Button variant="outline">Cancel</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Test Details</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" type="text" v-model="form.name" required />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" rows="3" />
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="type">Type</Label>
                            <Select :model-value="form.type" @update:model-value="handleTypeChange">
                                <SelectTrigger id="type">
                                    <SelectValue placeholder="Select test type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="numeric">Numeric</SelectItem>
                                    <SelectItem value="text">Text</SelectItem>
                                    <SelectItem value="multiple_choice">Multiple Choice</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="form.errors.type" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="max_score">Max Score</Label>
                            <Input id="max_score" type="number" step="0.01" min="0" v-model="form.max_score" required />
                            <InputError class="mt-2" :message="form.errors.max_score" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="evaluation_criteria">Evaluation Criteria</Label>
                            <Textarea id="evaluation_criteria" v-model="form.evaluation_criteria" rows="3" />
                            <InputError class="mt-2" :message="form.errors.evaluation_criteria" />
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="form.type === 'multiple_choice'">
                    <CardHeader>
                        <CardTitle>Questions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <TestQuestionEditor v-model:questions="form.questions" />
                        <InputError class="mt-2" :message="form.errors.questions" />
                    </CardContent>
                </Card>

                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="form.processing">Save Changes</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
