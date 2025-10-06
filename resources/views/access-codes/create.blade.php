@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-key me-2"></i>Nouveau Code d'Accès
        </h1>
        <a href="{{ route('access-codes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations du Code d'Accès</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('access-codes.store') }}" method="POST">
                        @csrf

                        <!-- Client -->
                        <div class="mb-3">
                            <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select @error('client_id') is-invalid @enderror"
                                    id="client_id"
                                    name="client_id"
                                    required
                                    onchange="loadClientBoxes()">
                                <option value="">-- Sélectionner un client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->nom }} {{ $client->prenom }}
                                        @if($client->email)
                                            ({{ $client->email }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Box (optionnel) -->
                        <div class="mb-3">
                            <label for="box_id" class="form-label">Box</label>
                            <select class="form-select @error('box_id') is-invalid @enderror" id="box_id" name="box_id">
                                <option value="">-- Tous les accès (optionnel) --</option>
                                @foreach($boxes as $box)
                                    <option value="{{ $box->id }}" {{ old('box_id') == $box->id ? 'selected' : '' }}>
                                        Box {{ $box->numero }}
                                        @if($box->contratActif && $box->contratActif->client)
                                            - {{ $box->contratActif->client->nom }} {{ $box->contratActif->client->prenom }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('box_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Laissez vide pour donner accès à tous les boxes du client</small>
                        </div>

                        <!-- Type de code -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Type de code <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror"
                                    id="type"
                                    name="type"
                                    required
                                    onchange="updateTypeFields()">
                                <option value="">-- Sélectionner un type --</option>
                                <option value="pin" {{ old('type') == 'pin' ? 'selected' : '' }}>Code PIN</option>
                                <option value="qr" {{ old('type') == 'qr' ? 'selected' : '' }}>QR Code</option>
                                <option value="badge" {{ old('type') == 'badge' ? 'selected' : '' }}>Badge RFID</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Code PIN (visible uniquement si type = pin) -->
                        <div class="mb-3" id="pin-field" style="display: none;">
                            <label for="code_pin" class="form-label">Code PIN (6 chiffres)</label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control @error('code_pin') is-invalid @enderror"
                                       id="code_pin"
                                       name="code_pin"
                                       maxlength="6"
                                       pattern="[0-9]{6}"
                                       placeholder="123456"
                                       value="{{ old('code_pin') }}">
                                <button type="button" class="btn btn-outline-secondary" onclick="generatePIN()">
                                    <i class="fas fa-random"></i> Générer
                                </button>
                                @error('code_pin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Laissez vide pour générer automatiquement</small>
                        </div>

                        <!-- Badge ID (visible uniquement si type = badge) -->
                        <div class="mb-3" id="badge-field" style="display: none;">
                            <label for="badge_uid" class="form-label">ID Badge RFID</label>
                            <input type="text"
                                   class="form-control @error('badge_uid') is-invalid @enderror"
                                   id="badge_uid"
                                   name="badge_uid"
                                   placeholder="Ex: A1B2C3D4"
                                   value="{{ old('badge_uid') }}">
                            @error('badge_uid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Accès temporaire -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="temporaire"
                                       name="temporaire"
                                       value="1"
                                       {{ old('temporaire') ? 'checked' : '' }}
                                       onchange="toggleDateFields()">
                                <label class="form-check-label" for="temporaire">
                                    Accès temporaire
                                </label>
                            </div>
                        </div>

                        <!-- Dates (visibles uniquement si temporaire) -->
                        <div id="date-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_debut" class="form-label">Date de début</label>
                                    <input type="datetime-local"
                                           class="form-control @error('date_debut') is-invalid @enderror"
                                           id="date_debut"
                                           name="date_debut"
                                           value="{{ old('date_debut') }}">
                                    @error('date_debut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_fin" class="form-label">Date de fin</label>
                                    <input type="datetime-local"
                                           class="form-control @error('date_fin') is-invalid @enderror"
                                           id="date_fin"
                                           name="date_fin"
                                           value="{{ old('date_fin') }}">
                                    @error('date_fin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes"
                                      name="notes"
                                      rows="3"
                                      placeholder="Informations complémentaires...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Boutons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer le code d'accès
                            </button>
                            <a href="{{ route('access-codes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar d'aide -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Aide
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Types de codes d'accès</h6>
                    <ul class="small">
                        <li><strong>Code PIN :</strong> Code à 6 chiffres pour clavier numérique</li>
                        <li><strong>QR Code :</strong> Généré automatiquement pour scan mobile</li>
                        <li><strong>Badge RFID :</strong> Carte ou badge à puce</li>
                    </ul>

                    <h6 class="mt-3">Accès temporaire</h6>
                    <p class="small">
                        Activez cette option pour créer un accès avec une durée limitée (visiteur, maintenance, etc.)
                    </p>

                    <h6 class="mt-3">Box spécifique ou global</h6>
                    <p class="small">
                        Si vous ne sélectionnez pas de box, le code donnera accès à tous les boxes du client.
                    </p>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3 bg-warning">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-shield-alt"></i> Sécurité
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <i class="fas fa-check text-success"></i> Tous les codes sont uniques
                    </p>
                    <p class="small mb-2">
                        <i class="fas fa-check text-success"></i> Les PINs sont chiffrés en base
                    </p>
                    <p class="small mb-0">
                        <i class="fas fa-check text-success"></i> Historique des accès tracé
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Afficher/masquer les champs selon le type
function updateTypeFields() {
    const type = document.getElementById('type').value;
    const pinField = document.getElementById('pin-field');
    const badgeField = document.getElementById('badge-field');

    pinField.style.display = type === 'pin' ? 'block' : 'none';
    badgeField.style.display = type === 'badge' ? 'block' : 'none';
}

// Afficher/masquer les dates
function toggleDateFields() {
    const temporaire = document.getElementById('temporaire').checked;
    const dateFields = document.getElementById('date-fields');
    dateFields.style.display = temporaire ? 'block' : 'none';
}

// Générer un PIN aléatoire
function generatePIN() {
    const pin = Math.floor(100000 + Math.random() * 900000).toString();
    document.getElementById('code_pin').value = pin;
}

// Charger les boxes du client sélectionné
function loadClientBoxes() {
    const clientId = document.getElementById('client_id').value;
    // TODO: Charger via AJAX les boxes du client si nécessaire
}

// Initialiser l'affichage au chargement
document.addEventListener('DOMContentLoaded', function() {
    updateTypeFields();
    toggleDateFields();
});
</script>
@endsection
