@extends('layouts.client')

@section('title', 'Créer un Mandat SEPA')

@section('content')
<div class="mb-4">
    <a href="{{ route('client.sepa') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
    <h1 class="h3">Créer un Mandat SEPA</h1>
    <p class="text-muted">Mettez en place le prélèvement automatique pour vos factures</p>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-university me-2"></i>Mandat de Prélèvement SEPA</h5>
            </div>
            <div class="card-body">
                <!-- Informations sur le prélèvement -->
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Qu'est-ce qu'un mandat SEPA ?</h6>
                    <p class="mb-0 small">
                        Le mandat SEPA vous permet d'autoriser BOXIBOX à prélever automatiquement vos factures sur votre compte bancaire.
                        Vous bénéficiez ainsi d'un paiement simplifié et ne risquez plus les oublis.
                    </p>
                </div>

                <form action="{{ route('client.sepa.store') }}" method="POST" id="sepaForm">
                    @csrf

                    <!-- Titulaire du compte -->
                    <div class="mb-4">
                        <label for="titulaire" class="form-label fw-semibold">
                            Titulaire du compte *
                        </label>
                        <input type="text"
                               class="form-control @error('titulaire') is-invalid @enderror"
                               id="titulaire"
                               name="titulaire"
                               value="{{ old('titulaire', $client->nom . ' ' . $client->prenom) }}"
                               required>
                        @error('titulaire')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Nom du titulaire du compte bancaire</div>
                    </div>

                    <!-- IBAN -->
                    <div class="mb-4">
                        <label for="iban" class="form-label fw-semibold">
                            IBAN *
                        </label>
                        <input type="text"
                               class="form-control @error('iban') is-invalid @enderror"
                               id="iban"
                               name="iban"
                               value="{{ old('iban') }}"
                               placeholder="FR76 XXXX XXXX XXXX XXXX XXXX XXX"
                               maxlength="27"
                               required>
                        @error('iban')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">27 caractères pour un IBAN français (ex: FR76 1234 5678 9012 3456 7890 123)</div>
                    </div>

                    <!-- BIC -->
                    <div class="mb-4">
                        <label for="bic" class="form-label fw-semibold">
                            BIC (Code SWIFT) *
                        </label>
                        <input type="text"
                               class="form-control @error('bic') is-invalid @enderror"
                               id="bic"
                               name="bic"
                               value="{{ old('bic') }}"
                               placeholder="XXXXXXXX ou XXXXXXXXXXX"
                               minlength="8"
                               maxlength="11"
                               required>
                        @error('bic')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">8 ou 11 caractères (ex: BNPAFRPP ou BNPAFRPPXXX)</div>
                    </div>

                    <hr class="my-4">

                    <!-- Informations créancier -->
                    <div class="bg-light p-3 rounded mb-4">
                        <h6 class="mb-3">Informations du créancier</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <strong>Nom:</strong> BOXIBOX
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Adresse:</strong> [Adresse entreprise]
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>ICS:</strong> [Numéro ICS]
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Type:</strong> Prélèvement récurrent
                            </div>
                        </div>
                    </div>

                    <!-- Consentement -->
                    <div class="mb-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Autorisation de prélèvement</h6>
                                <p class="card-text small">
                                    En signant ce formulaire de mandat, vous autorisez BOXIBOX à envoyer des instructions à votre banque
                                    pour débiter votre compte, et votre banque à débiter votre compte conformément aux instructions de BOXIBOX.
                                </p>
                                <p class="card-text small mb-3">
                                    Vous bénéficiez du droit d'être remboursé par votre banque selon les conditions décrites dans la convention
                                    que vous avez passée avec elle. Une demande de remboursement doit être présentée dans les 8 semaines suivant
                                    la date de débit de votre compte pour un prélèvement autorisé.
                                </p>

                                <div class="form-check">
                                    <input class="form-check-input @error('consentement') is-invalid @enderror"
                                           type="checkbox"
                                           id="consentement"
                                           name="consentement"
                                           value="1"
                                           required>
                                    <label class="form-check-label fw-semibold" for="consentement">
                                        J'ai lu et j'accepte les conditions du mandat de prélèvement SEPA *
                                    </label>
                                    @error('consentement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Signature électronique -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Signature électronique</label>
                        <div class="alert alert-secondary">
                            <i class="fas fa-signature me-2"></i>
                            En cliquant sur "Créer le mandat", vous signez électroniquement ce document.
                            <br>
                            <small class="text-muted">
                                Date de signature: {{ now()->format('d/m/Y à H:i') }}
                            </small>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('client.sepa') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check me-1"></i>Créer le mandat SEPA
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Informations légales -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-shield-alt me-2 text-success"></i>Sécurité et confidentialité</h6>
                <p class="card-text small text-muted mb-0">
                    Vos données bancaires sont cryptées et sécurisées. Elles ne seront utilisées que pour les prélèvements autorisés.
                    Vous pouvez révoquer ce mandat à tout moment en nous contactant.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Formatage automatique de l'IBAN
document.getElementById('iban').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '').toUpperCase();
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
});

// Formatage automatique du BIC
document.getElementById('bic').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

// Validation du formulaire
document.getElementById('sepaForm').addEventListener('submit', function(e) {
    const consentement = document.getElementById('consentement');
    if (!consentement.checked) {
        e.preventDefault();
        alert('Vous devez accepter les conditions du mandat SEPA');
        consentement.focus();
    }
});
</script>
@endpush
@endsection