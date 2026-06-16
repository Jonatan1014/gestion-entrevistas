<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
import { CheckCircle, XCircle, Eye } from 'lucide-vue-next';
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
    { title: 'Entrevistas', href: '/interviews' },
];

const formatDateTime = (date: string) => {
    return new Date(date).toLocaleString('es-AR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

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
    score: '' as number | string,
});

const cancelForm = useForm({
    cancellation_reason: '',
});

const openCompleteDialog = (interview: Interview) => {
    selectedInterview.value = interview;
    completeForm.observations = '';
    completeForm.score = '';
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
    <Head title="Entrevistas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Entrevistas</h1>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('interviews.calendar')">Calendario</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="route('interviews.create')">Programar entrevista</Link>
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Filtrar entrevistas</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="interviewer_id">Entrevistador</Label>
                            <Select v-model="filters.interviewer_id" @update:model-value="updateFilters">
                                <SelectTrigger id="interviewer_id">
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todos</SelectItem>
                                    <SelectItem v-for="i in interviewers" :key="i.id" :value="String(i.id)">
                                        {{ i.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid gap-2">
                            <Label for="vacancy_id">Vacante</Label>
                            <Select v-model="filters.vacancy_id" @update:model-value="updateFilters">
                                <SelectTrigger id="vacancy_id">
                                    <SelectValue placeholder="Todas" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Todas</SelectItem>
                                    <SelectItem v-for="v in vacancies" :key="v.id" :value="String(v.id)">
                                        {{ v.position }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="grid gap-2">
                            <Label for="applicant_name">Postulante</Label>
                            <Input
                                id="applicant_name"
                                v-model="filters.applicant_name"
                                type="text"
                                placeholder="Buscar por nombre…"
                                @input="updateFilters"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Todas las entrevistas</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 font-medium">Postulante</th>
                                    <th class="px-4 py-3 font-medium">Vacante</th>
                                    <th class="px-4 py-3 font-medium">Entrevistador</th>
                                    <th class="px-4 py-3 font-medium">Programada</th>
                                    <th class="px-4 py-3 font-medium">Tipo</th>
                                    <th class="px-4 py-3 font-medium">Estado</th>
                                    <th class="px-4 py-3 font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="interview in interviews.data" :key="interview.id" class="border-b hover:bg-muted/50">
                                    <td class="px-4 py-3">
                                        <Link :href="route('interviews.show', interview.id)" class="text-primary hover:underline font-medium">
                                            {{ interview.applicant.name }}
                                        </Link>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ interview.vacancy.position }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ interview.interviewer.name }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ formatDateTime(interview.scheduled_at) }}</td>
                                    <td class="px-4 py-3">
                                        <InterviewTypeBadge :type="interview.type" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <InterviewStatusBadge :status="interview.status" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <Button as-child variant="outline" size="sm">
                                                <Link :href="route('interviews.show', interview.id)">
                                                    <Eye class="mr-1 h-3.5 w-3.5" />
                                                    Ver
                                                </Link>
                                            </Button>
                                            <template v-if="canManageInterviews && interview.status === 'pending'">
                                                <Button variant="outline" size="sm" @click="openCompleteDialog(interview)">
                                                    <CheckCircle class="mr-1 h-3.5 w-3.5" />
                                                    Completar
                                                </Button>
                                                <Button variant="destructive" size="sm" @click="openCancelDialog(interview)">
                                                    <XCircle class="mr-1 h-3.5 w-3.5" />
                                                    Cancelar
                                                </Button>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="interviews.data.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                        No hay entrevistas registradas.
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

            <!-- Complete Dialog -->
            <Dialog v-model:open="completeDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Completar entrevista</DialogTitle>
                        <DialogDescription>
                            Registrá las observaciones de la entrevista con {{ selectedInterview?.applicant.name }}.
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
                            Indicá el motivo de cancelación para la entrevista con {{ selectedInterview?.applicant.name }}.
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
