<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Building2, Users, CalendarCheck, ClipboardCheck } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    stats: {
        activeVacancies: number;
        totalApplicants: number;
        upcomingInterviews: number;
        completedInterviews: number;
    };
    recentApplicants: Array<{
        id: number;
        name: string;
        email: string;
        created_at: string;
        created_by: string | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const statCards = [
    { key: 'activeVacancies', label: 'Vacantes Activas', icon: Building2, color: 'text-indigo-600 dark:text-indigo-400' },
    { key: 'totalApplicants', label: 'Postulantes', icon: Users, color: 'text-emerald-600 dark:text-emerald-400' },
    { key: 'upcomingInterviews', label: 'Entrevistas Pendientes', icon: CalendarCheck, color: 'text-amber-600 dark:text-amber-400' },
    { key: 'completedInterviews', label: 'Entrevistas Completadas', icon: ClipboardCheck, color: 'text-sky-600 dark:text-sky-400' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Stats Grid -->
            <div class="grid auto-rows-min gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <Card v-for="card in statCards" :key="card.key" class="overflow-hidden transition-shadow hover:shadow-md">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            {{ card.label }}
                        </CardTitle>
                        <component :is="card.icon" :class="['size-5', card.color]" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold tracking-tight">
                            {{ stats[card.key as keyof typeof stats] }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Applicants -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Postulantes Recientes</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="recentApplicants.length === 0" class="py-8 text-center text-sm text-muted-foreground">
                        No hay postulantes registrados aún.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Nombre</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Email</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Registrado por</th>
                                    <th class="px-4 py-3 font-medium text-muted-foreground">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="applicant in recentApplicants"
                                    :key="applicant.id"
                                    class="border-b transition-colors hover:bg-muted/50"
                                >
                                    <td class="px-4 py-3 font-medium">{{ applicant.name }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ applicant.email }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ applicant.created_by ?? '—' }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ applicant.created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
