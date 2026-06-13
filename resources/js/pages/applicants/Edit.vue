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
    applicant: {
        id: number;
        name: string;
        phone: string;
        email: string;
        address: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Applicants', href: '/applicants' },
    { title: props.applicant.name, href: `/applicants/${props.applicant.id}` },
    { title: 'Edit', href: `/applicants/${props.applicant.id}/edit` },
];

const form = useForm({
    name: props.applicant.name,
    phone: props.applicant.phone,
    email: props.applicant.email,
    address: props.applicant.address ?? '',
});

const submit = () => {
    form.put(route('applicants.update', props.applicant.id));
};
</script>

<template>
    <Head title="Edit Applicant" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Edit Applicant</h1>
                <Link :href="route('applicants.show', applicant.id)">
                    <Button variant="outline">Back to Applicant</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Applicant Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="name">Full Name</Label>
                            <Input id="name" type="text" v-model="form.name" required />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone">Phone</Label>
                            <Input id="phone" type="text" v-model="form.phone" required />
                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="address">Address</Label>
                            <Textarea id="address" v-model="form.address" rows="3" />
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="form.processing">Update Applicant</Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
