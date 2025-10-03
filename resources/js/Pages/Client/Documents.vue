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
                                accept=".pdf"
                                @change="handleFileSelect"
                            >
                            <p class="text-muted small mt-3 mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Format accepté : PDF uniquement | Taille maximale : 20 Mo
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

                    <!-- Aperçu du fichier sélectionné -->
                    <div v-if="selectedFile" class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                            <div class="flex-grow-1">
                                <strong>{{ selectedFile.name }}</strong>
                                <br>
                                <small class="text-muted">{{ formatFileSize(selectedFile.size) }}</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" @click="removeFile">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" :disabled="!selectedFile || uploading">
                        <i class="fas fa-upload me-1"></i>
                        <span v-if="uploading">Envoi en cours...</span>
                        <span v-else>Envoyer le document</span>
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
    </ClientLayout>
</template>

<script>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { router } from '@inertiajs/vue3';

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

    data() {
        return {
            selectedFile: null,
            isDragOver: false,
            uploading: false,
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
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.processFile(files[0]);
            }
        },

        handleFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.processFile(files[0]);
            }
        },

        processFile(file) {
            // Vérifier le type
            if (file.type !== 'application/pdf') {
                alert('Seuls les fichiers PDF sont acceptés.');
                return;
            }

            // Vérifier la taille (20 Mo max)
            const maxSize = 20 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('La taille du fichier ne doit pas dépasser 20 Mo.');
                return;
            }

            this.selectedFile = file;

            // Pré-remplir le nom si vide
            if (!this.form.nom_document) {
                this.form.nom_document = file.name.replace('.pdf', '');
            }
        },

        removeFile() {
            this.selectedFile = null;
            this.$refs.fileInput.value = '';
        },

        uploadDocument() {
            if (!this.selectedFile) return;

            this.uploading = true;

            const formData = new FormData();
            formData.append('file', this.selectedFile);
            if (this.form.nom_document) {
                formData.append('nom_document', this.form.nom_document);
            }

            router.post(route('client.documents.upload'), formData, {
                onSuccess: () => {
                    this.selectedFile = null;
                    this.form.nom_document = '';
                    this.$refs.fileInput.value = '';
                    this.uploading = false;
                },
                onError: () => {
                    this.uploading = false;
                }
            });
        },

        confirmDelete(document) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce document ?')) {
                router.delete(route('client.documents.delete', document.id));
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
