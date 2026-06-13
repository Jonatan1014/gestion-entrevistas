<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
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

interface Interview {
    id: number;
    scheduled_at: string;
    type: string;
    status: string;
    link: string | null;
    location_notes: string | null;
    observations: string | null;
    cancellation_reason: string | null;
    completed_at: string | null;
    applicant: { id: number; name: string };
    vacancy: { id: number; position: string };
    interviewer: { id: number; name: string };
}

const props = defineProps<{
    interview: Interview;
    canManageInterviews: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Interviews', href: '/interviews' },
    { title: `Interview #${props.interview.id}`, href: `/interviews/${props.interview.id}` },
];

const completeDialogOpen = ref(false);
const cancelDialogOpen = ref(false);

const completeForm = useForm({
    observations: props.interview.observations ?? '',
});

const cancelForm = useForm({
    cancellation_reason: '',
});

const submitComplete = () => {
    completeForm.post(route('interviews.complete', props.interview.id), {
        onSuccess: () => {
            completeDialogOpen.value = false;
        },
    });
};

const submitCancel = () => {
    cancelForm.post(route('interviews.cancel', props.interview.id), {
        onSuccess: () => {
            cancelDialogOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Interview Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold">Interview #{{ interview.id }}</h1>
                    <InterviewStatusBadge :status="interview.status" />
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('interviews.index')">
                        <Button variant="outline">Back to Interviews</Button>
                    </Link>
                    <template v-if="canManageInterviews && interview.status === 'pending'">
                        <Button variant="outline" @click="completeDialogOpen = true">Complete</Button>
                        <Button variant="destructive" @click="cancelDialogOpen = true">Cancel</Button>
                    </template>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Interview Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Applicant</p>
                            <p class="text-sm">{{ interview.applicant.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Vacancy</p>
                            <p class="text-sm">{{ interview.vacancy.position }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Interviewer</p>
                            <p class="text-sm">{{ interview.interviewer.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Scheduled At</p>
                            <p class="text-sm">{{ interview.scheduled_at }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Type</p>
                            <p class="text-sm">
                                <InterviewTypeBadge :type="interview.type" />
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Completed At</p>
                            <p class="text-sm">{{ interview.completed_at ?? '—' }}</p>
                        </div>
                    </div>

                    <Separator />

                    <div v-if="interview.type === 'virtual' && interview.link">
                        <p class="text-sm font-medium text-muted-foreground mb-1">Meeting Link</p>
                        <a :href="interview.link" target="_blank" class="text-sm text-primary hover:underline">{{ interview.link }}</a>
                    </div>

                    <div v-if="interview.type === 'presencial' && interview.location_notes">
                        <p class="text-sm font-medium text-muted-foreground mb-1">Location Notes</p>
                        <p class="text-sm whitespace-pre-line">{{ interview.location_notes }}</p>
                    </div>

                    <div v-if="interview.observations">
                        <p class="text-sm font-medium text-muted-foreground mb-1">Observations</p>
                        <p class="text-sm whitespace-pre-line">{{ interview.observations }}</p>
                    </div>

                    <div v-if="interview.cancellation_reason">
                        <p class="text-sm font-medium text-muted-foreground mb-1">Cancellation Reason</p>
                        <p class="text-sm whitespace-pre-line">{{ interview.cancellation_reason }}</p>
                    </div>
                </CardContent>
            </Card>

            <Dialog v-model:open="completeDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Complete Interview</DialogTitle>
                        <DialogDescription>
                            Record observations to complete this interview.
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
                            Provide a reason for cancelling this interview.
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
