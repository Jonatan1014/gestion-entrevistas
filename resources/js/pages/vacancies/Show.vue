<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import VacancyStatusBadge from '@/components/vacancy/VacancyStatusBadge.vue';

const props = defineProps<{
    vacancy: {
        id: number;
        position: string;
        location: string;
        requirements: string;
        status: string;
        min_grade: number | null;
        created_at: string;
        creator: { id: number; name: string };
    };
    canEditVacancies: boolean;
    canDeleteVacancies: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vacancies', href: '/vacancies' },
    { title: props.vacancy.position, href: `/vacancies/${props.vacancy.id}` },
];
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
                    <Link :href="route('vacancies.edit', vacancy.id)">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Link v-if="vacancy.status === 'open'" :href="route('vacancies.close', vacancy.id)" method="post">
                        <Button variant="outline">Close</Button>
                    </Link>
                    <Link v-if="vacancy.status === 'open'" :href="route('vacancies.cancel', vacancy.id)" method="post">
                        <Button variant="destructive">Cancel</Button>
                    </Link>
                    <Link v-if="vacancy.status === 'closed'" :href="route('vacancies.reopen', vacancy.id)" method="post">
                        <Button variant="outline">Reopen</Button>
                    </Link>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Vacancy Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Position</p>
                            <p class="text-sm">{{ vacancy.position }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Location</p>
                            <p class="text-sm">{{ vacancy.location }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Minimum Grade</p>
                            <p class="text-sm">{{ vacancy.min_grade ?? 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Created By</p>
                            <p class="text-sm">{{ vacancy.creator.name }}</p>
                        </div>
                    </div>
                    <Separator />
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-2">Requirements</p>
                        <p class="text-sm whitespace-pre-line">{{ vacancy.requirements }}</p>
                    </div>
                </CardContent>
            </Card>

            <Tabs default-value="tests" class="w-full">
                <TabsList>
                    <TabsTrigger value="tests">Tests</TabsTrigger>
                    <TabsTrigger value="applicants">Applicants</TabsTrigger>
                    <TabsTrigger value="results">Results</TabsTrigger>
                </TabsList>
                <TabsContent value="tests">
                    <Card>
                        <CardContent class="pt-6">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-muted-foreground">Manage the tests associated with this vacancy.</p>
                                <Link :href="route('vacancies.tests.index', vacancy.id)">
                                    <Button variant="outline" size="sm">Manage Tests</Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
                <TabsContent value="applicants">
                    <Card>
                        <CardContent class="pt-6">
                            <p class="text-sm text-muted-foreground">Applicants will be available in a future phase.</p>
                        </CardContent>
                    </Card>
                </TabsContent>
                <TabsContent value="results">
                    <Card>
                        <CardContent class="pt-6">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-muted-foreground">View and record test results for applicants.</p>
                                <Link :href="route('vacancies.results.index', vacancy.id)">
                                    <Button variant="outline" size="sm">View Results</Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>