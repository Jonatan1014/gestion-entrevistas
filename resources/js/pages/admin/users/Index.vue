<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            email_verified_at: string | null;
            roles: string[];
            created_at: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Usuarios', href: '/admin/users' },
];

const updateFilters = () => {
    router.get('/admin/users', {
        search: filters.search ?? '',
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Gestión de usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Usuarios</h1>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Filtrar usuarios</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <Label for="search">Buscar por nombre o email</Label>
                            <Input
                                id="search"
                                v-model="filters.search"
                                type="text"
                                placeholder="Buscar…"
                                @input="updateFilters"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Todos los usuarios</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="px-4 py-3 font-medium">Nombre</th>
                                    <th class="px-4 py-3 font-medium">Email</th>
                                    <th class="px-4 py-3 font-medium">Roles</th>
                                    <th class="px-4 py-3 font-medium">Verificado</th>
                                    <th class="px-4 py-3 font-medium">Registrado</th>
                                    <th class="px-4 py-3 font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id" class="border-b hover:bg-muted/50">
                                    <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ user.email }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge v-for="role in user.roles" :key="role" variant="secondary" class="text-xs">
                                                {{ role }}
                                            </Badge>
                                            <span v-if="user.roles.length === 0" class="text-xs text-muted-foreground">Sin rol</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span v-if="user.email_verified_at" class="text-green-600 text-xs">✓ {{ user.email_verified_at }}</span>
                                        <span v-else class="text-red-600 text-xs">No verificado</span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ user.created_at }}</td>
                                    <td class="px-4 py-3">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="outline" size="sm">Acciones</Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('admin.users.edit', user.id)" class="w-full">Editar roles</Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('admin.users.destroy', user.id)" method="delete" class="w-full text-red-600">
                                                        Eliminar
                                                    </Link>
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                        No hay usuarios registrados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="users.links.length > 3" class="mt-4 flex justify-end gap-1">
                        <Link
                            v-for="link in users.links"
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
