<template>
    <ClientLayout title="Mes Fichiers">
        <div class="mb-4">
            <h1 class="h3">Mes Fichiers</h1>
            <p class="text-muted">Consultez et téléchargez vos documents</p>
        </div>

        <!-- Zone d'upload -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-cloud-upload-alt me-2"></i>Envoyer un document</h5>
            </div>
            <div class="card-body">
                <form @submit.prevent="uploadDocument" enctype="multipart/form-data">
                    <!-- Zone de glisser-déposer -->
                    <div
                        ref="uploadZone"
                        class="upload-zone"
                        :class="{ dragover: isDragOver }"
                        @dragenter.prevent="handleDragEnter"
                        @dragover.prevent="handleDragOver"
                        @dragleave.prevent="handleDragLeave"
                        @drop.prevent="handleDrop"
                    >
                        <div class="upload-zone-content">
                            <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                            <h5>Glissez-déposez votre fichier ici</h5>
                            <p class="text-muted mb-3">ou</p>
                            <label for="document" class="btn btn-primary">
                                <i class="fas fa-folder-open me-1"></i>Parcourir les fichiers
                            </label>
                            <input
                                type="file"
                                ref="fileInput"
                                class="d-none"
                                id="document"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                multiple
                                @change="handleFileSelect"
                            >
                            <p class="text-muted small mt-3 mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Formats : PDF, Word, Images | Taille max : 20 Mo par fichier | Upload multiple supporté
                            </p>
                        </div>
                    </div>

                    <div v-if="errors.document" class="text-danger mt-2">{{ errors.document }}</div>

                    <!-- Nom du fichier -->
                    <div class="mb-3 mt-3">
                        <label for="nom_document" class="form-label">Nom du document (optionnel)</label>
                        <input
                            type="text"
                            v-model="form.nom_document"
                            class="form-control"
                            :class="{ 'is-invalid': errors.nom_document }"
                            id="nom_document"
                            placeholder="Laissez vide pour utiliser le nom du fichier"
                        >
                        <div v-if="errors.nom_document" class="invalid-feedback">{{ errors.nom_document }}</div>
                    </div>

                    <!-- Aperçu des fichiers sélectionnés -->
                    <div v-if="selectedFiles.length > 0" class="mt-3">
                        <h6>Fichiers sélectionnés ({{ selectedFiles.length }})</h6>
                        <div class="list-group">
                            <div v-for="(file, index) in selectedFiles" :key="index" class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <i :class="getFileIcon(file)" class="fa-2x me-3"></i>
                                    <div class="flex-grow-1">
                                        <strong>{{ file.name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ formatFileSize(file.size) }}</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" @click="removeFile(index)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3" :disabled="selectedFiles.length === 0 || uploading">
                        <i class="fas fa-upload me-1"></i>
                        <span v-if="uploading">Envoi en cours... ({{ uploadProgress }}%)</span>
                        <span v-else>Envoyer {{ selectedFiles.length }} fichier(s)</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Liste des documents -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>Mes Documents</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;"><i class="fas fa-file"></i></th>
                                <th>Nom du document</th>
                                <th>Type</th>
                                <th>Date d'ajout</th>
                                <th>Taille</th>
                                <th>Envoyé par</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="document in documents.data" :key="document.id">
                                <td class="text-center">
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                </td>
                                <td>
                                    <strong>{{ document.nom_document || document.nom_original }}</strong>
                                    <br v-if="document.description">
                                    <small v-if="document.description" class="text-muted">
                                        {{ truncate(document.description, 50) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-danger">PDF</span>
                                </td>
                                <td>
                                    {{ formatDate(document.created_at) }}
                                    <br>
                                    <small class="text-muted">{{ formatTime(document.created_at) }}</small>
                                </td>
                                <td>
                                    <span v-if="document.taille">
                                        {{ formatFileSize(document.taille) }}
                                    </span>
                                    <span v-else class="text-muted">N/A</span>
                                </td>
                                <td>
                                    <span v-if="document.uploaded_by === $page.props.auth?.user?.id" class="badge bg-info">
                                        <i class="fas fa-user me-1"></i>Vous
                                    </span>
                                    <span v-else class="badge bg-secondary">
                                        <i class="fas fa-building me-1"></i>BOXIBOX
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button
                                            type="button"
                                            @click="previewDocument(document)"
                                            class="btn btn-outline-info"
                                            title="Prévisualiser"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a
                                            :href="route('client.documents.download', document.id)"
                                            class="btn btn-outline-primary"
                                            title="Télécharger"
                                        >
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button
                                            v-if="document.uploaded_by === $page.props.auth?.user?.id"
                                            type="button"
                                            @click="confirmDelete(document)"
                                            class="btn btn-outline-danger"
                                            title="Supprimer"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!documents.data || documents.data.length === 0">
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                    <p class="text-muted mb-1">Aucun document</p>
                                    <small class="text-muted">Envoyez votre premier document en utilisant le formulaire ci-dessus</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="documents.last_page > 1" class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Affichage de {{ documents.from }} à {{ documents.to }} sur {{ documents.total }} documents
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="{ disabled: !documents.prev_page_url }">
                                <a class="page-link" @click.prevent="changePage(documents.current_page - 1)">
                                    Précédent
                                </a>
                            </li>
                            <li class="page-item" :class="{ disabled: !documents.next_page_url }">
                                <a class="page-link" @click.prevent="changePage(documents.current_page + 1)">
                                    Suivant
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Modal Preview PDF -->
        <div v-if="showPreview" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-file-pdf text-danger me-2"></i>
                            {{ previewDocument.nom_document || previewDocument.nom_original }}
                        </h5>
                        <button type="button" class="btn-close" @click="closePreview"></button>
                    </div>
                    <div class="modal-body p-0" style="height: 80vh;">
                        <iframe
                            v-if="previewDocument"
                            :src="route('client.documents.download', previewDocument.id)"
                            class="w-100 h-100"
                            style="border: none;"
                        ></iframe>
                    </div>
                    <div class="modal-footer">
                        <a
                            :href="route('client.documents.download', previewDocument.id)"
                            class="btn btn-primary"
                            download
                        >
                            <i class="fas fa-download me-1"></i>Télécharger
                        </a>
                        <button type="button" class="btn btn-secondary" @click="closePreview">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

export default {
    components: {
        ClientLayout
    },

    props: {
        documents: {
            type: Object,
            required: true
        },
        errors: {
            type: Object,
            default: () => ({})
        }
    },

    setup() {
        const toast = useToast();
        return { toast };
    },

    data() {
        return {
            selectedFiles: [],
            isDragOver: false,
            uploading: false,
            uploadProgress: 0,
            showPreview: false,
            previewDocument: null,
            form: {
                nom_document: ''
            }
        };
    },

    methods: {
        handleDragEnter(e) {
            this.isDragOver = true;
        },

        handleDragOver(e) {
            this.isDragOver = true;
        },

        handleDragLeave(e) {
            if (e.target === this.$refs.uploadZone) {
                this.isDragOver = false;
            }
        },

        handleDrop(e) {
            this.isDragOver = false;
            const files = Array.from(e.dataTransfer.files);
            this.processFiles(files);
        },

        handleFileSelect(e) {
            const files = Array.from(e.target.files);
            this.processFiles(files);
        },

        processFiles(files) {
            const validFiles = [];
            const maxSize = 20 * 1024 * 1024; // 20 Mo
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/jpg',
                'image/png'
            ];

            for (const file of files) {
                // Vérifier le type
                if (!allowedTypes.includes(file.type)) {
                    this.toast.warning(`${file.name} : Type de fichier non supporté`);
                    continue;
                }

                // Vérifier la taille
                if (file.size > maxSize) {
                    this.toast.warning(`${file.name} : Fichier trop volumineux (max 20 Mo)`);
                    continue;
                }

                validFiles.push(file);
            }

            if (validFiles.length > 0) {
                this.selectedFiles.push(...validFiles);
                this.toast.success(`${validFiles.length} fichier(s) ajouté(s)`);
            }
        },

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
        },

        getFileIcon(file) {
            if (file.type === 'application/pdf') {
                return 'fas fa-file-pdf text-danger';
            } else if (file.type.startsWith('image/')) {
                return 'fas fa-file-image text-info';
            } else if (file.type.includes('word')) {
                return 'fas fa-file-word text-primary';
            }
            return 'fas fa-file text-secondary';
        },

        uploadDocument() {
            if (this.selectedFiles.length === 0) return;

            this.uploading = true;
            this.uploadProgress = 0;

            const formData = new FormData();

            // Ajouter tous les fichiers
            this.selectedFiles.forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });

            if (this.form.nom_document) {
                formData.append('nom_document', this.form.nom_document);
            }

            router.post(route('client.documents.upload'), formData, {
                onSuccess: () => {
                    this.selectedFiles = [];
                    this.form.nom_document = '';
                    this.$refs.fileInput.value = '';
                    this.uploading = false;
                    this.uploadProgress = 0;
                    this.toast.saveSuccess('Document(s)');
                },
                onError: (errors) => {
                    this.uploading = false;
                    this.uploadProgress = 0;
                    this.toast.error('Erreur lors de l\'upload des documents');
                },
                onProgress: (progress) => {
                    this.uploadProgress = Math.round((progress.loaded / progress.total) * 100);
                }
            });
        },

        previewDocument(document) {
            this.previewDocument = document;
            this.showPreview = true;
        },

        closePreview() {
            this.showPreview = false;
            this.previewDocument = null;
        },

        confirmDelete(document) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce document ?')) {
                router.delete(route('client.documents.delete', document.id), {
                    onSuccess: () => {
                        this.toast.deleteSuccess('Document');
                    },
                    onError: () => {
                        this.toast.error('Erreur lors de la suppression');
                    }
                });
            }
        },

        changePage(page) {
            if (page < 1 || page > this.documents.last_page) return;
            router.get(route('client.documents'), { page }, {
                preserveState: true,
                preserveScroll: true
            });
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR');
        },

        formatTime(date) {
            if (!date) return '';
            return new Date(date).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 Octets';
            const k = 1024;
            const sizes = ['Octets', 'Ko', 'Mo', 'Go'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        },

        truncate(str, length) {
            if (!str) return '';
            return str.length > length ? str.substring(0, length) + '...' : str;
        }
    }
};
</script>

<style scoped>
.upload-zone {
    border: 3px dashed #dee2e6;
    border-radius: 10px;
    padding: 40px;
    text-align: center;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
}

.upload-zone:hover {
    border-color: #0d6efd;
    background-color: #e7f3ff;
}

.upload-zone.dragover {
    border-color: #0d6efd;
    background-color: #cfe2ff;
    transform: scale(1.02);
}

.upload-zone-content {
    pointer-events: none;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
