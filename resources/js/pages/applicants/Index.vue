<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const props = defineProps<{
    applicants: {
        data: Array<{
            id: number;
            name: string;
            phone: string;
            email: string;
            is_blocked: boolean;
            created_at: string;
            created_by: { id: number; name: string };
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    canCreateApplicants: boolean;
    canEditApplicants: boolean;
    canDeleteApplicants: boolean;
    canBlockApplicants: boolean;
    filters: {
        search?: string;
        assigned_to_me?: boolean;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Applicants', href: '/applicants' },
];

const updateFilters = () => {
    router.get('/applicants', {
        search: props.filters.search ?? '',
        assigned_to_me: props.filters.assigned_to_me ? 1 : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Applicants" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Applicants</h1>
                <Link v-if="canCreateApplicants" :href="route('applicants.create')">
                    <Button>Register Applicant</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Filter Applicants</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <Label for="search">Search by name or email</Label>
                            <Input
                                id="search"
                                v-model="filters.search"
                                type="text"
                                placeholder="Search..."
                                @input="updateFilters"
                            />
                        </div>
                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="assigned_to_me"
                                :checked="filters.assigned_to_me"
                                @update:checked="filters.assigned_to_me = $event; updateFilters()"
                            />
                            <Label for="assigned_to_me" class="cursor-pointer">Assigned to me</Label>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>All Applicants</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 font-medium">Name</th>
                                    <th class="px-4 py-3 font-medium">Email</th>
                                    <th class="px-4 py-3 font-medium">Phone</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium">Created By</th>
                                    <th class="px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="applicant in applicants.data" :key="applicant.id" class="border-b hover:bg-muted/50">
                                    <td class="px-4 py-3">
                                        <Link :href="route('applicants.show', applicant.id)" class="text-primary hover:underline">
                                            {{ applicant.name }}
                                        </Link>
                                    </td>
                                    <td class="px-4 py-3">{{ applicant.email }}</td>
                                    <td class="px-4 py-3">{{ applicant.phone }}</td>
                                    <td class="px-4 py-3">
                                        <span v-if="applicant.is_blocked" class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">
                                            Blocked
                                        </span>
                                        <span v-else class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ applicant.created_by.name }}</td>
                                    <td class="px-4 py-3">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="outline" size="sm">Actions</Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('applicants.show', applicant.id)" class="w-full">View</Link>
                                                </DropdownMenuItem>
                                                <template v-if="canEditApplicants">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('applicants.edit', applicant.id)" class="w-full">Edit</Link>
                                                    </DropdownMenuItem>
                                                </template>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                                <tr v-if="applicants.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                        No applicants found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="applicants.links.length > 3" class="mt-4 flex justify-end gap-1">
                        <Link
                            v-for="link in applicants.links"
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
        </div>
    </AppLayout>
</template>
