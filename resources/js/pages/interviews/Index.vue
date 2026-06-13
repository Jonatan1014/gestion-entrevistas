<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import InterviewStatusBadge from '@/components/interview/InterviewStatusBadge.vue';
import InterviewTypeBadge from '@/components/interview/InterviewTypeBadge.vue';
import InputError from '@/components/InputError.vue';
import { ref } from 'vue';

interface Interviewer {
    id: number;
    name: string;
}

interface VacancyOption {
    id: number;
    position: string;
}

interface Interview {
    id: number;
    scheduled_at: string;
    type: string;
    status: string;
    applicant: { id: number; name: string };
    vacancy: { id: number; position: string };
    interviewer: { id: number; name: string };
}

const props = defineProps<{
    interviews: {
        data: Interview[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    interviewers: Interviewer[];
    vacancies: VacancyOption[];
    canManageInterviews: boolean;
    filters: {
        interviewer_id?: string;
        vacancy_id?: string;
        applicant_name?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Interviews', href: '/interviews' },
];

const updateFilters = () => {
    router.get('/interviews', {
        interviewer_id: props.filters.interviewer_id ?? '',
        vacancy_id: props.filters.vacancy_id ?? '',
        applicant_name: props.filters.applicant_name ?? '',
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const selectedInterview = ref<Interview | null>(null);
const completeDialogOpen = ref(false);
const cancelDialogOpen = ref(false);

const completeForm = useForm({
    observations: '',
});

const cancelForm = useForm({
    cancellation_reason: '',
});

const openCompleteDialog = (interview: Interview) => {
    selectedInterview.value = interview;
    completeForm.observations = '';
    completeDialogOpen.value = true;
};

const openCancelDialog = (interview: Interview) => {
    selectedInterview.value = interview;
    cancelForm.cancellation_reason = '';
    cancelDialogOpen.value = true;
};

const submitComplete = () => {
    if (!selectedInterview.value) return;

    completeForm.post(route('interviews.complete', selectedInterview.value.id), {
        onSuccess: () => {
            completeDialogOpen.value = false;
            selectedInterview.value = null;
        },
    });
};

const submitCancel = () => {
    if (!selectedInterview.value) return;

    cancelForm.post(route('interviews.cancel', selectedInterview.value.id), {
        onSuccess: () => {
            cancelDialogOpen.value = false;
            selectedInterview.value = null;
        },
    });
};
</script>

<template>
    <Head title="Interviews" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Interviews</h1>
                <Link :href="route('interviews.create')">
                    <Button>Schedule Interview</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Filter Interviews</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
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
                            <Label for="applicant_name">Applicant</Label>
                            <Input
                                id="applicant_name"
                                v-model="filters.applicant_name"
                                type="text"
                                placeholder="Search by applicant name..."
                                @input="updateFilters"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>All Interviews</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 font-medium">Applicant</th>
                                    <th class="px-4 py-3 font-medium">Vacancy</th>
                                    <th class="px-4 py-3 font-medium">Interviewer</th>
                                    <th class="px-4 py-3 font-medium">Scheduled At</th>
                                    <th class="px-4 py-3 font-medium">Type</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="interview in interviews.data" :key="interview.id" class="border-b hover:bg-muted/50">
                                    <td class="px-4 py-3">
                                        <Link :href="route('interviews.show', interview.id)" class="text-primary hover:underline">
                                            {{ interview.applicant.name }}
                                        </Link>
                                    </td>
                                    <td class="px-4 py-3">{{ interview.vacancy.position }}</td>
                                    <td class="px-4 py-3">{{ interview.interviewer.name }}</td>
                                    <td class="px-4 py-3">{{ interview.scheduled_at }}</td>
                                    <td class="px-4 py-3">
                                        <InterviewTypeBadge :type="interview.type" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <InterviewStatusBadge :status="interview.status" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <Link :href="route('interviews.show', interview.id)">
                                                <Button variant="outline" size="sm">View</Button>
                                            </Link>
                                            <template v-if="canManageInterviews && interview.status === 'pending'">
                                                <Button variant="outline" size="sm" @click="openCompleteDialog(interview)">Complete</Button>
                                                <Button variant="destructive" size="sm" @click="openCancelDialog(interview)">Cancel</Button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="interviews.data.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                        No interviews found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="interviews.links.length > 3" class="mt-4 flex justify-end gap-1">
                        <Link
                            v-for="link in interviews.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            :class="[
                                'rounded px-3 py-1 text-sm',
                                link.active ? 'bg-primary text-primary-foreground' : 'bg-muted hover:bg-muted/80',
                                link.url === null ? 'pointer-events-none opacity-50' : '',
                            ]"
                            preserve-state
                        >
                            <span v-html="link.label" />
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <Dialog v-model:open="completeDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Complete Interview</DialogTitle>
                        <DialogDescription>
                            Record observations for the interview with {{ selectedInterview?.applicant.name }}.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitComplete" class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="observations">Observations</Label>
                            <textarea
                                id="observations"
                                v-model="completeForm.observations"
                                class="min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                placeholder="Enter detailed observations..."
                                required
                            />
                            <InputError :message="completeForm.errors.observations" />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="completeDialogOpen = false">Cancel</Button>
                            <Button type="submit" :disabled="completeForm.processing">Complete</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="cancelDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Cancel Interview</DialogTitle>
                        <DialogDescription>
                            Provide a cancellation reason for the interview with {{ selectedInterview?.applicant.name }}.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitCancel" class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="cancellation_reason">Cancellation Reason</Label>
                            <textarea
                                id="cancellation_reason"
                                v-model="cancelForm.cancellation_reason"
                                class="min-h-[100px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                placeholder="Enter cancellation reason..."
                                required
                            />
                            <InputError :message="cancelForm.errors.cancellation_reason" />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="cancelDialogOpen = false">Cancel</Button>
                            <Button type="submit" variant="destructive" :disabled="cancelForm.processing">Confirm</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
