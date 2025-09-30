@extends('layouts.app')

@section('title', 'Prospects')

@section('actions')
@can('create_prospects')
    <a href="{{ route('prospects.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau Prospect
    </a>
@endcan
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['total'] }}</div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-info">{{ $stats['nouveaux'] }}</div>
                <div class="stat-label">Nouveaux</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-warning">{{ $stats['contactes'] }}</div>
                <div class="stat-label">Contactés</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-success">{{ $stats['interesses'] }}</div>
                <div class="stat-label">Intéressés</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-primary">{{ $stats['convertis'] }}</div>
                <div class="stat-label">Convertis</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card text-center">
                <div class="stat-number text-danger">{{ $stats['perdus'] }}</div>
                <div class="stat-label">Perdus</div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                           placeholder="Nom, email, téléphone...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="">Tous</option>
                        <option value="nouveau" {{ request('statut') == 'nouveau' ? 'selected' : '' }}>Nouveau</option>
                        <option value="contacte" {{ request('statut') == 'contacte' ? 'selected' : '' }}>Contacté</option>
                        <option value="interesse" {{ request('statut') == 'interesse' ? 'selected' : '' }}>Intéressé</option>
                        <option value="perdu" {{ request('statut') == 'perdu' ? 'selected' : '' }}>Perdu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Origine</label>
                    <select name="origine" class="form-select">
                        <option value="">Toutes</option>
                        <option value="site_web" {{ request('origine') == 'site_web' ? 'selected' : '' }}>Site web</option>
                        <option value="telephone" {{ request('origine') == 'telephone' ? 'selected' : '' }}>Téléphone</option>
                        <option value="visite" {{ request('origine') == 'visite' ? 'selected' : '' }}>Visite</option>
                        <option value="recommandation" {{ request('origine') == 'recommandation' ? 'selected' : '' }}>Recommandation</option>
                        <option value="publicite" {{ request('origine') == 'publicite' ? 'selected' : '' }}>Publicité</option>
                        <option value="autre" {{ request('origine') == 'autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Assigné à</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Tous</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">État</label>
                    <select name="converted" class="form-select">
                        <option value="">Tous</option>
                        <option value="0" {{ request('converted') === '0' ? 'selected' : '' }}>Non convertis</option>
                        <option value="1" {{ request('converted') === '1' ? 'selected' : '' }}>Convertis</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des prospects -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Contact</th>
                            <th>Origine</th>
                            <th>Statut</th>
                            <th>Assigné à</th>
                            <th>Dernière interaction</th>
                            <th>Relance prévue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prospects as $prospect)
                            <tr class="{{ $prospect->converted_to_client_at ? 'table-success' : '' }}">
                                <td>
                                    <strong>{{ $prospect->prenom }} {{ $prospect->nom }}</strong>
                                    @if($prospect->converted_to_client_at)
                                        <span class="badge bg-success ms-2">Converti</span>
                                    @endif
                                </td>
                                <td>
                                    @if($prospect->email)
                                        <div><i class="fas fa-envelope text-muted me-1"></i>{{ $prospect->email }}</div>
                                    @endif
                                    @if($prospect->telephone)
                                        <div><i class="fas fa-phone text-muted me-1"></i>{{ $prospect->telephone }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $prospect->origine)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($prospect->statut) {
                                            'nouveau' => 'bg-info',
                                            'contacte' => 'bg-warning',
                                            'interesse' => 'bg-success',
                                            'perdu' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($prospect->statut) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $prospect->assignedUser->name ?? '-' }}
                                </td>
                                <td>
                                    @if($prospect->interactions->count() > 0)
                                        {{ $prospect->interactions->first()->date_interaction->format('d/m/Y') }}
                                        <small class="text-muted d-block">
                                            {{ $prospect->interactions->first()->type_interaction }}
                                        </small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($prospect->date_relance)
                                        <span class="badge {{ $prospect->date_relance->isPast() ? 'bg-danger' : 'bg-warning' }}">
                                            {{ $prospect->date_relance->format('d/m/Y') }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('prospects.show', $prospect) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('edit_prospects')
                                            @if(!$prospect->converted_to_client_at)
                                                <a href="{{ route('prospects.edit', $prospect) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        @endcan
                                        @can('convert_prospects')
                                            @if(!$prospect->converted_to_client_at)
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                        onclick="convertProspect({{ $prospect->id }})">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-3"></i>
                                    <p>Aucun prospect trouvé.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $prospects->appends(request()->query())->links() }}
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
            <form method="POST" id="convertForm">
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
@endsection

@push('scripts')
<script>
function convertProspect(prospectId) {
    const form = document.getElementById('convertForm');
    form.action = `/commercial/prospects/${prospectId}/convert`;

    const modal = new bootstrap.Modal(document.getElementById('convertModal'));
    modal.show();
}
</script>
@endpush