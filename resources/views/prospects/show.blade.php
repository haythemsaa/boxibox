@extends('layouts.app')

@section('title')
    Prospect - {{ $prospect->prenom }} {{ $prospect->nom }}
@endsection

@section('actions')
@can('edit_prospects')
    @if(!$prospect->converted_to_client_at)
        <a href="{{ route('prospects.edit', $prospect) }}" class="btn btn-secondary me-2">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
    @endif
@endcan
@can('convert_prospects')
    @if(!$prospect->converted_to_client_at)
        <button type="button" class="btn btn-success" onclick="convertProspect()">
            <i class="fas fa-user-plus me-2"></i>Convertir en client
        </button>
    @endif
@endcan
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Informations générales -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Informations générales
                    </h5>
                    @php
                        $badgeClass = match($prospect->statut) {
                            'nouveau' => 'bg-info',
                            'contacte' => 'bg-warning',
                            'interesse' => 'bg-success',
                            'perdu' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($prospect->statut) }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="130">Nom complet :</th>
                                    <td>{{ $prospect->prenom }} {{ $prospect->nom }}</td>
                                </tr>
                                <tr>
                                    <th>Email :</th>
                                    <td>
                                        @if($prospect->email)
                                            <a href="mailto:{{ $prospect->email }}">{{ $prospect->email }}</a>
                                        @else
                                            <span class="text-muted">Non renseigné</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Téléphone :</th>
                                    <td>
                                        @if($prospect->telephone)
                                            <a href="tel:{{ $prospect->telephone }}">{{ $prospect->telephone }}</a>
                                        @else
                                            <span class="text-muted">Non renseigné</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Origine :</th>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $prospect->origine)) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="130">Adresse :</th>
                                    <td>
                                        @if($prospect->adresse)
                                            {{ $prospect->adresse }}<br>
                                            {{ $prospect->code_postal }} {{ $prospect->ville }}
                                        @else
                                            <span class="text-muted">Non renseignée</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Assigné à :</th>
                                    <td>{{ $prospect->assignedUser->name ?? 'Non assigné' }}</td>
                                </tr>
                                <tr>
                                    <th>Créé le :</th>
                                    <td>{{ $prospect->created_at->format('d/m/Y à H:i') }}</td>
                                </tr>
                                @if($prospect->date_relance)
                                    <tr>
                                        <th>Relance prévue :</th>
                                        <td>
                                            <span class="badge {{ $prospect->date_relance->isPast() ? 'bg-danger' : 'bg-warning' }}">
                                                {{ $prospect->date_relance->format('d/m/Y') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($prospect->notes)
                        <hr>
                        <div>
                            <strong>Notes :</strong>
                            <p class="mt-2">{{ $prospect->notes }}</p>
                        </div>
                    @endif

                    @if($prospect->converted_to_client_at)
                        <hr>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Ce prospect a été converti en client le {{ $prospect->converted_to_client_at->format('d/m/Y à H:i') }}.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historique des interactions -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-comments me-2"></i>Historique des interactions ({{ $interactions->count() }})
                    </h5>
                    @if(!$prospect->converted_to_client_at)
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addInteractionModal">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    @forelse($interactions as $interaction)
                        <div class="d-flex mb-3 {{ !$loop->last ? 'border-bottom pb-3' : '' }}">
                            <div class="flex-shrink-0 me-3">
                                @php
                                    $iconClass = match($interaction->type_interaction) {
                                        'appel_entrant', 'appel_sortant' => 'fa-phone',
                                        'email' => 'fa-envelope',
                                        'visite' => 'fa-home',
                                        'courrier' => 'fa-mail-bulk',
                                        'sms' => 'fa-sms',
                                        default => 'fa-comment'
                                    };
                                    $bgClass = match($interaction->resultat) {
                                        'interesse' => 'bg-success',
                                        'pas_interesse' => 'bg-danger',
                                        'demande_rappel' => 'bg-warning',
                                        'rdv_pris' => 'bg-info',
                                        'sans_reponse' => 'bg-secondary',
                                        default => 'bg-primary'
                                    };
                                @endphp
                                <div class="rounded-circle {{ $bgClass }} text-white d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px;">
                                    <i class="fas {{ $iconClass }}"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $interaction->getLibelleTypeAttribute() }}</h6>
                                        <small class="text-muted">
                                            {{ $interaction->date_interaction->format('d/m/Y à H:i') }}
                                            @if($interaction->duree_minutes)
                                                - {{ $interaction->duree_minutes }} min
                                            @endif
                                            - par {{ $interaction->createdBy->name }}
                                        </small>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        {{ $interaction->getLibelleResultatAttribute() }}
                                    </span>
                                </div>
                                <p class="mb-1 mt-2">{{ $interaction->contenu }}</p>
                                @if($interaction->date_relance_prevue)
                                    <small class="text-warning">
                                        <i class="fas fa-clock me-1"></i>Relance prévue le {{ $interaction->date_relance_prevue->format('d/m/Y') }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-comments fa-2x mb-3"></i>
                            <p>Aucune interaction enregistrée.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Actions rapides -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Actions rapides</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($prospect->email)
                            <a href="mailto:{{ $prospect->email }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-envelope me-2"></i>Envoyer un email
                            </a>
                        @endif
                        @if($prospect->telephone)
                            <a href="tel:{{ $prospect->telephone }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-phone me-2"></i>Appeler
                            </a>
                        @endif
                        @if(!$prospect->converted_to_client_at)
                            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#addInteractionModal">
                                <i class="fas fa-plus me-2"></i>Ajouter interaction
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Statistiques</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="fs-4 fw-bold text-primary">{{ $interactions->count() }}</div>
                                <small class="text-muted">Interactions</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="fs-4 fw-bold text-info">{{ $prospect->created_at->diffInDays(now()) }}</div>
                            <small class="text-muted">Jours</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$prospect->converted_to_client_at)
    <!-- Modal d'ajout d'interaction -->
    <div class="modal fade" id="addInteractionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une interaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('prospects.add-interaction', $prospect) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Type d'interaction *</label>
                                    <select name="type_interaction" class="form-select" required>
                                        <option value="appel_entrant">Appel entrant</option>
                                        <option value="appel_sortant">Appel sortant</option>
                                        <option value="email">Email</option>
                                        <option value="visite">Visite</option>
                                        <option value="courrier">Courrier</option>
                                        <option value="sms">SMS</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date et heure *</label>
                                    <input type="datetime-local" name="date_interaction" class="form-control"
                                           value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Durée (minutes)</label>
                                    <input type="number" name="duree_minutes" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Résultat *</label>
                                    <select name="resultat" class="form-select" required>
                                        <option value="interesse">Intéressé</option>
                                        <option value="pas_interesse">Pas intéressé</option>
                                        <option value="demande_rappel">Demande rappel</option>
                                        <option value="rdv_pris">RDV pris</option>
                                        <option value="sans_reponse">Sans réponse</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contenu de l'interaction *</label>
                            <textarea name="contenu" class="form-control" rows="4" required
                                      placeholder="Décrivez le contenu de l'interaction..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date de relance prévue</label>
                            <input type="date" name="date_relance_prevue" class="form-control"
                                   min="{{ now()->addDay()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de conversion -->
    <div class="modal fade" id="convertModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Convertir en client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('prospects.convert', $prospect) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Type de client</label>
                            <select name="type_client" class="form-select" required>
                                <option value="particulier">Particulier</option>
                                <option value="entreprise">Entreprise</option>
                            </select>
                        </div>
                        <p class="text-muted">
                            Les informations du prospect seront copiées dans la fiche client.
                            Vous pourrez les compléter après la conversion.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Convertir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function convertProspect() {
    const modal = new bootstrap.Modal(document.getElementById('convertModal'));
    modal.show();
}
</script>
@endpush