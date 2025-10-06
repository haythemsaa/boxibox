@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-key me-2"></i>Gestion des Codes d'Accès
        </h1>
        <a href="{{ route('access-codes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau Code
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="search" class="form-control" placeholder="Code PIN ou nom client" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="">Tous</option>
                        <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="expire" {{ request('statut') === 'expire' ? 'selected' : '' }}>Expiré</option>
                        <option value="suspendu" {{ request('statut') === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                        <option value="revoque" {{ request('statut') === 'revoque' ? 'selected' : '' }}>Révoqué</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">Tous</option>
                        <option value="pin" {{ request('type') === 'pin' ? 'selected' : '' }}>Code PIN</option>
                        <option value="qr" {{ request('type') === 'qr' ? 'selected' : '' }}>QR Code</option>
                        <option value="badge" {{ request('type') === 'badge' ? 'selected' : '' }}>Badge</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-select">
                        <option value="">Tous les clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->nom }} {{ $client->prenom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des codes -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Codes d'Accès ({{ $accessCodes->total() }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Code/Info</th>
                            <th>Box</th>
                            <th>Statut</th>
                            <th>Validité</th>
                            <th>Utilisations</th>
                            <th>Dernière Utilisation</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accessCodes as $code)
                        <tr>
                            <td>
                                <strong>{{ $code->client->nom }} {{ $code->client->prenom }}</strong>
                                <br>
                                <small class="text-muted">{{ $code->client->email }}</small>
                            </td>
                            <td>
                                @if($code->type === 'pin')
                                    <span class="badge bg-primary"><i class="fas fa-keyboard"></i> PIN</span>
                                @elseif($code->type === 'qr')
                                    <span class="badge bg-info"><i class="fas fa-qrcode"></i> QR Code</span>
                                @else
                                    <span class="badge bg-secondary"><i class="fas fa-id-card"></i> Badge</span>
                                @endif
                            </td>
                            <td>
                                @if($code->type === 'pin')
                                    <code class="fs-5">{{ $code->code_pin }}</code>
                                @elseif($code->type === 'qr')
                                    <a href="{{ route('access-codes.download-qr', $code) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> QR
                                    </a>
                                @endif
                            </td>
                            <td>{{ $code->box->numero ?? 'Tous' }}</td>
                            <td>
                                @if($code->statut === 'actif')
                                    <span class="badge bg-success">Actif</span>
                                @elseif($code->statut === 'expire')
                                    <span class="badge bg-danger">Expiré</span>
                                @elseif($code->statut === 'suspendu')
                                    <span class="badge bg-warning">Suspendu</span>
                                @else
                                    <span class="badge bg-dark">Révoqué</span>
                                @endif
                            </td>
                            <td>
                                @if($code->date_debut && $code->date_fin)
                                    <small>
                                        Du {{ $code->date_debut->format('d/m/Y') }}<br>
                                        Au {{ $code->date_fin->format('d/m/Y') }}
                                    </small>
                                @elseif($code->date_fin)
                                    <small>Jusqu'au {{ $code->date_fin->format('d/m/Y') }}</small>
                                @else
                                    <span class="badge bg-secondary">Permanent</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $code->nb_utilisations }}
                                @if($code->max_utilisations)
                                    / {{ $code->max_utilisations }}
                                @endif
                            </td>
                            <td>
                                @if($code->derniere_utilisation)
                                    <small>{{ $code->derniere_utilisation->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="text-muted">Jamais</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('access-codes.show', $code) }}" class="btn btn-sm btn-info" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('access-codes.edit', $code) }}" class="btn btn-sm btn-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($code->statut === 'actif')
                                    <form action="{{ route('access-codes.suspend', $code) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Suspendre">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    </form>
                                @elseif($code->statut === 'suspendu')
                                    <form action="{{ route('access-codes.reactivate', $code) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Réactiver">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun code d'accès trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $accessCodes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
