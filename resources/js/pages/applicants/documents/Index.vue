<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Upload, Eye, Download, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

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
    { title: 'Postulantes', href: '/applicants' },
    { title: props.applicant.name, href: `/applicants/${props.applicant.id}` },
    { title: 'Documentos', href: `/applicants/${props.applicant.id}/documents` },
];

const fileInput = ref<HTMLInputElement>();

const uploadForm = useForm({
    type: 'cv' as string,
    file: null as File | null,
});

const submitUpload = () => {
    uploadForm.post(route('applicants.documents.store', props.applicant.id), {
        onSuccess: () => {
            uploadForm.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('es-AR');
};

const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const docTypeLabel = (type: string) => type === 'cv' ? 'CV' : 'Certificado';
</script>

<template>
    <Head title="Documentos del postulante" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Documentos de {{ applicant.name }}</h1>
                <Button as-child variant="outline">
                    <Link :href="route('applicants.show', applicant.id)">Volver al postulante</Link>
                </Button>
            </div>

            <!-- Upload Form -->
            <Card v-if="canCreateApplicants">
                <CardHeader>
                    <CardTitle>Subir documento</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitUpload" class="space-y-4" enctype="multipart/form-data">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="doc-type">Tipo de documento</Label>
                                <Select v-model="uploadForm.type">
                                    <SelectTrigger id="doc-type">
                                        <SelectValue placeholder="Seleccionar tipo" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="cv">CV</SelectItem>
                                        <SelectItem value="certificate">Certificado</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid gap-2">
                                <Label for="doc-file">Archivo (PDF, DOCX, JPG, PNG — máx. 5 MB)</Label>
                                <Input
                                    id="doc-file"
                                    ref="fileInput"
                                    type="file"
                                    accept=".pdf,.docx,.jpg,.jpeg,.png"
                                    @input="uploadForm.file = ($event.target as HTMLInputElement).files?.[0] ?? null"
                                />
                                <p v-if="uploadForm.errors.file" class="text-sm text-red-600">{{ uploadForm.errors.file }}</p>
                                <p v-if="uploadForm.errors.type" class="text-sm text-red-600">{{ uploadForm.errors.type }}</p>
                            </div>
                        </div>
                        <Button type="submit" :disabled="uploadForm.processing || !uploadForm.file">
                            <Upload class="mr-2 h-4 w-4" />
                            {{ uploadForm.processing ? 'Subiendo…' : 'Subir documento' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <!-- Document List -->
            <Card>
                <CardHeader>
                    <CardTitle>Documentos subidos</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="documents.length === 0" class="py-8 text-center text-muted-foreground">
                        No hay documentos subidos.
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="document in documents" :key="document.id" class="flex items-center justify-between rounded-lg border p-4">
                            <div>
                                <p class="font-medium">{{ document.original_name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ docTypeLabel(document.type) }} · {{ formatBytes(document.size) }} · {{ formatDate(document.created_at) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    as="a"
                                    :href="route('applicants.documents.preview', [applicant.id, document.id])"
                                    target="_blank"
                                >
                                    <Eye class="mr-1 h-3.5 w-3.5" />
                                    Vista previa
                                </Button>
                                <Button as-child variant="outline" size="sm">
                                    <Link :href="route('applicants.documents.download', [applicant.id, document.id])">
                                        <Download class="mr-1 h-3.5 w-3.5" />
                                        Descargar
                                    </Link>
                                </Button>
                                <Button v-if="canCreateApplicants" as-child variant="destructive" size="sm">
                                    <Link
                                        :href="route('applicants.documents.destroy', [applicant.id, document.id])"
                                        method="delete"
                                        as="button"
                                    >
                                        <Trash2 class="mr-1 h-3.5 w-3.5" />
                                        Eliminar
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
