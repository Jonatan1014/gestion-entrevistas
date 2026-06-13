<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    vacancy: {
        id: number;
        position: string;
        location: string;
        requirements: string;
        min_grade: number | null;
        status: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Vacancies', href: '/vacancies' },
    { title: 'Edit', href: `/vacancies/${props.vacancy.id}/edit` },
];

const form = useForm({
    position: props.vacancy.position,
    location: props.vacancy.location,
    requirements: props.vacancy.requirements,
    min_grade: props.vacancy.min_grade ?? ('' as string | number),
});

const submit = () => {
    form.put(route('vacancies.update', props.vacancy.id));
};
</script>

<template>
    <Head title="Edit Vacancy" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Edit Vacancy</h1>
                <Link :href="route('vacancies.show', vacancy.id)">
                    <Button variant="outline">Cancel</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Vacancy Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="position">Position</Label>
                            <Input id="position" type="text" v-model="form.position" required />
                            <InputError class="mt-2" :message="form.errors.position" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="location">Location</Label>
                            <Input id="location" type="text" v-model="form.location" required />
                            <InputError class="mt-2" :message="form.errors.location" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="requirements">Requirements</Label>
                            <Textarea id="requirements" v-model="form.requirements" required rows="4" />
                            <InputError class="mt-2" :message="form.errors.requirements" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="min_grade">Minimum Grade (optional)</Label>
                            <Input id="min_grade" type="number" step="0.01" v-model="form.min_grade" />
                            <InputError class="mt-2" :message="form.errors.min_grade" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">Save Changes</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>