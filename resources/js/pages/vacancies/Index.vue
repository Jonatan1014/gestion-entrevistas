<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import VacancyStatusBadge from '@/components/vacancy/VacancyStatusBadge.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

defineProps<{
    vacancies: Array<{
        id: number;
        position: string;
        location: string;
        status: string;
        min_grade: number | null;
        created_at: string;
        creator: { id: number; name: string };
    }>;
    canCreateVacancies: boolean;
    canEditVacancies: boolean;
    canDeleteVacancies: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vacancies', href: '/vacancies' },
];
</script>

<template>
    <Head title="Vacancies" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Vacancies</h1>
                <Link v-if="canCreateVacancies" :href="route('vacancies.create')">
                    <Button>Create Vacancy</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Vacancies</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 font-medium">Position</th>
                                    <th class="px-4 py-3 font-medium">Location</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium">Min Grade</th>
                                    <th class="px-4 py-3 font-medium">Created By</th>
                                    <th class="px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="vacancy in vacancies" :key="vacancy.id" class="border-b hover:bg-muted/50">
                                    <td class="px-4 py-3">
                                        <Link :href="route('vacancies.show', vacancy.id)" class="text-primary hover:underline">
                                            {{ vacancy.position }}
                                        </Link>
                                    </td>
                                    <td class="px-4 py-3">{{ vacancy.location }}</td>
                                    <td class="px-4 py-3">
                                        <VacancyStatusBadge :status="vacancy.status" />
                                    </td>
                                    <td class="px-4 py-3">{{ vacancy.min_grade ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ vacancy.creator.name }}</td>
                                    <td class="px-4 py-3">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="outline" size="sm">Actions</Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('vacancies.show', vacancy.id)" class="w-full">View</Link>
                                                </DropdownMenuItem>
                                                <template v-if="canEditVacancies">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('vacancies.edit', vacancy.id)" class="w-full">Edit</Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem v-if="vacancy.status === 'open'" as-child>
                                                        <Link :href="route('vacancies.close', vacancy.id)" method="post" class="w-full">Close</Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem v-if="vacancy.status === 'open'" as-child>
                                                        <Link :href="route('vacancies.cancel', vacancy.id)" method="post" class="w-full text-red-600">Cancel</Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem v-if="vacancy.status === 'closed'" as-child>
                                                        <Link :href="route('vacancies.reopen', vacancy.id)" method="post" class="w-full">Reopen</Link>
                                                    </DropdownMenuItem>
                                                </template>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                                <tr v-if="vacancies.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                        No vacancies found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>