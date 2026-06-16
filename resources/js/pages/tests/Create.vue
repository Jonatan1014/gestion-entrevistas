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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tests', href: '/tests' },
    { title: 'Create', href: '/tests/create' },
];

const form = useForm({
    name: '',
    description: '',
    type: 'numeric',
    max_score: '' as string | number,
    evaluation_criteria: '',
    questions: [] as Array<{
        question_text: string;
        options: string[];
        correct_answer_indices: number[];
        points: string | number;
        order: number;
    }>,
});

const submit = () => {
    form.post(route('tests.store'));
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
    <Head title="Create Test" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Create Test</h1>
                <Link :href="route('tests.index')">
                    <Button variant="outline">Back to Tests</Button>
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
                            <Input id="name" type="text" v-model="form.name" required placeholder="e.g. Technical Score" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Describe the test purpose..." rows="3" />
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
                                    <SelectItem value="multiple_choice">Opción múltiple</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="form.errors.type" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="max_score">Max Score</Label>
                            <Input id="max_score" type="number" step="0.01" min="0" v-model="form.max_score" required placeholder="e.g. 100" />
                            <InputError class="mt-2" :message="form.errors.max_score" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="evaluation_criteria">Evaluation Criteria</Label>
                            <Textarea id="evaluation_criteria" v-model="form.evaluation_criteria" placeholder="Describe how this test is evaluated..." rows="3" />
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
                    <Button type="submit" :disabled="form.processing">Create Test</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
