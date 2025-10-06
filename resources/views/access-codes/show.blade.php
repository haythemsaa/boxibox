@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-key me-2"></i>Code d'Accès #{{ $accessCode->id }}
        </h1>
        <div>
            <a href="{{ route('access-codes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="{{ route('access-codes.edit', $accessCode) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Modifier
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Informations Principales -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations du Code</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Type :</strong>
                            <span class="badge bg-info">
                                @if($accessCode->type === 'pin')
                                    <i class="fas fa-hashtag"></i> Code PIN
                                @elseif($accessCode->type === 'qr')
                                    <i class="fas fa-qrcode"></i> QR Code
                                @else
                                    <i class="fas fa-id-card"></i> Badge
                                @endif
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Statut :</strong>
                            @if($accessCode->statut === 'actif')
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Actif</span>
                            @elseif($accessCode->statut === 'expire')
                                <span class="badge bg-warning"><i class="fas fa-clock"></i> Expiré</span>
                            @elseif($accessCode->statut === 'suspendu')
                                <span class="badge bg-warning"><i class="fas fa-pause-circle"></i> Suspendu</span>
                            @else
                                <span class="badge bg-danger"><i class="fas fa-ban"></i> Révoqué</span>
                            @endif
                        </div>
                    </div>

                    @if($accessCode->type === 'pin')
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Code PIN :</strong>
                            <div class="alert alert-info mt-2">
                                <h3 class="mb-0 text-center font-monospace">{{ $accessCode->code_pin }}</h3>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($accessCode->type === 'qr' && $accessCode->qr_code_path)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>QR Code :</strong>
                            <div class="text-center mt-2">
                                <img src="{{ asset('storage/' . $accessCode->qr_code_path) }}" alt="QR Code" class="img-fluid" style="max-width: 300px;">
                                <div class="mt-2">
                                    <a href="{{ route('access-codes.download-qr', $accessCode) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Client :</strong><br>
                            <a href="{{ route('clients.show', $accessCode->client) }}">
                                {{ $accessCode->client->nom }} {{ $accessCode->client->prenom }}
                            </a>
                        </div>
                        <div class="col-md-6">
                            <strong>Box :</strong><br>
                            @if($accessCode->box)
                                <a href="{{ route('boxes.show', $accessCode->box) }}">
                                    Box {{ $accessCode->box->numero }}
                                </a>
                            @else
                                <span class="text-muted">Non assigné</span>
                            @endif
                        </div>
                    </div>

                    @if($accessCode->temporaire)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date début :</strong><br>
                            {{ $accessCode->date_debut ? $accessCode->date_debut->format('d/m/Y H:i') : '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date fin :</strong><br>
                            {{ $accessCode->date_fin ? $accessCode->date_fin->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Utilisations :</strong><br>
                            {{ $accessCode->nb_utilisations }}
                            @if($accessCode->max_utilisations)
                                / {{ $accessCode->max_utilisations }}
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Dernière utilisation :</strong><br>
                            {{ $accessCode->derniere_utilisation ? $accessCode->derniere_utilisation->format('d/m/Y H:i') : 'Jamais utilisé' }}
                        </div>
                    </div>

                    @if($accessCode->notes)
                    <div class="row mb-3">
                        <div class="col-12">
                            <strong>Notes :</strong><br>
                            <div class="alert alert-light mt-2">{{ $accessCode->notes }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Historique des accès -->
            @if($accessCode->logs && $accessCode->logs->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Historique des Accès ({{ $accessCode->logs->count() }} derniers)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Date/Heure</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accessCode->logs as $log)
                                <tr>
                                    <td>{{ $log->date_heure->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        @if($log->type_acces === 'entree')
                                            <span class="badge bg-success"><i class="fas fa-sign-in-alt"></i> Entrée</span>
                                        @else
                                            <span class="badge bg-info"><i class="fas fa-sign-out-alt"></i> Sortie</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->succes)
                                            <span class="badge bg-success"><i class="fas fa-check"></i> Autorisé</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-times"></i> Refusé</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->notes ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    @if($accessCode->statut === 'actif')
                        <form method="POST" action="{{ route('access-codes.suspend', $accessCode) }}" class="mb-2">
                            @csrf
                            <div class="mb-2">
                                <input type="text" name="reason" class="form-control" placeholder="Raison de la suspension">
                            </div>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-pause"></i> Suspendre
                            </button>
                        </form>

                        <form method="POST" action="{{ route('access-codes.revoke', $accessCode) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir révoquer ce code ?')">
                            @csrf
                            <div class="mb-2">
                                <input type="text" name="reason" class="form-control" placeholder="Raison de la révocation">
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-ban"></i> Révoquer
                            </button>
                        </form>
                    @elseif($accessCode->statut === 'suspendu')
                        <form method="POST" action="{{ route('access-codes.reactivate', $accessCode) }}">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-play"></i> Réactiver
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Métadonnées</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Créé le :</strong><br>
                        {{ $accessCode->created_at->format('d/m/Y H:i') }}
                    </p>
                    <p class="mb-0">
                        <strong>Modifié le :</strong><br>
                        {{ $accessCode->updated_at->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
