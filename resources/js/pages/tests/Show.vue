<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';

interface Question {
    id: number;
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
    canManageTests: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tests', href: '/tests' },
    { title: props.test.name, href: `/tests/${props.test.id}` },
];

const typeLabel = (type: string) => {
    return {
        numeric: 'Numérica',
        text: 'Texto',
        multiple_choice: 'Opción múltiple',
    }[type] ?? type;
};

const optionLabel = (index: number) => String.fromCharCode(65 + index);
</script>

<template>
    <Head :title="test.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold">{{ test.name }}</h1>
                    <Badge variant="outline">{{ typeLabel(test.type) }}</Badge>
                </div>
                <div v-if="canManageTests" class="flex items-center gap-2">
                    <Link :href="route('tests.edit', test.id)">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Link :href="route('tests.destroy', test.id)" method="delete">
                        <Button variant="destructive">Delete</Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Test Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Type</p>
                            <p class="text-sm">{{ typeLabel(test.type) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Max Score</p>
                            <p class="text-sm">{{ test.max_score }}</p>
                        </div>
                    </div>
                    <Separator />
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-2">Description</p>
                        <p class="text-sm whitespace-pre-line">{{ test.description }}</p>
                    </div>
                    <div v-if="test.evaluation_criteria">
                        <p class="text-sm font-medium text-muted-foreground mb-2">Evaluation Criteria</p>
                        <p class="text-sm whitespace-pre-line">{{ test.evaluation_criteria }}</p>
                    </div>
                </CardContent>
            </Card>

            <Card v-if="test.type === 'multiple_choice'">
                <CardHeader>
                    <CardTitle>Questions</CardTitle>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div v-if="test.questions.length === 0" class="text-sm text-muted-foreground">
                        No questions configured.
                    </div>
                    <div v-for="question in test.questions" :key="question.id" class="space-y-2">
                        <p class="font-medium">{{ question.order }}. {{ question.question_text }}</p>
                        <div class="grid gap-1 pl-4">
                            <p v-for="(option, index) in question.options" :key="index" class="text-sm">
                                {{ optionLabel(index) }}. {{ option }}
                                <span v-if="question.correct_answer_indices.includes(index)" class="text-green-600">(correct)</span>
                            </p>
                        </div>
                        <p class="text-sm text-muted-foreground">Points: {{ question.points }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
