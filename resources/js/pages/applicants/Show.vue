<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import ApplicantStatusBadge from '@/components/applicant/ApplicantStatusBadge.vue';

const props = defineProps<{
    applicant: {
        id: number;
        name: string;
        phone: string;
        email: string;
        address: string | null;
        is_blocked: boolean;
        block_reason: string | null;
        blocked_at: string | null;
        created_at: string;
        created_by: { id: number; name: string };
        blocked_by: { id: number; name: string } | null;
        documents: Array<{
            id: number;
            type: string;
            original_name: string;
            mime_type: string;
            size: number;
            created_at: string;
        }>;
        vacancies: Array<{
            id: number;
            position: string;
            location: string;
            pivot: {
                status: string;
                created_at: string;
            };
        }>;
    };
    history: Array<{
        type: string;
        date: string;
        title: string;
        description: string;
    }>;
    canEditApplicants: boolean;
    canDeleteApplicants: boolean;
    canBlockApplicants: boolean;
    canCreateApplicants: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Applicants', href: '/applicants' },
    { title: props.applicant.name, href: `/applicants/${props.applicant.id}` },
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
    <Head :title="applicant.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold">{{ applicant.name }}</h1>
                    <span v-if="applicant.is_blocked" class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">
                        Blocked
                    </span>
                    <span v-else class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300">
                        Active
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <Link v-if="canEditApplicants" :href="route('applicants.edit', applicant.id)">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <template v-if="canBlockApplicants">
                        <Link v-if="!applicant.is_blocked" :href="route('applicants.block', applicant.id)" method="post" :data="{ block_reason: 'Block reason placeholder' }">
                            <Button variant="destructive">Block</Button>
                        </Link>
                        <Link v-else :href="route('applicants.unblock', applicant.id)" method="post">
                            <Button variant="outline">Unblock</Button>
                        </Link>
                    </template>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Applicant Details</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Email</p>
                            <p class="text-sm">{{ applicant.email }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Phone</p>
                            <p class="text-sm">{{ applicant.phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Address</p>
                            <p class="text-sm">{{ applicant.address ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Created By</p>
                            <p class="text-sm">{{ applicant.created_by.name }}</p>
                        </div>
                    </div>
                    <Separator />
                    <div v-if="applicant.is_blocked" class="rounded-md bg-red-50 p-4 dark:bg-red-950">
                        <p class="text-sm font-medium text-red-800 dark:text-red-300">Block Reason</p>
                        <p class="text-sm text-red-700 dark:text-red-400">{{ applicant.block_reason }}</p>
                        <p v-if="applicant.blocked_by" class="mt-1 text-xs text-red-600 dark:text-red-500">
                            Blocked by {{ applicant.blocked_by.name }} on {{ formatDate(applicant.blocked_at!) }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Tabs default-value="documents" class="w-full">
                <TabsList>
                    <TabsTrigger value="documents">Documents</TabsTrigger>
                    <TabsTrigger value="history">History</TabsTrigger>
                    <TabsTrigger value="vacancies">Vacancies</TabsTrigger>
                </TabsList>

                <TabsContent value="documents">
                    <Card>
                        <CardHeader>
                            <CardTitle>Documents</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="applicant.documents.length === 0" class="py-8 text-center text-muted-foreground">
                                No documents uploaded.
                            </div>
                            <div v-else class="space-y-4">
                                <div v-for="document in applicant.documents" :key="document.id" class="flex items-center justify-between rounded-lg border p-4">
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

                            <div class="mt-6">
                                <Link :href="route('applicants.documents.index', applicant.id)">
                                    <Button variant="outline">Manage Documents</Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="history">
                    <Card>
                        <CardHeader>
                            <CardTitle>History Timeline</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="history.length === 0" class="py-8 text-center text-muted-foreground">
                                No history available.
                            </div>
                            <div v-else class="space-y-4">
                                <div v-for="(event, index) in history" :key="index" class="relative border-l-2 border-muted pl-4">
                                    <div class="absolute -left-[5px] top-1 h-2 w-2 rounded-full bg-primary" />
                                    <p class="text-sm font-medium">{{ event.title }}</p>
                                    <p class="text-sm text-muted-foreground">{{ event.description }}</p>
                                    <p class="text-xs text-muted-foreground">{{ formatDate(event.date) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="vacancies">
                    <Card>
                        <CardHeader>
                            <CardTitle>Associated Vacancies</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="applicant.vacancies.length === 0" class="py-8 text-center text-muted-foreground">
                                Not associated with any vacancies.
                            </div>
                            <div v-else class="space-y-4">
                                <div v-for="vacancy in applicant.vacancies" :key="vacancy.id" class="flex items-center justify-between rounded-lg border p-4">
                                    <div>
                                        <p class="font-medium">{{ vacancy.position }}</p>
                                        <p class="text-sm text-muted-foreground">{{ vacancy.location }}</p>
                                    </div>
                                    <ApplicantStatusBadge :status="vacancy.pivot.status" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>
        </div>
    </AppLayout>
</template>
