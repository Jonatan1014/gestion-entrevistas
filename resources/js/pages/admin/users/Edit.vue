<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    editUser: {
        id: number;
        name: string;
        email: string;
        roles: string[];
    };
    roles: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Usuarios', href: '/admin/users' },
    { title: `Editar: ${props.editUser.name}`, href: `/admin/users/${props.editUser.id}/edit` },
];

const form = useForm({
    roles: [...props.editUser.roles],
});

const toggleRole = (name: string) => {
    const idx = form.roles.indexOf(name);
    if (idx === -1) {
        form.roles.push(name);
    } else {
        form.roles.splice(idx, 1);
    }
};

const submit = () => {
    form.patch(route('admin.users.update', props.editUser.id));
};
</script>

<template>
    <Head :title="`Editar roles — ${editUser.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Editar roles de {{ editUser.name }}</h1>
                <Button as-child variant="outline">
                    <Link :href="route('admin.users.index')">Volver a usuarios</Link>
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ editUser.email }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6" novalidate>
                        <div class="grid gap-2">
                            <Label>Roles asignados</Label>
                            <p class="text-xs text-muted-foreground">Seleccioná los roles para este usuario. Sin rol no puede acceder a nada.</p>
                            <div class="space-y-2 rounded-lg border p-4">
                                <div v-for="role in roles" :key="role.id" class="flex items-center gap-3 rounded-md p-2 transition-colors hover:bg-muted/50">
                                    <Checkbox
                                        :id="`role-${role.id}`"
                                        :checked="form.roles.includes(role.name)"
                                        @update:checked="toggleRole(role.name)"
                                    />
                                    <Label :for="`role-${role.id}`" class="cursor-pointer font-medium">{{ role.name }}</Label>
                                </div>
                            </div>
                            <InputError :message="form.errors.roles" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">Guardar cambios</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
