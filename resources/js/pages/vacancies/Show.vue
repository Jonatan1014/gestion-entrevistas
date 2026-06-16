<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import VacancyStatusBadge from '@/components/vacancy/VacancyStatusBadge.vue';
import ApplicantStatusBadge from '@/components/applicant/ApplicantStatusBadge.vue';
import { BarChart, ClipboardCheck, Users, Pencil, Ban, CheckCircle, Trophy, Medal, Award } from 'lucide-vue-next';

defineProps<{
    vacancy: {
        id: number;
        position: string;
        location: string;
        requirements: string;
        status: string;
        min_grade: number | null;
        creator: { id: number; name: string };
    };
    tests: Array<{ id: number; name: string; type: string; max_score: number; weight: number }>;
    applicants: Array<{ id: number; name: string; email: string; status: string }>;
    ranking: Array<{
        id: number;
        name: string;
        email: string;
        status: string;
        weighted_average: number;
        meets_min_grade: boolean;
        has_scores: boolean;
    }>;
    canEditVacancies: boolean;
    canDeleteVacancies: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vacantes', href: '/vacancies' },
];

const typeLabel = (type: string) => ({ numeric: 'Numérica', text: 'Texto', multiple_choice: 'Opción múltiple' }[type] ?? type);

const statusLabel = (status: string) => ({
    registered: 'Registrado', in_interview: 'En entrevista', evaluated: 'Evaluado', apt: 'Apto', no_apt: 'No apto',
}[status] ?? status);

const rankIcon = (index: number) => {
    if (index === 0) return Trophy;
    if (index === 1) return Medal;
    if (index === 2) return Award;
    return null;
};
</script>

<template>
    <Head :title="vacancy.position" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold">{{ vacancy.position }}</h1>
                    <VacancyStatusBadge :status="vacancy.status" />
                </div>
                <div v-if="canEditVacancies" class="flex items-center gap-2">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="route('vacancies.edit', vacancy.id)">
                            <Pencil class="mr-1 h-3.5 w-3.5" />
                            Editar
                        </Link>
                    </Button>
                    <Button v-if="vacancy.status === 'open'" as-child variant="outline" size="sm">
                        <Link :href="route('vacancies.close', vacancy.id)" method="post">
                            <Ban class="mr-1 h-3.5 w-3.5" />
                            Cerrar
                        </Link>
                    </Button>
                    <Button v-if="vacancy.status === 'open'" as-child variant="destructive" size="sm">
                        <Link :href="route('vacancies.cancel', vacancy.id)" method="post">
                            Cancelar
                        </Link>
                    </Button>
                    <Button v-if="vacancy.status === 'closed'" as-child variant="outline" size="sm">
                        <Link :href="route('vacancies.reopen', vacancy.id)" method="post">
                            <CheckCircle class="mr-1 h-3.5 w-3.5" />
                            Reabrir
                        </Link>
                    </Button>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Detalles de la vacante</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Cargo</p>
                            <p class="text-sm">{{ vacancy.position }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Ubicación</p>
                            <p class="text-sm">{{ vacancy.location }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Nota mínima</p>
                            <p class="text-sm">{{ vacancy.min_grade ?? 'No definida' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Creada por</p>
                            <p class="text-sm">{{ vacancy.creator.name }}</p>
                        </div>
                    </div>
                    <Separator />
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-2">Requisitos</p>
                        <p class="text-sm whitespace-pre-line">{{ vacancy.requirements }}</p>
                    </div>
                </CardContent>
            </Card>

            <Tabs default-value="ranking" class="w-full">
                <TabsList>
                    <TabsTrigger value="ranking">
                        <Trophy class="mr-1 h-4 w-4" />
                        Ranking
                    </TabsTrigger>
                    <TabsTrigger value="tests">
                        <ClipboardCheck class="mr-1 h-4 w-4" />
                        Pruebas
                    </TabsTrigger>
                    <TabsTrigger value="applicants">
                        <Users class="mr-1 h-4 w-4" />
                        Postulantes
                    </TabsTrigger>
                </TabsList>

                <!-- Ranking Tab -->
                <TabsContent value="ranking">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Ranking de postulantes</CardTitle>
                            <Button as-child size="sm">
                                <Link :href="route('vacancies.results.index', vacancy.id)">
                                    <BarChart class="mr-1 h-4 w-4" />
                                    Ver resultados completos
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div v-if="ranking.length === 0" class="py-8 text-center text-muted-foreground">
                                No hay postulantes asociados a esta vacante.
                            </div>
                            <div v-else class="space-y-2">
                                <div
                                    v-for="(applicant, index) in ranking"
                                    :key="applicant.id"
                                    class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/30"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-muted text-sm font-bold">
                                            {{ index + 1 }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <component :is="rankIcon(index)" v-if="rankIcon(index)" class="h-4 w-4" :class="index === 0 ? 'text-yellow-500' : index === 1 ? 'text-gray-400' : 'text-amber-600'" />
                                                <p class="font-medium">{{ applicant.name }}</p>
                                            </div>
                                            <p class="text-xs text-muted-foreground">{{ applicant.email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <ApplicantStatusBadge :status="applicant.status" />
                                        <div class="text-right">
                                            <p v-if="applicant.has_scores" class="text-lg font-bold" :class="applicant.meets_min_grade ? 'text-green-600' : 'text-red-600'">
                                                {{ applicant.weighted_average.toFixed(2) }}
                                            </p>
                                            <p v-else class="text-sm text-muted-foreground">Sin puntajes</p>
                                            <p class="text-xs text-muted-foreground">Promedio</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Tests Tab -->
                <TabsContent value="tests">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Pruebas de esta vacante</CardTitle>
                            <Button as-child variant="outline" size="sm">
                                <Link :href="route('vacancies.tests.index', vacancy.id)">
                                    <ClipboardCheck class="mr-1 h-4 w-4" />
                                    Gestionar pruebas
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div v-if="tests.length === 0" class="py-8 text-center text-muted-foreground">
                                No hay pruebas asociadas a esta vacante.
                            </div>
                            <div v-else class="space-y-2">
                                <div v-for="test in tests" :key="test.id" class="flex items-center justify-between rounded-lg border p-4">
                                    <div>
                                        <p class="font-medium">{{ test.name }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ typeLabel(test.type) }} · Puntaje máx: {{ test.max_score }} · Peso: {{ test.weight }}%
                                        </p>
                                    </div>
                                    <Badge variant="outline">{{ test.weight }}%</Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Applicants Tab -->
                <TabsContent value="applicants">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between">
                            <CardTitle>Postulantes de esta vacante</CardTitle>
                            <Button as-child variant="outline" size="sm">
                                <Link :href="route('applicants.index')">
                                    <Users class="mr-1 h-4 w-4" />
                                    Todos los postulantes
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <div v-if="applicants.length === 0" class="py-8 text-center text-muted-foreground">
                                No hay postulantes asociados a esta vacante.
                            </div>
                            <div v-else class="space-y-2">
                                <div v-for="applicant in applicants" :key="applicant.id" class="flex items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/30">
                                    <div>
                                        <Link :href="route('applicants.show', applicant.id)" class="font-medium text-primary hover:underline">
                                            {{ applicant.name }}
                                        </Link>
                                        <p class="text-xs text-muted-foreground">{{ applicant.email }}</p>
                                    </div>
                                    <ApplicantStatusBadge :status="applicant.status" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
