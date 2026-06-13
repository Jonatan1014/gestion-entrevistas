<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{
    applicant: {
        id: number;
        name: string;
    };
    documents: Array<{
        id: number;
        type: string;
        original_name: string;
        mime_type: string;
        size: number;
        created_at: string;
    }>;
    canCreateApplicants: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Applicants', href: '/applicants' },
    { title: props.applicant.name, href: `/applicants/${props.applicant.id}` },
    { title: 'Documents', href: `/applicants/${props.applicant.id}/documents` },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleString();
};

const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Head title="Applicant Documents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Documents for {{ applicant.name }}</h1>
                <Link :href="route('applicants.show', applicant.id)">
                    <Button variant="outline">Back to Applicant</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Documents</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="documents.length === 0" class="py-8 text-center text-muted-foreground">
                        No documents uploaded.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="document in documents" :key="document.id" class="flex items-center justify-between rounded-lg border p-4">
                            <div>
                                <p class="font-medium">{{ document.original_name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ document.type }} · {{ formatBytes(document.size) }} · {{ formatDate(document.created_at) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link :href="route('applicants.documents.download', [applicant.id, document.id])">
                                    <Button variant="outline" size="sm">Download</Button>
                                </Link>
                                <Link v-if="canCreateApplicants" :href="route('applicants.documents.destroy', [applicant.id, document.id])" method="delete">
                                    <Button variant="destructive" size="sm">Delete</Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
