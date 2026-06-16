<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ChevronLeft, ChevronRight, Calendar as CalendarIcon } from 'lucide-vue-next';

defineProps<{
    calendar: Record<string, Array<{
        id: number;
        scheduled_at: string;
        type: string;
        status: string;
        applicant_name: string;
        vacancy_position: string;
        interviewer_name: string;
    }>>;
    year: number;
    month: number;
    monthName: string;
    prevMonth: { year: number; month: number };
    nextMonth: { year: number; month: number };
    daysInMonth: number;
    firstDayOfWeek: number;
    today: string;
    canManageInterviews: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Entrevistas', href: '/interviews' },
    { title: 'Calendario', href: '/interviews/calendar' },
];

const DAYS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

const statusColor = (status: string) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-700';
        case 'completed': return 'bg-green-100 text-green-800 border-green-300 dark:bg-green-900/30 dark:text-green-300 dark:border-green-700';
        case 'cancelled': return 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900/30 dark:text-red-300 dark:border-red-700';
        default: return '';
    }
};

const statusLabel = (status: string) => {
    switch (status) {
        case 'pending': return 'Pendiente';
        case 'completed': return 'Completada';
        case 'cancelled': return 'Cancelada';
        default: return status;
    }
};

const typeLabel = (type: string) => type === 'virtual' ? 'Virtual' : 'Presencial';
</script>

<template>
    <Head title="Calendario de entrevistas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Calendario de entrevistas</h1>
                    <p class="text-sm text-muted-foreground">{{ monthName }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('interviews.calendar', prevMonth)">
                        <Button variant="outline" size="sm">
                            <ChevronLeft class="mr-1 h-4 w-4" />
                            {{ ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][prevMonth.month] }}
                        </Button>
                    </Link>
                    <Link :href="route('interviews.calendar', { year: new Date().getFullYear(), month: new Date().getMonth() + 1 })">
                        <Button variant="outline" size="sm">
                            <CalendarIcon class="mr-1 h-4 w-4" />
                            Hoy
                        </Button>
                    </Link>
                    <Link :href="route('interviews.calendar', nextMonth)">
                        <Button variant="outline" size="sm">
                            {{ ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][nextMonth.month] }}
                            <ChevronRight class="ml-1 h-4 w-4" />
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Calendar Grid -->
            <Card>
                <CardContent class="p-0">
                    <div class="grid grid-cols-7">
                        <!-- Day headers -->
                        <div v-for="day in DAYS" :key="day" class="border-b border-r px-2 py-3 text-center text-xs font-semibold text-muted-foreground last:border-r-0">
                            {{ day }}
                        </div>

                        <!-- Empty cells before first day -->
                        <div
                            v-for="i in firstDayOfWeek"
                            :key="'empty-' + i"
                            class="border-b border-r bg-muted/20 last:border-r-0"
                            :style="{ minHeight: '100px' }"
                        />

                        <!-- Day cells -->
                        <div
                            v-for="day in daysInMonth"
                            :key="'day-' + day"
                            class="border-b border-r p-1 transition-colors last:border-r-0 hover:bg-muted/30"
                            :class="{
                                'bg-[#51eead]/5': today === `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`,
                            }"
                            :style="{ minHeight: '100px' }"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <span
                                    class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold"
                                    :class="today === `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}` ? 'bg-[#0d0d0d] text-white' : ''"
                                >
                                    {{ day }}
                                </span>
                            </div>

                            <!-- Interviews for this day -->
                            <div class="space-y-1">
                                <Link
                                    v-for="interview in (calendar[`${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`] ?? [])"
                                    :key="interview.id"
                                    :href="route('interviews.show', interview.id)"
                                    class="block rounded border px-1.5 py-0.5 text-[11px] leading-tight transition-colors hover:ring-1 hover:ring-[#51eead]"
                                    :class="statusColor(interview.status)"
                                >
                                    <div class="font-medium truncate">{{ interview.scheduled_at }} — {{ interview.applicant_name }}</div>
                                    <div class="truncate opacity-75">{{ interview.vacancy_position }}</div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Legend -->
            <div class="flex items-center gap-4 text-sm text-muted-foreground">
                <div class="flex items-center gap-1.5">
                    <div class="h-3 w-3 rounded border border-yellow-300 bg-yellow-100 dark:bg-yellow-900/30 dark:border-yellow-700" />
                    Pendiente
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-3 w-3 rounded border border-green-300 bg-green-100 dark:bg-green-900/30 dark:border-green-700" />
                    Completada
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="h-3 w-3 rounded border border-red-300 bg-red-100 dark:bg-red-900/30 dark:border-red-700" />
                    Cancelada
                </div>
            </div>
        </div>
    </AppLayout>
</template>
