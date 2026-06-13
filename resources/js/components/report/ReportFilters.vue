<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { router } from '@inertiajs/vue3';

interface VacancyOption {
    id: number;
    position: string;
}

interface InterviewerOption {
    id: number;
    name: string;
}

const props = defineProps<{
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

const updateFilters = () => {
    const currentRoute = route().current() as string;
    router.get(route(currentRoute), {
        vacancy_id: props.filters.vacancy_id ?? '',
        date_from: props.filters.date_from ?? '',
        date_to: props.filters.date_to ?? '',
        interviewer_id: props.filters.interviewer_id ?? '',
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Filters</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="grid gap-2">
                    <Label for="vacancy_id">Vacancy</Label>
                    <Select v-model="filters.vacancy_id" @update:model-value="updateFilters">
                        <SelectTrigger id="vacancy_id">
                            <SelectValue placeholder="All vacancies" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">All vacancies</SelectItem>
                            <SelectItem v-for="vacancy in vacancies" :key="vacancy.id" :value="String(vacancy.id)">
                                {{ vacancy.position }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label for="date_from">Date From</Label>
                    <Input
                        id="date_from"
                        v-model="filters.date_from"
                        type="date"
                        @change="updateFilters"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="date_to">Date To</Label>
                    <Input
                        id="date_to"
                        v-model="filters.date_to"
                        type="date"
                        @change="updateFilters"
                    />
                </div>

                <div v-if="isAdmin" class="grid gap-2">
                    <Label for="interviewer_id">Interviewer</Label>
                    <Select v-model="filters.interviewer_id" @update:model-value="updateFilters">
                        <SelectTrigger id="interviewer_id">
                            <SelectValue placeholder="All interviewers" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">All interviewers</SelectItem>
                            <SelectItem v-for="interviewer in interviewers" :key="interviewer.id" :value="String(interviewer.id)">
                                {{ interviewer.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
