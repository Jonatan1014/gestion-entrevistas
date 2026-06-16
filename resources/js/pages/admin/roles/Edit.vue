<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { computed } from 'vue';

const props = defineProps<{
    role: {
        id: number;
        name: string;
        permissions: string[];
        is_protected: boolean;
    };
    permissions: Record<string, Array<{ id: number; name: string; guard_name: string }>>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Roles', href: '/admin/roles' },
    { title: `Edit: ${props.role.name}`, href: `/admin/roles/${props.role.id}/edit` },
];

const form = useForm({
    name: props.role.name,
    permissions: [...props.role.permissions],
});

const moduleNames: Record<string, string> = {
    vacancies: 'Vacancies',
    applicants: 'Applicants',
    documents: 'Documents',
    tests: 'Tests',
    results: 'Resultados',
    interviews: 'Interviews',
    reports: 'Reports',
    roles: 'Roles',
    other: 'Other',
};

const sortedModules = computed(() => {
    return Object.keys(props.permissions).sort((a, b) => {
        const order: Record<string, number> = {
            vacancies: 1, applicants: 2, documents: 3, tests: 4, results: 5,
            interviews: 6, reports: 7, roles: 8, other: 9,
        };
        return (order[a] ?? 10) - (order[b] ?? 10);
    });
});

const togglePermission = (name: string) => {
    const idx = form.permissions.indexOf(name);
    if (idx === -1) {
        form.permissions.push(name);
    } else {
        form.permissions.splice(idx, 1);
    }
};

const selectAllInModule = (modulePermissions: Array<{ name: string }>) => {
    const names = modulePermissions.map((p) => p.name);
    const allSelected = names.every((n) => form.permissions.includes(n));
    if (allSelected) {
        form.permissions = form.permissions.filter((p) => !names.includes(p));
    } else {
        for (const name of names) {
            if (!form.permissions.includes(name)) {
                form.permissions.push(name);
            }
        }
    }
};

const submit = () => {
    form.patch(route('admin.roles.update', props.role.id));
};
</script>

<template>
    <Head :title="`Edit Role: ${role.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Edit Role: {{ role.name }}</h1>
                <Link :href="route('admin.roles.index')">
                    <Button variant="outline">Back to Roles</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Role Permissions</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="name">Role Name</Label>
                            <Input
                                id="name"
                                type="text"
                                v-model="form.name"
                                :disabled="role.is_protected"
                                required
                            />
                            <p v-if="role.is_protected" class="text-sm text-muted-foreground">
                                System roles cannot be renamed.
                            </p>
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="space-y-4">
                            <h3 class="text-lg font-medium">Permissions</h3>
                            <InputError :message="form.errors.permissions" />

                            <div v-for="module in sortedModules" :key="module" class="border rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <Checkbox
                                        :id="`module-${module}`"
                                        :checked="permissions[module].every((p: { name: string }) => form.permissions.includes(p.name))"
                                        @update:checked="selectAllInModule(permissions[module])"
                                    />
                                    <Label :for="`module-${module}`" class="font-semibold cursor-pointer">
                                        {{ moduleNames[module] || module }}
                                    </Label>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 pl-8">
                                    <div
                                        v-for="perm in permissions[module]"
                                        :key="perm.id"
                                        class="flex items-center gap-2"
                                    >
                                        <Checkbox
                                            :id="`perm-${perm.id}`"
                                            :checked="form.permissions.includes(perm.name)"
                                            @update:checked="togglePermission(perm.name)"
                                        />
                                        <Label :for="`perm-${perm.id}`" class="text-sm cursor-pointer">
                                            {{ perm.name }}
                                        </Label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">Save Changes</Button>
                            <Link :href="route('admin.roles.index')">
                                <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
