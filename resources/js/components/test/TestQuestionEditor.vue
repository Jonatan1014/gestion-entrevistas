<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';

interface Question {
    question_text: string;
    options: string[];
    correct_answer_indices: number[];
    points: string | number;
    order: number;
}

const questions = defineModel<Question[]>('questions', {
    default: () => [],
});

const addQuestion = () => {
    questions.value.push({
        question_text: '',
        options: ['', ''],
        correct_answer_indices: [],
        points: 0,
        order: questions.value.length + 1,
    });
};

const removeQuestion = (index: number) => {
    questions.value.splice(index, 1);
    questions.value.forEach((q, i) => (q.order = i + 1));
};

const addOption = (questionIndex: number) => {
    questions.value[questionIndex].options.push('');
};

const removeOption = (questionIndex: number, optionIndex: number) => {
    const question = questions.value[questionIndex];
    question.options.splice(optionIndex, 1);
    question.correct_answer_indices = question.correct_answer_indices
        .filter((i) => i !== optionIndex)
        .map((i) => (i > optionIndex ? i - 1 : i));
};

const toggleCorrect = (questionIndex: number, optionIndex: number) => {
    const question = questions.value[questionIndex];
    const current = question.correct_answer_indices;
    const position = current.indexOf(optionIndex);

    if (position === -1) {
        current.push(optionIndex);
    } else {
        current.splice(position, 1);
    }

    current.sort((a, b) => a - b);
};

const saveQuestion = (index: number) => {
    // In this implementation changes are synchronized automatically via v-model.
    // The save button provides explicit UX feedback and can be extended later.
    questions.value[index].order = index + 1;
};
</script>

<template>
    <div class="space-y-4">
        <Card v-for="(question, index) in questions" :key="index">
            <CardHeader>
                <CardTitle class="text-base">Question {{ question.order }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-2">
                    <Label :for="`question-text-${index}`">Question Text</Label>
                    <Textarea :id="`question-text-${index}`" v-model="question.question_text" placeholder="Enter the question..." rows="2" />
                </div>

                <div class="grid gap-2">
                    <Label>Options</Label>
                    <div v-for="(option, optionIndex) in question.options" :key="optionIndex" class="flex items-center gap-2">
                        <Checkbox
                            :id="`correct-${index}-${optionIndex}`"
                            :checked="question.correct_answer_indices.includes(optionIndex)"
                            @update:checked="toggleCorrect(index, optionIndex)"
                        />
                        <Label :for="`correct-${index}-${optionIndex}`" class="sr-only">Correct</Label>
                        <Input v-model="question.options[optionIndex]" type="text" :placeholder="`Option ${String.fromCharCode(65 + optionIndex)}`" />
                        <Button type="button" variant="outline" size="sm" @click="removeOption(index, optionIndex)">Remove</Button>
                    </div>
                    <Button type="button" variant="outline" size="sm" class="w-fit" @click="addOption(index)">Add Option</Button>
                </div>

                <div class="grid gap-2">
                    <Label :for="`points-${index}`">Points</Label>
                    <Input :id="`points-${index}`" v-model="question.points" type="number" step="0.01" min="0" />
                </div>
            </CardContent>
            <CardFooter class="flex justify-between">
                <Button type="button" variant="outline" size="sm" @click="saveQuestion(index)">Save Question</Button>
                <Button type="button" variant="destructive" size="sm" @click="removeQuestion(index)">Delete Question</Button>
            </CardFooter>
        </Card>

        <Button type="button" variant="outline" @click="addQuestion">Add New Question</Button>
    </div>
</template>
