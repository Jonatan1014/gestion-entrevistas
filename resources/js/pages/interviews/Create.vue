<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import { Calendar, Clock, Link2, Video, MapPin } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Option {
    id: number;
    name: string;
}

interface VacancyOption {
    id: number;
    position: string;
    location: string;
}

const props = defineProps<{
    vacancies: VacancyOption[];
    vacancyApplicants: Record<string, Array<{ id: number; name: string; email: string }>>;
    interviewers: Option[];
    isAdmin: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Entrevistas', href: '/interviews' },
    { title: 'Programar', href: '/interviews/create' },
];

const interviewDate = ref('');
const interviewTime = ref('');
const dateInput = ref<HTMLInputElement>();
const timeInput = ref<HTMLInputElement>();

const vacancyOptions = computed(() => props.vacancies.map((v) => ({ id: v.id, name: v.position, sub: v.location })));
const interviewerOptions = computed(() => props.interviewers.map((i) => ({ id: i.id, name: i.name })));

// Get applicants for the selected vacancy from the preloaded map
const applicantOptions = computed(() => {
    if (!form.vacancy_id) return [];
    const list = props.vacancyApplicants[form.vacancy_id] ?? [];
    return list.map((a) => ({ id: a.id, name: a.name, sub: a.email }));
});

const form = useForm({
    vacancy_id: '',
    applicant_id: '',
    interviewer_id: props.isAdmin ? '' : String(props.interviewers[0]?.id ?? ''),
    scheduled_at: '',
    type: 'virtual',
    link: '',
    location_notes: '',
});

const dateLabel = computed(() => {
    if (!interviewDate.value) return null;
    return new Date(interviewDate.value + 'T00:00:00').toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long' });
});

const timeLabel = computed(() => {
    if (!interviewTime.value) return null;
    const [h, m] = interviewTime.value.split(':').map(Number);
    const period = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${String(m).padStart(2, '0')} ${period}`;
});

const nowLabel = computed(() => {
    return new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
});

const submit = () => {
    if (!interviewDate.value || !interviewTime.value) return;

    const now = new Date();
    const selected = new Date(interviewDate.value + 'T' + interviewTime.value + ':00');

    if (isNaN(selected.getTime())) {
        form.errors.scheduled_at = 'Fecha u hora inválida.';
        return;
    }

    if (selected.getTime() <= now.getTime()) {
        const nowStr = now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        const selStr = selected.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', hour12: true });
        form.errors.scheduled_at = `Hora seleccionada (${selStr}) no es posterior a la actual (${nowStr}).`;
        return;
    }

    form.clearErrors();
    form.scheduled_at = interviewDate.value + ' ' + interviewTime.value + ':00';
    form.post(route('interviews.store'));
};
</script>

<template>
    <Head title="Programar entrevista" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full w-full max-w-lg flex-1 flex-col gap-8 rounded-xl p-4 py-8">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Programar entrevista</h1>
                <p class="mt-1 text-sm text-muted-foreground">Completá los datos para agendar una nueva entrevista.</p>
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-8" novalidate>
                <!-- Vacancy -->
                <div class="space-y-1.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Vacante</p>
                    <SearchableSelect v-model="form.vacancy_id" :options="vacancyOptions" placeholder="Buscar vacante…" />
                    <InputError :message="form.errors.vacancy_id" />
                </div>

                <!-- Applicant -->
                <div class="space-y-1.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Postulante</p>
                    <SearchableSelect
                        v-if="form.vacancy_id"
                        v-model="form.applicant_id"
                        :options="applicantOptions"
                        placeholder="Buscar postulante…"
                    />
                    <p v-else class="text-sm text-muted-foreground">Primero seleccioná una vacante.</p>
                    <InputError :message="form.errors.applicant_id" />
                </div>

                <!-- Interviewer -->
                <div v-if="isAdmin" class="space-y-1.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Entrevistador</p>
                    <SearchableSelect v-model="form.interviewer_id" :options="interviewerOptions" placeholder="Buscar entrevistador…" />
                    <InputError :message="form.errors.interviewer_id" />
                </div>

                <!-- Date + Time -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Fecha y hora</p>
                        <p class="text-[10px] text-muted-foreground">Hora actual: {{ nowLabel }}</p>
                    </div>
                    <div class="flex items-stretch gap-px overflow-hidden rounded-lg border border-input bg-muted/30">
                        <button
                            type="button"
                            class="flex flex-1 items-center gap-2.5 bg-background px-4 py-3 transition-colors hover:bg-muted/30"
                            @click="(dateInput as HTMLInputElement)?.showPicker()"
                        >
                            <Calendar class="size-4 shrink-0 text-muted-foreground" />
                            <span v-if="dateLabel" class="text-sm">{{ dateLabel }}</span>
                            <span v-else class="text-sm text-muted-foreground">Fecha</span>
                        </button>
                        <button
                            type="button"
                            class="flex items-center gap-2.5 bg-background px-4 py-3 transition-colors hover:bg-muted/30"
                            @click="(timeInput as HTMLInputElement)?.showPicker()"
                        >
                            <Clock class="size-4 shrink-0 text-muted-foreground" />
                            <span v-if="timeLabel" class="text-sm tabular-nums">{{ timeLabel }}</span>
                            <span v-else class="text-sm text-muted-foreground">Hora</span>
                        </button>
                    </div>
                    <input ref="dateInput" type="date" v-model="interviewDate" required :min="new Date().toISOString().split('T')[0]" class="sr-only" tabindex="-1" />
                    <input ref="timeInput" type="time" v-model="interviewTime" required class="sr-only" tabindex="-1" />
                    <InputError :message="form.errors.scheduled_at" />
                </div>

                <!-- Type -->
                <div class="space-y-2">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Modalidad</p>
                    <div class="flex rounded-lg border border-input bg-muted/30 p-0.5">
                        <button
                            type="button"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium transition-all"
                            :class="form.type === 'virtual' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                            @click="form.type = 'virtual'"
                        >
                            <Video class="size-3.5" />
                            Virtual
                        </button>
                        <button
                            type="button"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium transition-all"
                            :class="form.type === 'presencial' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                            @click="form.type = 'presencial'"
                        >
                            <MapPin class="size-3.5" />
                            Presencial
                        </button>
                    </div>
                    <InputError :message="form.errors.type" />
                </div>

                <!-- Link (virtual) -->
                <div v-if="form.type === 'virtual'" class="space-y-1.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Enlace</p>
                    <div class="relative">
                        <Link2 class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="form.link"
                            type="url"
                            placeholder="https://meet.google.com/abc-defg"
                            class="pl-10"
                        />
                    </div>
                    <InputError :message="form.errors.link" />
                </div>

                <!-- Location (presencial) -->
                <div v-if="form.type === 'presencial'" class="space-y-1.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Indicaciones</p>
                    <Textarea v-model="form.location_notes" rows="2" placeholder="Sala de reuniones, 3er piso…" />
                    <InputError :message="form.errors.location_notes" />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between border-t border-input pt-6">
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="route('interviews.index')">Cancelar</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing" size="sm">
                        {{ form.processing ? 'Programando…' : 'Programar entrevista' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
