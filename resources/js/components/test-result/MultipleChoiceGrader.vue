<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';

interface Question {
    id: number;
    question_text: string;
    options: string[];
    correct_answer_indices: number[];
    points: number;
}

const props = defineProps<{
    questions: Question[];
    modelValue: Record<number, number[]>;
    readOnly?: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: Record<number, number[]>): void;
}>();

const isCorrect = (question: Question): boolean => {
    const selected = [...(props.modelValue[question.id] ?? [])].sort((a, b) => a - b);
    const correct = [...(question.correct_answer_indices ?? [])].sort((a, b) => a - b);

    return JSON.stringify(selected) === JSON.stringify(correct);
};

const toggleOption = (questionId: number, index: number, isMultiple: boolean) => {
    if (props.readOnly) return;

    const current = props.modelValue[questionId] ?? [];

    if (isMultiple) {
        const updated = current.includes(index)
            ? current.filter((i) => i !== index)
            : [...current, index];
        emit('update:modelValue', { ...props.modelValue, [questionId]: updated });
    } else {
        emit('update:modelValue', { ...props.modelValue, [questionId]: [index] });
    }
};
</script>

<template>
    <div class="space-y-6">
        <div
            v-for="question in questions"
            :key="question.id"
            class="rounded-lg border p-4"
            :class="{
                'border-green-500 bg-green-50 dark:bg-green-950/20': readOnly && isCorrect(question),
                'border-red-500 bg-red-50 dark:bg-red-950/20': readOnly && !isCorrect(question),
            }"
        >
            <div class="mb-3 flex items-start justify-between gap-4">
                <p class="font-medium">{{ question.question_text }}</p>
                <Badge v-if="readOnly" :variant="isCorrect(question) ? 'default' : 'destructive'">
                    {{ isCorrect(question) ? 'Correct' : 'Incorrect' }}
                </Badge>
            </div>

            <div class="space-y-2">
                <template v-if="question.correct_answer_indices.length > 1">
                    <div
                        v-for="(option, index) in question.options"
                        :key="`${question.id}-${index}`"
                        class="flex items-center gap-2"
                    >
                        <Checkbox
                            :id="`q-${question.id}-opt-${index}`"
                            :checked="modelValue[question.id]?.includes(index) ?? false"
                            :disabled="readOnly"
                            @update:checked="() => toggleOption(question.id, index, true)"
                        />
                        <Label :for="`q-${question.id}-opt-${index}`" class="cursor-pointer font-normal">
                            {{ option }}
                        </Label>
                    </div>
                </template>

                <template v-else>
                    <div
                        v-for="(option, index) in question.options"
                        :key="`${question.id}-${index}`"
                        class="flex items-center gap-2"
                    >
                        <input
                            :id="`q-${question.id}-opt-${index}`"
                            type="radio"
                            :name="`question-${question.id}`"
                            :value="index"
                            :checked="modelValue[question.id]?.[0] === index"
                            :disabled="readOnly"
                            class="h-4 w-4 border-primary text-primary focus:ring-primary"
                            @change="toggleOption(question.id, index, false)"
                        />
                        <Label :for="`q-${question.id}-opt-${index}`" class="cursor-pointer font-normal">
                            {{ option }}
                        </Label>
                    </div>
                </template>
            </div>

            <p class="mt-2 text-sm text-muted-foreground">{{ question.points }} points</p>
        </div>
    </div>
</template>
