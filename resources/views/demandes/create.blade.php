@extends('layouts.app')
@section('title', 'Nouvelle demande')
@section('page-title', 'Nouvelle demande')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('demandes.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>
</div>

@if($errors->any())
<div class="alert alert-error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
    {{ $errors->count() }} erreur(s) à corriger.
</div>
@endif

<div class="conge-form-layout">
<div class="conge-form-main">
<form method="POST" action="{{ route('demandes.store') }}" id="demandeForm">
    @csrf

    {{-- Sélection du type --}}
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-purple">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <h3>Type de demande</h3>
        </div>

        {{-- Grille de sélection visuelle --}}
        <div class="type-picker-grid">
    @foreach($catalogue as $groupe)
    <div class="type-picker-group">
        <div class="tpg-header">
            <span class="tpg-label">{{ $groupe['label'] }}</span>
        </div>
        <div class="tpg-items">
            {{-- Ici on itère sur les types du groupe, pas sur $catalogue --}}
            @foreach($groupe['types'] as $slug => $meta)
            <label class="type-picker-item {{ old('type_demande', $typePreselect ?? '') === $slug ? 'selected' : '' }}"
                   data-slug="{{ $slug }}"
                   data-catalogue="{{ json_encode($meta['champs'] ?? []) }}">
                <input type="radio" name="type_demande" value="{{ $slug }}"
                       {{ old('type_demande', $typePreselect ?? '') === $slug ? 'checked' : '' }}
                       class="type-radio-hidden">
                <span class="tpi-label">{{ $meta['label'] }}</span>
                
            </label>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
        @error('type_demande') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    {{-- Agent --}}
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <h3>Agent concerné</h3>
        </div>
        <div class="form-group">
            <label>Sélectionner l'agent <span class="req">*</span></label>
            <div class="input-wrapper select-wrapper">
                <select name="personnel_id" id="personnelSelect" required>
                    <option value="">— Choisir —</option>
                    @foreach($personnels as $p)
                       <option value="{{ $p->id }}"
                          data-contrat="{{ $p->type_contrat_actuel }}"
                          data-sexe="{{ $p->sexe }}"
                          data-corp="{{ $p->corporation }}"
                          data-service="{{ $p->service }}"
                          {{ old('personnel_id') == $p->id ? 'selected' : '' }}>
                          {{ $p->nom_complet }}
                          ({{ $p->type_contrat_actuel ?: 'Sans contrat' }} — {{ $p->service ?: $p->corporation ?: '—' }})
                       </option>
                    @endforeach
                </select>
            </div>
            @error('personnel_id') <span class="field-error">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Champs dynamiques --}}
    <div id="champsSpecifiques">

        {{-- Dates (congés, absences, stage, prestation) --}}
        <div class="form-section champ-group" data-champ="dates" style="display:none">
            <div class="form-section-header">
                <div class="section-icon section-amber"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg></div>
                <h3>Période</h3>
            </div>
            <div class="form-grid">
                <div class="form-group fg-3">
                    <label>Date de début</label>
                    <div class="input-wrapper">
                        <input type="date" name="date_debut" id="dateDebutDem" value="{{ old('date_debut') }}">
                    </div>
                </div>
                <div class="form-group fg-3">
                    <label>Date de fin</label>
                    <div class="input-wrapper">
                        <input type="date" name="date_fin" id="dateFinDem" value="{{ old('date_fin') }}">
                    </div>
                </div>
            </div>
            <div id="dureeDisplay" style="display:none;margin-top:8px" class="calc-result">
                <div class="cr-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Durée : <strong id="dureeText">—</strong>
                </div>
            </div>
        </div>

        {{-- Motif --}}
        <div class="form-section champ-group" data-champ="motif" style="display:none">
            <div class="form-section-header">
                <div class="section-icon section-teal"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg></div>
                <h3>Motif / Justification</h3>
            </div>
            <div class="form-group">
                <div class="input-wrapper">
                    <textarea name="motif" rows="3" placeholder="Précisez le motif ou la justification médicale…"
                        style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('motif') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Date accouchement (maternité) --}}
        <div class="form-section champ-group" data-champ="date_accouchement" style="display:none">
            <div class="form-section-header">
                <div class="section-icon section-red"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
                <h3>Congé maternité</h3>
            </div>
            <div class="form-grid">
                <div class="form-group fg-3">
                    <label>Date d'accouchement prévue</label>
                    <div class="input-wrapper">
                        <input type="date" name="date_accouchement_prevu" value="{{ old('date_accouchement_prevu') }}">
                    </div>
                </div>
            </div>
            <p class="field-hint" style="margin-top:6px">Le congé maternité est de 14 semaines (98 jours) en droit béninois.</p>
        </div>

        {{-- Établissement (stage) --}}
        <div class="form-section champ-group" data-champ="etablissement" style="display:none">
            <div class="form-section-header">
                <div class="section-icon section-green"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg></div>
                <h3>Établissement de formation</h3>
            </div>
            <div class="form-grid">
                <div class="form-group fg-4">
                    <label>Nom de l'établissement</label>
                    <div class="input-wrapper">
                        <input type="text" name="etablissement_stage" value="{{ old('etablissement_stage') }}" placeholder="Ex: ISMA, EPAC, FSS…">
                    </div>
                </div>
                <div class="form-group fg-2">
                    <label>Niveau d'étude</label>
                    <div class="input-wrapper">
                        <input type="text" name="niveau_etude" value="{{ old('niveau_etude') }}" placeholder="Ex: Licence 2, Master…">
                    </div>
                </div>
            </div>
        </div>

        {{-- Spécialité (stage) --}}
        <div class="form-section champ-group" data-champ="specialite" style="display:none">
            <div class="form-section-header">
                <div class="section-icon section-blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg></div>
                <h3>Spécialité / Filière</h3>
            </div>
            <div class="form-grid">
                <div class="form-group fg-4">
                    <div class="input-wrapper">
                        <input type="text" name="specialite" value="{{ old('specialite') }}" placeholder="Ex: Infirmerie, Pharmacie, Gestion…">
                    </div>
                </div>
            </div>
        </div>

        {{-- Faits reprochés (demande d'explication) --}}
        <div class="form-section champ-group" data-champ="faits" style="display:none">
            <div class="form-section-header">
                <div class="section-icon section-red"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
                <h3>Faits reprochés</h3>
            </div>
            <div class="form-group">
                <label>Description des faits</label>
                <div class="input-wrapper">
                    <textarea name="faits_reproches" rows="4" placeholder="Décrire précisément les faits reprochés…"
                        style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('faits_reproches') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-section champ-group" data-champ="date_faits" style="display:none">
            <div class="form-grid">
                <div class="form-group fg-3">
                    <label>Date des faits</label>
                    <div class="input-wrapper">
                        <input type="date" name="date_faits" value="{{ old('date_faits') }}">
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Observations (toujours visible) --}}
    <div class="form-section">
        <div class="form-group">
            <label>Observations</label>
            <div class="input-wrapper">
                <textarea name="observations" rows="2" placeholder="Informations complémentaires…"
                    style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('observations') }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-submit-bar">
        <a href="{{ route('demandes.index') }}" class="btn-ghost">Annuler</a>
        <button type="submit" name="action" value="brouillon" class="btn-ghost">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/></svg>
            Brouillon
        </button>
        <button type="submit" name="action" value="soumettre" class="btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Soumettre au DRH
        </button>
    </div>
</form>
</div>

<div class="conge-form-aside">
    <div class="aside-card">
        <h4>Guides par catégorie</h4>
        <div class="absence-type-list">
            @foreach($catalogue as $groupeSlug => $groupe)
            <div class="at-section">
                <div class="at-section-title at-non-ded" style="color:var(--col-text-2)">{{ $groupe['label'] }}</div>
                @foreach($groupe['types'] as $slug => $meta)
                <div class="at-item">
                    <span class="at-label">{{ $meta['label'] }}</span>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script>
// Mapping champs → sections à afficher
const champsMap = {
    'dates':            ['dates'],
    'motif':            ['motif'],
    'date_accouchement':['date_accouchement'],
    'etablissement':    ['etablissement'],
    'specialite':       ['specialite'],
    'faits':            ['faits'],
    'date_faits':       ['date_faits'],
};

function afficherChamps(champsArray) {
    document.querySelectorAll('.champ-group').forEach(el => {
        el.style.display = 'none';
    });
    if (!champsArray) return;
    champsArray.forEach(champ => {
        document.querySelectorAll(`.champ-group[data-champ="${champ}"]`).forEach(el => {
            el.style.display = 'block';
        });
    });
}

// Init au chargement
const selectedItem = document.querySelector('.type-picker-item.selected');
if (selectedItem) {
    const champs = JSON.parse(selectedItem.dataset.champs || '[]');
    afficherChamps(champs);
}

// Clic sur un type
document.querySelectorAll('.type-picker-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.type-picker-item').forEach(i => i.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input[type=radio]').checked = true;
        const champs = JSON.parse(this.dataset.champs || '[]');
        afficherChamps(champs);
    });
});

// Calcul durée
const dateDebut = document.getElementById('dateDebutDem');
const dateFin   = document.getElementById('dateFinDem');
function updateDuree() {
    if (!dateDebut.value || !dateFin.value) { document.getElementById('dureeDisplay').style.display = 'none'; return; }
    const d1   = new Date(dateDebut.value), d2 = new Date(dateFin.value);
    const days = Math.floor((d2 - d1) / 86400000) + 1;
    if (days < 1) { document.getElementById('dureeDisplay').style.display = 'none'; return; }
    document.getElementById('dureeText').textContent = days + ' jour(s)';
    document.getElementById('dureeDisplay').style.display = 'flex';
}
if (dateDebut) { dateDebut.addEventListener('change', updateDuree); dateFin.addEventListener('change', updateDuree); }
</script>
@endpush
