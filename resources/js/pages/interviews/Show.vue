<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
import { ExternalLink, MapPin, CheckCircle, XCircle } from 'lucide-vue-next';
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
    { title: 'Entrevistas', href: '/interviews' },
    { title: `Entrevista #${props.interview.id}`, href: `/interviews/${props.interview.id}` },
];

const formatDateTime = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleString('es-AR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const completeDialogOpen = ref(false);
const cancelDialogOpen = ref(false);

const completeForm = useForm({
    observations: props.interview.observations ?? '',
    score: props.interview.score ?? '' as number | string,
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
    <Head title="Detalles de la entrevista" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold">Entrevista #{{ interview.id }}</h1>
                    <InterviewStatusBadge :status="interview.status" />
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('interviews.index')">Volver a entrevistas</Link>
                    </Button>
                    <template v-if="canManageInterviews && interview.status === 'pending'">
                        <Button variant="outline" @click="completeDialogOpen = true">
                            <CheckCircle class="mr-1 h-4 w-4" />
                            Completar
                        </Button>
                        <Button variant="destructive" @click="cancelDialogOpen = true">
                            <XCircle class="mr-1 h-4 w-4" />
                            Cancelar
                        </Button>
                    </template>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Detalles de la entrevista</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Postulante</p>
                            <p class="text-sm font-medium">{{ interview.applicant.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Vacante</p>
                            <p class="text-sm">{{ interview.vacancy.position }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Entrevistador</p>
                            <p class="text-sm">{{ interview.interviewer.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Programada para</p>
                            <p class="text-sm font-medium">{{ formatDateTime(interview.scheduled_at) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Modalidad</p>
                            <p class="text-sm">
                                <InterviewTypeBadge :type="interview.type" />
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Completada el</p>
                            <p class="text-sm">{{ formatDateTime(interview.completed_at) }}</p>
                        </div>
                        <div v-if="interview.score">
                            <p class="text-sm font-medium text-muted-foreground">Nota de entrevista</p>
                            <p class="text-sm font-bold text-lg">{{ interview.score }}/10</p>
                        </div>
                    </div>

                    <Separator />

                    <div v-if="interview.type === 'virtual' && interview.link" class="rounded-lg border p-3">
                        <p class="text-sm font-medium text-muted-foreground mb-1">Enlace de la reunión</p>
                        <a :href="interview.link" target="_blank" class="inline-flex items-center gap-1 text-sm text-primary hover:underline">
                            <ExternalLink class="h-3.5 w-3.5" />
                            {{ interview.link }}
                        </a>
                    </div>

                    <div v-if="interview.type === 'presencial' && interview.location_notes" class="rounded-lg border p-3">
                        <p class="text-sm font-medium text-muted-foreground mb-1">
                            <MapPin class="inline h-3.5 w-3.5 mr-1" />
                            Indicaciones del lugar
                        </p>
                        <p class="text-sm whitespace-pre-line">{{ interview.location_notes }}</p>
                    </div>

                    <div v-if="interview.observations" class="rounded-lg border p-3">
                        <p class="text-sm font-medium text-muted-foreground mb-1">Observaciones</p>
                        <p class="text-sm whitespace-pre-line">{{ interview.observations }}</p>
                    </div>

                    <div v-if="interview.cancellation_reason" class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-950">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-1">Motivo de cancelación</p>
                        <p class="text-sm text-red-700 dark:text-red-400 whitespace-pre-line">{{ interview.cancellation_reason }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Complete Dialog -->
            <Dialog v-model:open="completeDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Completar entrevista</DialogTitle>
                        <DialogDescription>
                            Registrá las observaciones para finalizar esta entrevista.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitComplete" class="space-y-4" novalidate>
                        <div class="grid gap-2">
                            <Label for="score">Nota de entrevista (1-10)</Label>
                            <Input
                                id="score"
                                v-model="completeForm.score"
                                type="number"
                                min="1"
                                max="10"
                                placeholder="1-10"
                                required
                                class="w-24 text-center text-lg font-semibold"
                            />
                            <InputError :message="completeForm.errors.score" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="observations">Observaciones</Label>
                            <Textarea
                                id="observations"
                                v-model="completeForm.observations"
                                rows="4"
                                placeholder="Registrá observaciones detalladas…"
                                required
                            />
                            <InputError :message="completeForm.errors.observations" />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="completeDialogOpen = false">Cancelar</Button>
                            <Button type="submit" :disabled="completeForm.processing">Completar</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Cancel Dialog -->
            <Dialog v-model:open="cancelDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Cancelar entrevista</DialogTitle>
                        <DialogDescription>
                            Indicá el motivo de la cancelación.
                        </DialogDescription>
                    </DialogHeader>

                    <form @submit.prevent="submitCancel" class="space-y-4" novalidate>
                        <div class="grid gap-2">
                            <Label for="cancellation_reason">Motivo de cancelación</Label>
                            <Textarea
                                id="cancellation_reason"
                                v-model="cancelForm.cancellation_reason"
                                rows="3"
                                placeholder="Describí el motivo…"
                                required
                            />
                            <InputError :message="cancelForm.errors.cancellation_reason" />
                        </div>

                        <DialogFooter>
                            <Button type="button" variant="outline" @click="cancelDialogOpen = false">Cancelar</Button>
                            <Button type="submit" variant="destructive" :disabled="cancelForm.processing">Confirmar cancelación</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
