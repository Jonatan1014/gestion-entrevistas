<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface BreakdownItem {
    score: number;
    max_score: number;
    weight: number;
    normalized: number;
    contribution: number;
    test_name?: string;
}

interface WeightedAverage {
    score: number;
    meets_min_grade: boolean;
    breakdown: BreakdownItem[];
}

const props = defineProps<{
    weightedAverage: WeightedAverage;
    minGrade?: number | null;
}>();

const colorClass = (meets: boolean): string =>
    meets ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Weighted Average</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-3xl font-bold" :class="colorClass(weightedAverage.meets_min_grade)">
                        {{ weightedAverage.score.toFixed(2) }}
                    </p>
                    <p v-if="minGrade !== null && minGrade !== undefined" class="text-sm text-muted-foreground">
                        Minimum grade: {{ minGrade }}
                    </p>
                </div>
                <div
                    class="rounded-full px-3 py-1 text-sm font-medium"
                    :class="{
                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300': weightedAverage.meets_min_grade,
                        'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300': !weightedAverage.meets_min_grade,
                    }"
                >
                    {{ weightedAverage.meets_min_grade ? 'Meets requirement' : 'Below threshold' }}
                </div>
            </div>

            <div class="space-y-2">
                <p class="text-sm font-medium">Breakdown</p>
                <div
                    v-for="(item, index) in weightedAverage.breakdown"
                    :key="index"
                    class="flex items-center justify-between rounded-md border p-2 text-sm"
                >
                    <span class="text-muted-foreground">
                        {{ item.test_name ?? `Test ${index + 1}` }}:
                        {{ item.score }}/{{ item.max_score }} × {{ item.weight }}%
                    </span>
                    <span class="font-medium">{{ item.contribution.toFixed(2) }}</span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
