<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
import InputError from '@/components/InputError.vue';

interface Option {
    id: number;
    name: string;
}

interface VacancyOption {
    id: number;
    position: string;
}

const props = defineProps<{
    applicants: Option[];
    vacancies: VacancyOption[];
    interviewers: Option[];
    isAdmin: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Interviews', href: '/interviews' },
    { title: 'Create', href: '/interviews/create' },
];

const form = useForm({
    vacancy_id: '',
    applicant_id: '',
    interviewer_id: props.isAdmin ? '' : String(props.interviewers[0]?.id ?? ''),
    scheduled_at: '',
    type: 'virtual',
    link: '',
    location_notes: '',
});

const submit = () => {
    form.post(route('interviews.store'));
};
</script>

<template>
    <Head title="Schedule Interview" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Schedule Interview</h1>
                <Link :href="route('interviews.index')">
                    <Button variant="outline">Back to Interviews</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Interview Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="vacancy_id">Vacancy</Label>
                            <Select v-model="form.vacancy_id" required>
                                <SelectTrigger id="vacancy_id">
                                    <SelectValue placeholder="Select a vacancy" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="vacancy in vacancies" :key="vacancy.id" :value="String(vacancy.id)">
                                        {{ vacancy.position }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.vacancy_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="applicant_id">Applicant</Label>
                            <Select v-model="form.applicant_id" required>
                                <SelectTrigger id="applicant_id">
                                    <SelectValue placeholder="Select an applicant" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="applicant in applicants" :key="applicant.id" :value="String(applicant.id)">
                                        {{ applicant.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.applicant_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="interviewer_id">Interviewer</Label>
                            <Select v-model="form.interviewer_id" :disabled="!isAdmin" required>
                                <SelectTrigger id="interviewer_id">
                                    <SelectValue placeholder="Select an interviewer" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="interviewer in interviewers" :key="interviewer.id" :value="String(interviewer.id)">
                                        {{ interviewer.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.interviewer_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="scheduled_at">Scheduled At</Label>
                            <Input id="scheduled_at" type="datetime-local" v-model="form.scheduled_at" required />
                            <InputError :message="form.errors.scheduled_at" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="type">Type</Label>
                            <Select v-model="form.type" required>
                                <SelectTrigger id="type">
                                    <SelectValue placeholder="Select interview type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="virtual">Virtual</SelectItem>
                                    <SelectItem value="presencial">Presencial</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.type" />
                        </div>

                        <div v-if="form.type === 'virtual'" class="grid gap-2">
                            <Label for="link">Meeting Link</Label>
                            <Input id="link" type="url" v-model="form.link" placeholder="https://meet.example/abc" />
                            <InputError :message="form.errors.link" />
                        </div>

                        <div v-if="form.type === 'presencial'" class="grid gap-2">
                            <Label for="location_notes">Location Notes</Label>
                            <textarea
                                id="location_notes"
                                v-model="form.location_notes"
                                class="min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                placeholder="Room, building, instructions..."
                            />
                            <InputError :message="form.errors.location_notes" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">Schedule Interview</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
