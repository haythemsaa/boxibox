@extends('layouts.app')

@section('title', 'Mes Mandats SEPA')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3">Mes Mandats SEPA</h1>
        <p class="text-muted">Gérez vos moyens de paiement par prélèvement</p>
    </div>

    <div class="card">
        <div class="card-body">
            @if($mandats->count() > 0)
            <div class="row">
                @foreach($mandats as $mandat)
                <div class="col-md-6 mb-4">
                    <div class="card border">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-university me-2"></i>Mandat SEPA
                            </h6>
                            @if($mandat->statut == 'actif')
                                <span class="badge bg-success">Actif</span>
                            @elseif($mandat->statut == 'suspendu')
                                <span class="badge bg-warning">Suspendu</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($mandat->statut) }}</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="40%">RUM:</th>
                                    <td><code>{{ $mandat->rum }}</code></td>
                                </tr>
                                <tr>
                                    <th>Titulaire:</th>
                                    <td>{{ $mandat->titulaire }}</td>
                                </tr>
                                <tr>
                                    <th>IBAN:</th>
                                    <td>
                                        <code>{{ substr($mandat->iban, 0, 4) }} **** **** **** {{ substr($mandat->iban, -4) }}</code>
                                    </td>
                                </tr>
                                <tr>
                                    <th>BIC:</th>
                                    <td><code>{{ $mandat->bic }}</code></td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>
                                        @if($mandat->type_paiement == 'recurrent')
                                            <span class="badge bg-primary">Récurrent</span>
                                        @else
                                            <span class="badge bg-info">Ponctuel</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Date Signature:</th>
                                    <td>{{ $mandat->date_signature ? $mandat->date_signature->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                                @if($mandat->date_activation)
                                <tr>
                                    <th>Date Activation:</th>
                                    <td>{{ $mandat->date_activation->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                            </table>
                            <div class="card-footer bg-transparent">
                                <a href="{{ route('client.sepa.pdf', $mandat) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i>Télécharger PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-university fa-4x text-muted mb-4"></i>
                <h5 class="text-muted">Aucun mandat SEPA</h5>
                <p class="text-muted mb-4">Vous n'avez pas encore configuré de prélèvement automatique</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Pour mettre en place un prélèvement automatique, contactez notre service commercial.
                </div>
            </div>
            @endif
        </div>
    </div>

    @if($mandats->count() > 0)
    <div class="alert alert-info mt-4">
        <h6><i class="fas fa-info-circle me-2"></i>Information</h6>
        <p class="mb-0">
            Les prélèvements SEPA permettent le paiement automatique de vos factures.
            Pour toute modification ou résiliation de mandat, veuillez contacter notre service commercial.
        </p>
    </div>
    @endif
</div>

<style>
.table th {
    color: #6c757d;
    font-weight: 600;
}
</style>
@endsection