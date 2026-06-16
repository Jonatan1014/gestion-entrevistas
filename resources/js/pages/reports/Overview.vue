<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Briefcase, Users, CalendarCheck, TrendingUp, ArrowRight, TestTube, Video } from 'lucide-vue-next';

defineProps<{
    kpis: { activeVacancies: number; applicantsInProcess: number; interviewsThisMonth: number; conversionRate: number };
    pipeline: { registered: number; in_interview: number; evaluated: number; apt: number; no_apt: number };
    activity: Array<{ type: string; applicant: string; test?: string; score?: number; vacancy: string; evaluator?: string; interviewer?: string; status?: string; date: string }>;
    vacancies: Array<{ id: number; position: string }>;
    interviewers: Array<{ id: number; name: string }>;
    isAdmin: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Reportes', href: '/reports' }];

const kpiCards = [
    { key: 'activeVacancies' as const, label: 'Vacantes activas', icon: Briefcase, suffix: '' },
    { key: 'applicantsInProcess' as const, label: 'En proceso', icon: Users, suffix: '' },
    { key: 'interviewsThisMonth' as const, label: 'Entrevistas del mes', icon: CalendarCheck, suffix: '' },
    { key: 'conversionRate' as const, label: 'Tasa de aptos', icon: TrendingUp, suffix: '%' },
];

const stages = [
    { key: 'registered' as const, label: 'Registrados', color: 'bg-blue-500' },
    { key: 'in_interview' as const, label: 'En entrevista', color: 'bg-yellow-500' },
    { key: 'evaluated' as const, label: 'Evaluados', color: 'bg-purple-500' },
    { key: 'apt' as const, label: 'Aptos', color: 'bg-green-500' },
    { key: 'no_apt' as const, label: 'No aptos', color: 'bg-red-500' },
];

const statusLabels: Record<string, string> = { pending: 'Pendiente', completed: 'Completada', cancelled: 'Cancelada' };
</script>

<template>
    <Head title="Reportes" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">Reportes</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Indicadores clave del proceso de selección.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm"><Link :href="route('reports.comparison')">Comparación</Link></Button>
                    <Button as-child variant="outline" size="sm"><Link :href="route('reports.pipeline')">Pipeline</Link></Button>
                </div>
            </div>

            <!-- KPIs -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <Card v-for="c in kpiCards" :key="c.key" class="border-input/50">
                    <CardContent class="p-5">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">{{ c.label }}</p>
                            <component :is="c.icon" class="size-4 text-[#51eead]" />
                        </div>
                        <p class="mt-2 text-3xl font-bold tabular-nums">{{ kpis[c.key] }}{{ c.suffix }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Pipeline + Activity -->
            <div class="grid gap-6 lg:grid-cols-5">
                <Card class="lg:col-span-3 border-input/50">
                    <CardContent class="p-5">
                        <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-4">Embudo de selección</p>
                        <div class="space-y-3">
                            <div v-for="s in stages" :key="s.key" class="flex items-center gap-3">
                                <p class="w-24 text-sm text-muted-foreground shrink-0">{{ s.label }}</p>
                                <div class="flex-1 h-6 rounded-full bg-muted/50 overflow-hidden">
                                    <div :class="s.color" class="h-full rounded-full flex items-center justify-end pr-2 transition-all duration-500" :style="{ width: Math.max((pipeline[s.key] / Math.max(...Object.values(pipeline).filter((v): v is number => typeof v === 'number'), 1)) * 100, pipeline[s.key] > 0 ? 4 : 0) + '%' }">
                                        <span v-if="pipeline[s.key] > 0" class="text-[10px] font-bold text-white tabular-nums">{{ pipeline[s.key] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="lg:col-span-2 border-input/50">
                    <CardContent class="p-5">
                        <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-4">Actividad reciente</p>
                        <div v-if="activity.length === 0" class="py-8 text-center text-sm text-muted-foreground">Sin actividad.</div>
                        <div v-else class="space-y-3">
                            <div v-for="(item, i) in activity" :key="i" class="flex items-start gap-3">
                                <div class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-muted/50">
                                    <TestTube v-if="item.type === 'score'" class="size-3 text-[#51eead]" />
                                    <Video v-else class="size-3 text-[#51eead]" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm truncate">
                                        <span class="font-medium">{{ item.applicant }}</span>
                                        <span class="text-muted-foreground">
                                            — {{ item.type === 'score' ? `${item.score} en ${item.test}` : statusLabels[item.status ?? ''] ?? item.status }}
                                        </span>
                                    </p>
                                    <p class="text-xs text-muted-foreground">{{ item.vacancy }} · {{ item.date }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <span>Ver en detalle:</span>
                <Link :href="route('reports.comparison')" class="inline-flex items-center gap-1 text-primary hover:underline">Comparación <ArrowRight class="size-3" /></Link>
                <Link :href="route('reports.pipeline')" class="inline-flex items-center gap-1 text-primary hover:underline">Pipeline <ArrowRight class="size-3" /></Link>
                <Link :href="route('reports.interviews')" class="inline-flex items-center gap-1 text-primary hover:underline">Entrevistas <ArrowRight class="size-3" /></Link>
            </div>
        </div>
    </AppLayout>
</template>
