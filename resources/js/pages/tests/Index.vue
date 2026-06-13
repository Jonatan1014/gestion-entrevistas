<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Test {
    id: number;
    name: string;
    description: string;
    type: string;
    max_score: number;
    evaluation_criteria: string | null;
    created_at: string;
}

defineProps<{
    tests: Test[];
    canManageTests: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tests', href: '/tests' },
];

const typeLabel = (type: string) => {
    return {
        numeric: 'Numeric',
        text: 'Text',
        multiple_choice: 'Multiple Choice',
    }[type] ?? type;
};
</script>

<template>
    <Head title="Tests" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Tests</h1>
                <Link v-if="canManageTests" :href="route('tests.create')">
                    <Button>Create Test</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Tests</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 font-medium">Name</th>
                                    <th class="px-4 py-3 font-medium">Type</th>
                                    <th class="px-4 py-3 font-medium">Max Score</th>
                                    <th class="px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="test in tests" :key="test.id" class="border-b hover:bg-muted/50">
                                    <td class="px-4 py-3">
                                        <Link :href="route('tests.show', test.id)" class="text-primary hover:underline">
                                            {{ test.name }}
                                        </Link>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge variant="outline">{{ typeLabel(test.type) }}</Badge>
                                    </td>
                                    <td class="px-4 py-3">{{ test.max_score }}</td>
                                    <td class="px-4 py-3">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="outline" size="sm">Actions</Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('tests.show', test.id)" class="w-full">View</Link>
                                                </DropdownMenuItem>
                                                <template v-if="canManageTests">
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('tests.edit', test.id)" class="w-full">Edit</Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem as-child>
                                                        <Link :href="route('tests.destroy', test.id)" method="delete" class="w-full text-red-600">Delete</Link>
                                                    </DropdownMenuItem>
                                                </template>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                                <tr v-if="tests.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                        No tests found.
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
