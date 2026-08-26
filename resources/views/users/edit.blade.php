@extends('layouts.app')

@section('title', isset($user) ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur')
@section('page-title', isset($user) ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur')

@section('content')
<div class="form-page">
    <div class="form-card">
        <div class="form-card-header">
            <a href="{{ route('users.index') }}" class="btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                Retour
            </a>
            <h2>{{ isset($user) ? 'Modifier : '.$user->nom_complet : 'Créer un compte utilisateur' }}</h2>
        </div>

        <form method="POST" action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}">
            @csrf
            @if(isset($user)) @method('PUT') @endif

            <div class="form-body">
                @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="form-section">
                    <h3 class="form-section-title">Identité</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" name="nom" id="nom" value="{{ old('nom', $user->nom ?? '') }}" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="prenoms">Prénoms *</label>
                            <input type="text" name="prenoms" id="prenoms" value="{{ old('prenoms', $user->prenoms ?? '') }}" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="sexe">Sexe *</label>
                            <select name="sexe" id="sexe" required class="form-control">
                                <option value="">— Choisir —</option>
                                <option value="M" {{ old('sexe', $user->sexe ?? '') == 'M' ? 'selected' : '' }}>Masculin</option>
                                <option value="F" {{ old('sexe', $user->sexe ?? '') == 'F' ? 'selected' : '' }}>Féminin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="text" name="telephone" id="telephone" value="{{ old('telephone', $user->telephone ?? '') }}" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Accès</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="role">Rôle *</label>
                            <select name="role" id="role" required class="form-control" onchange="toggleCentre(this.value)">
                                <option value="">— Choisir —</option>
                                <optgroup label="Rôles globaux (DDIS)">
                                    <option value="crh" {{ old('role', $user->role ?? '') == 'crh' ? 'selected' : '' }}>Conseiller RH (CRH)</option>
                                    <option value="ddis" {{ old('role', $user->role ?? '') == 'ddis' ? 'selected' : '' }}>Directeur DDIS</option>
                                    <option value="ddrh" {{ old('role', $user->role ?? '') == 'ddrh' ? 'selected' : '' }}>Directeur DRH (lecture seule)</option>
                                </optgroup>
                                <optgroup label="Rôles de centre">
                                    <option value="drh_centre" {{ old('role', $user->role ?? '') == 'drh_centre' ? 'selected' : '' }}>DRH de centre</option>
                                    <option value="assistant_rh" {{ old('role', $user->role ?? '') == 'assistant_rh' ? 'selected' : '' }}>Assistant RH</option>
                                    <option value="directeur_centre" {{ old('role', $user->role ?? '') == 'directeur_centre' ? 'selected' : '' }}>Directeur de centre</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="form-group" id="centreGroup">
                            <label for="centre_id">Centre d'affectation *</label>
                            <select name="centre_id" id="centre_id" class="form-control">
                                <option value="">— Choisir un centre —</option>
                                @foreach($centres as $centre)
                                <option value="{{ $centre->id }}" {{ old('centre_id', $user->centre_id ?? '') == $centre->id ? 'selected' : '' }}>
                                    {{ $centre->nom }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title">Mot de passe {{ isset($user) ? '(laisser vide pour ne pas changer)' : '' }}</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="password">Mot de passe {{ isset($user) ? '' : '*' }}</label>
                            <input type="password" name="password" id="password" {{ isset($user) ? '' : 'required' }} class="form-control" minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirmer *</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-footer">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    {{ isset($user) ? 'Mettre à jour' : 'Créer le compte' }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleCentre(role) {
    const centreGroup = document.getElementById('centreGroup');
    const globalRoles = ['crh', 'ddis', 'ddrh'];
    if (globalRoles.includes(role)) {
        centreGroup.style.display = 'none';
        document.getElementById('centre_id').value = '';
    } else {
        centreGroup.style.display = 'block';
    }
}
// Init on load
document.addEventListener('DOMContentLoaded', () => {
    const role = document.getElementById('role').value;
    if (role) toggleCentre(role);
});
</script>
@endpush

@push('styles')
<style>
.form-page { max-width: 760px; margin: 0 auto; }
.form-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.03); }
.form-card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: 1rem; background: #fafafa; }
.form-card-header h2 { margin: 0; font-size: 1.1rem; font-weight: 600; color: #111827; }
.btn-back { display: inline-flex; align-items: center; gap: 0.3rem; color: #4b5563; font-size: 0.85rem; font-weight: 500; text-decoration: none; padding: 0.4rem 0.75rem; border-radius: 8px; background: #fff; border: 1px solid #d1d5db; transition: all 0.15s; }
.btn-back:hover { color: #111827; background: #f3f4f6; }
.form-body { padding: 1.75rem; }
.form-section { margin-bottom: 1.75rem; padding-bottom: 1.25rem; border-bottom: 1px dashed #f3f4f6; }
.form-section:last-child { margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
.form-section-title { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #1a5c45; margin-bottom: 1rem; font-weight: 700; display: flex; align-items: center; gap: 0.4rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group label { font-size: 0.82rem; font-weight: 600; color: #374151; }
.form-control { width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem; box-sizing: border-box; transition: border-color 0.15s; background: #fff; }
.form-control:focus { outline: none; border-color: #1a5c45; box-shadow: 0 0 0 3px rgba(26,92,69,0.08); }
.form-footer { padding: 1.25rem 1.75rem; border-top: 1px solid #f3f4f6; display: flex; justify-content: flex-end; gap: 0.75rem; background: #fafafa; }
.alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 0.85rem 1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.87rem; }
@media (max-width: 640px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-body { padding: 1.25rem; }
    .form-footer { flex-direction: column; }
    .form-footer .btn { width: 100%; justify-content: center; }
}
</style>
@endpush
@endsection

