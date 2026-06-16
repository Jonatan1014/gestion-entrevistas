<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

defineProps<{
    roles: Array<{
        id: number;
        name: string;
        permissions: string[];
        permissions_count: number;
        is_protected: boolean;
    }>;
    canCreateRoles: boolean;
    canEditRoles: boolean;
    canDeleteRoles: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: '/admin/roles' },
];

const deleteForm = useForm({});
</script>

<template>
    <Head title="Role Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Role Management</h1>
                <Button v-if="canCreateRoles" as-child>
                    <Link :href="route('admin.roles.create')">Crear rol</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Roles</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 font-medium">Role</th>
                                    <th class="px-4 py-3 font-medium">Permissions</th>
                                    <th class="px-4 py-3 font-medium">Type</th>
                                    <th class="px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="role in roles" :key="role.id" class="border-b hover:bg-muted/50">
                                    <td class="px-4 py-3 font-medium">{{ role.name }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge variant="secondary" class="text-xs">
                                                {{ role.permissions_count }} permissions
                                            </Badge>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge :variant="role.is_protected ? 'default' : 'outline'" class="text-xs">
                                            {{ role.is_protected ? 'System' : 'Custom' }}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="outline" size="sm">Actions</Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem v-if="canEditRoles" as-child>
                                                    <Link :href="route('admin.roles.edit', role.id)">Edit</Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    v-if="canDeleteRoles && !role.is_protected"
                                                    as-child
                                                >
                                                    <Link
                                                        as="button"
                                                        method="delete"
                                                        :href="route('admin.roles.destroy', role.id)"
                                                        preserve-scroll
                                                    >
                                                        Delete
                                                    </Link>
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                                <tr v-if="roles.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                        No roles found.
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
