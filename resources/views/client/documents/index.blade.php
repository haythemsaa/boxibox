@extends('layouts.client')

@section('title', 'Mes Fichiers')

@section('content')
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
        <form action="{{ route('client.documents.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <!-- Zone de glisser-déposer -->
            <div class="upload-zone" id="uploadZone">
                <div class="upload-zone-content">
                    <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                    <h5>Glissez-déposez votre fichier ici</h5>
                    <p class="text-muted mb-3">ou</p>
                    <label for="document" class="btn btn-primary">
                        <i class="fas fa-folder-open me-1"></i>Parcourir les fichiers
                    </label>
                    <input type="file"
                           class="d-none @error('document') is-invalid @enderror"
                           id="document"
                           name="document"
                           accept=".pdf">
                    <p class="text-muted small mt-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Format accepté : PDF uniquement | Taille maximale : 20 Mo
                    </p>
                </div>
                @error('document')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Nom du fichier -->
            <div class="mb-3 mt-3">
                <label for="nom_document" class="form-label">Nom du document (optionnel)</label>
                <input type="text"
                       class="form-control @error('nom_document') is-invalid @enderror"
                       id="nom_document"
                       name="nom_document"
                       placeholder="Laissez vide pour utiliser le nom du fichier"
                       value="{{ old('nom_document') }}">
                @error('nom_document')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Aperçu du fichier sélectionné -->
            <div id="filePreview" class="alert alert-info d-none">
                <div class="d-flex align-items-center">
                    <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                    <div class="flex-grow-1">
                        <strong id="fileName"></strong>
                        <br>
                        <small id="fileSize" class="text-muted"></small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeFile">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                <i class="fas fa-upload me-1"></i>Envoyer le document
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
                    @forelse($documents as $document)
                    <tr>
                        <td class="text-center">
                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                        </td>
                        <td>
                            <strong>{{ $document->nom_document }}</strong>
                            @if($document->description)
                                <br>
                                <small class="text-muted">{{ Str::limit($document->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-danger">PDF</span>
                        </td>
                        <td>
                            {{ $document->created_at->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $document->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            @if($document->taille_fichier)
                                {{ number_format($document->taille_fichier / 1024 / 1024, 2) }} Mo
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($document->uploaded_by == Auth::id())
                                <span class="badge bg-info">
                                    <i class="fas fa-user me-1"></i>Vous
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-building me-1"></i>BOXIBOX
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('client.documents.download', $document) }}"
                                   class="btn btn-outline-primary"
                                   title="Télécharger">
                                    <i class="fas fa-download"></i>
                                </a>
                                @if($document->uploaded_by == Auth::id())
                                <form action="{{ route('client.documents.delete', $document) }}"
                                      method="POST"
                                      class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-outline-danger"
                                            title="Supprimer"
                                            onclick="confirmDelete(event, 'Le document sera définitivement supprimé.')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-1">Aucun document</p>
                            <small class="text-muted">Envoyez votre premier document en utilisant le formulaire ci-dessus</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($documents->hasPages())
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Affichage de {{ $documents->firstItem() }} à {{ $documents->lastItem() }} sur {{ $documents->total() }} documents
            </div>
            <div>
                {{ $documents->links() }}
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
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
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('document');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFileBtn = document.getElementById('removeFile');
    const submitBtn = document.getElementById('submitBtn');

    // Gestion du glisser-déposer
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, () => {
            uploadZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, () => {
            uploadZone.classList.remove('dragover');
        }, false);
    });

    uploadZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    // Gestion de la sélection de fichier
    fileInput.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });

    // Afficher l'aperçu du fichier
    function handleFileSelect(file) {
        // Vérifier le type de fichier
        if (file.type !== 'application/pdf') {
            alert('Seuls les fichiers PDF sont acceptés.');
            fileInput.value = '';
            return;
        }

        // Vérifier la taille (20 Mo max)
        const maxSize = 20 * 1024 * 1024; // 20 Mo en octets
        if (file.size > maxSize) {
            alert('La taille du fichier ne doit pas dépasser 20 Mo.');
            fileInput.value = '';
            return;
        }

        // Afficher l'aperçu
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        filePreview.classList.remove('d-none');
        submitBtn.disabled = false;

        // Pré-remplir le nom du document si vide
        const nomDocumentInput = document.getElementById('nom_document');
        if (!nomDocumentInput.value) {
            nomDocumentInput.value = file.name.replace('.pdf', '');
        }
    }

    // Supprimer le fichier sélectionné
    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        filePreview.classList.add('d-none');
        submitBtn.disabled = true;
    });

    // Formater la taille du fichier
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Octets';
        const k = 1024;
        const sizes = ['Octets', 'Ko', 'Mo', 'Go'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
});
</script>
@endpush
@endsection