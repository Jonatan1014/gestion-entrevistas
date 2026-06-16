<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import { Target, User, ClipboardCheck, History, Plus } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

interface Vacancy {
    id: number;
    position: string;
    location: string;
}

interface Applicant {
    id: number;
    name: string;
    email: string;
}

interface Test {
    id: number;
    name: string;
    type: string;
    max_score: number;
}

interface ScoreRecord {
    id: number;
    score: number;
    observations: string | null;
    evaluator_name: string;
    created_at: string;
}

const props = defineProps<{
    vacancies: Vacancy[];
    preselected: {
        vacancy_id: string | null;
        applicant_id: string | null;
        test_id: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Registrar puntaje', href: '/quick-score' },
];

const vacancyOptions = computed(() => props.vacancies.map(v => ({ id: v.id, name: v.position, sub: v.location })));

const form = useForm({
    vacancy_id: props.preselected.vacancy_id ?? '',
    applicant_id: props.preselected.applicant_id ?? '',
    test_id: props.preselected.test_id ?? '',
    score: '',
    observations: '',
});

const applicants = ref<Applicant[]>([]);
const tests = ref<Test[]>([]);
const history = ref<ScoreRecord[]>([]);
const loadingApplicants = ref(false);
const loadingTests = ref(false);

// Fetch applicants when vacancy changes
watch(() => form.vacancy_id, async (id) => {
    form.applicant_id = '';
    form.test_id = '';
    applicants.value = [];
    tests.value = [];
    history.value = [];
    if (!id) return;
    loadingApplicants.value = true;
    try {
        const { data } = await axios.get(`/api/vacancies/${id}/applicants`);
        applicants.value = data;
    } finally {
        loadingApplicants.value = false;
    }
}, { immediate: true });

// Fetch tests when vacancy changes
watch(() => form.vacancy_id, async (id) => {
    tests.value = [];
    history.value = [];
    if (!id) return;
    loadingTests.value = true;
    try {
        const { data } = await axios.get(`/api/vacancies/${id}/tests`);
        tests.value = data;
    } finally {
        loadingTests.value = false;
    }
});

// Fetch history when all three are selected
watch([() => form.vacancy_id, () => form.applicant_id, () => form.test_id], async ([v, a, t]) => {
    history.value = [];
    if (!v || !a || !t) return;
    try {
        const { data } = await axios.get(`/api/vacancies/${v}/applicants/${a}/tests/${t}/history`);
        history.value = data;
    } catch {}
});

const applicantOpts = computed(() => applicants.value.map(a => ({ id: a.id, name: a.name, sub: a.email })));
const testOpts = computed(() => tests.value.map(t => ({ id: t.id, name: t.name, sub: `Máx: ${t.max_score}` })));

const selectedTest = computed(() => tests.value.find(t => String(t.id) === form.test_id));

const submit = () => {
    form.post(route('quick-score.store'), {
        onSuccess: () => {
            form.reset('score', 'observations');
            // Refresh history
        },
    });
};
</script>

<template>
    <Head title="Registrar puntaje" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full w-full max-w-lg flex-1 flex-col gap-8 rounded-xl p-4 py-8">
            <div>
                <h1 class="text-xl font-semibold tracking-tight">Registrar puntaje</h1>
                <p class="mt-1 text-sm text-muted-foreground">Seleccioná vacante, postulante y prueba para registrar un puntaje.</p>
            </div>

            <!-- Step 1: Vacancy -->
            <div class="space-y-1.5">
                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Vacante</p>
                <SearchableSelect v-model="form.vacancy_id" :options="vacancyOptions" placeholder="Buscar vacante…" />
            </div>

            <!-- Step 2: Applicant -->
            <div v-if="form.vacancy_id" class="space-y-1.5">
                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Postulante</p>
                <SearchableSelect v-model="form.applicant_id" :options="applicantOpts" placeholder="Buscar postulante…" />
                <p v-if="loadingApplicants" class="text-xs text-muted-foreground">Cargando…</p>
                <p v-else-if="applicants.length === 0" class="text-xs text-muted-foreground">No hay postulantes en esta vacante.</p>
            </div>

            <!-- Step 3: Test -->
            <div v-if="form.applicant_id" class="space-y-1.5">
                <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Prueba</p>
                <SearchableSelect v-model="form.test_id" :options="testOpts" placeholder="Buscar prueba…" />
                <p v-if="loadingTests" class="text-xs text-muted-foreground">Cargando…</p>
                <p v-else-if="tests.length === 0" class="text-xs text-muted-foreground">No hay pruebas asociadas a esta vacante.</p>
            </div>

            <!-- Step 4: Score -->
            <div v-if="form.test_id" class="space-y-4 border-t border-input pt-6">
                <!-- History -->
                <div v-if="history.length > 0" class="space-y-2">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Intentos anteriores</p>
                    <div class="space-y-1.5">
                        <div v-for="h in history" :key="h.id" class="flex items-center justify-between rounded-md bg-muted/30 px-3 py-2 text-sm">
                            <div>
                                <span class="font-medium tabular-nums">{{ h.score }}</span>
                                <span class="text-muted-foreground"> / {{ selectedTest?.max_score }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ h.evaluator_name }} · {{ h.created_at }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New score -->
                <div class="space-y-1.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Nuevo puntaje</p>
                    <div class="flex items-center gap-3">
                        <Input
                            v-model="form.score"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            class="w-24 text-center text-lg font-semibold tabular-nums"
                            :max="selectedTest?.max_score"
                            min="0"
                        />
                        <span class="text-sm text-muted-foreground">/ {{ selectedTest?.max_score ?? '—' }}</span>
                    </div>
                    <InputError :message="form.errors.score" />
                </div>

                <div class="space-y-1.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Observaciones</p>
                    <Textarea v-model="form.observations" rows="2" placeholder="Opcional…" />
                    <InputError :message="form.errors.observations" />
                </div>

                <Button type="button" class="w-full bg-[#51eead] text-black hover:bg-[#3ddb94] font-semibold" size="lg" :disabled="form.processing || !form.score" @click="submit">
                    <Plus class="mr-2 size-4" />
                    {{ form.processing ? 'Registrando…' : 'Registrar puntaje' }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
