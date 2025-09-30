@extends('layouts.client')

@section('title', 'Mes Relances')

@section('content')
<div class="mb-4">
    <h1 class="h3">Mes Relances</h1>
    <p class="text-muted">Historique des rappels de paiement</p>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date Rappel</th>
                        <th>Facture Concernée</th>
                        <th>Type</th>
                        <th>Mode d'Envoi</th>
                        <th class="text-end">Montant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($relances as $relance)
                    <tr>
                        <td>
                            <strong>{{ $relance->date_rappel->format('d/m/Y') }}</strong>
                            <br>
                            <small class="text-muted">{{ $relance->date_rappel->diffForHumans() }}</small>
                        </td>
                        <td>
                            @if($relance->facture)
                                <a href="{{ route('client.factures.show', $relance->facture) }}" class="text-decoration-none">
                                    {{ $relance->facture->numero_facture }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $relance->facture->date_emission->format('d/m/Y') }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($relance->niveau == 1)
                                <span class="badge bg-info">1ère Relance</span>
                            @elseif($relance->niveau == 2)
                                <span class="badge bg-warning">2ème Relance</span>
                            @elseif($relance->niveau == 3)
                                <span class="badge bg-danger">Mise en Demeure</span>
                            @else
                                <span class="badge bg-secondary">Niveau {{ $relance->niveau }}</span>
                            @endif
                        </td>
                        <td>
                            @if($relance->mode_envoi == 'email')
                                <i class="fas fa-envelope text-primary me-1"></i>Email
                            @elseif($relance->mode_envoi == 'courrier')
                                <i class="fas fa-mail-bulk text-info me-1"></i>Courrier
                            @elseif($relance->mode_envoi == 'sms')
                                <i class="fas fa-sms text-success me-1"></i>SMS
                            @else
                                {{ ucfirst($relance->mode_envoi) }}
                            @endif
                        </td>
                        <td class="text-end">
                            <strong class="text-danger">
                                {{ number_format($relance->montant_du ?? 0, 2) }} €
                            </strong>
                        </td>
                        <td>
                            @if($relance->statut == 'envoye')
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Envoyé
                                </span>
                            @elseif($relance->statut == 'en_attente')
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock me-1"></i>En attente
                                </span>
                            @elseif($relance->statut == 'regle')
                                <span class="badge bg-primary">
                                    <i class="fas fa-check-double me-1"></i>Réglé
                                </span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($relance->statut) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($relance->document_path)
                                <a href="#" class="btn btn-sm btn-outline-secondary" title="Télécharger">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-bell fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-1">Aucune relance</p>
                            <small class="text-muted">Vos paiements sont à jour !</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($relances->hasPages())
        <div class="mt-4">
            {{ $relances->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Informations -->
<div class="alert alert-info mt-4">
    <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>À propos des relances</h6>
    <p class="mb-0 small">
        Les relances sont envoyées automatiquement pour les factures impayées après l'échéance.
        Pour éviter les relances, pensez à activer le prélèvement automatique via
        <a href="{{ route('client.sepa') }}" class="alert-link">les mandats SEPA</a>.
    </p>
</div>

@push('styles')
<style>
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>
@endpush
@endsection