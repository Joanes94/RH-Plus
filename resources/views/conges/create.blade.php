@extends('layouts.app')
@section('title', 'Nouvelle demande de congé')
@section('page-title', 'Nouvelle demande de congé')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('conges.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour
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
<form method="POST" action="{{ route('conges.store') }}" id="congeForm">
    @csrf

    {{-- AGENT --}}
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-blue">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <h3>Agent concerné</h3>
        </div>
        <div class="form-group">
            <label>Agent <span class="req">*</span></label>
            <div class="input-wrapper select-wrapper">
                <select name="personnel_id" id="personnelSelect" class="{{ $errors->has('personnel_id') ? 'is-invalid' : '' }}" required>
                    <option value="">— Sélectionner un agent —</option>
                    @foreach($personnels as $p)
                        <option value="{{ $p->id }}" data-sexe="{{ $p->sexe }}" {{ old('personnel_id') == $p->id ? 'selected' : '' }}>
                            {{ strtoupper($p->nom) }} {{ $p->prenoms }} — {{ $p->service ?: '—' }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('personnel_id') <span class="field-error">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- TYPE DE CONGÉ --}}
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-green">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <h3>Type de congé</h3>
        </div>
        <div class="tpg-items">
            @foreach($typesConge as $slug => $label)
            <label class="type-picker-item {{ old('type_conge', 'administratif') === $slug ? 'selected' : '' }}" id="type-item-{{ $slug }}">
                <input type="radio" name="type_conge" value="{{ $slug }}"
                       class="type-radio-hidden"
                       {{ old('type_conge', 'administratif') === $slug ? 'checked' : '' }}>
                <span class="tpi-label">{{ $label }}</span>
            </label>
            @endforeach
        </div>
        @error('type_conge') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    {{-- DATES --}}
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-amber">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <h3>Période</h3>
        </div>
        <div class="form-grid">
            <div class="form-group fg-2">
                <label>Année du congé <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="annee" value="{{ old('annee', date('Y')) }}"
                           placeholder="{{ date('Y') }}" maxlength="10">
                </div>
                @error('annee') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group fg-2">
                <label>Date de début <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="date" name="date_debut" id="dateDebut"
                           value="{{ old('date_debut') }}" required>
                </div>
                @error('date_debut') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- Champ jours (administratif / technique) --}}
            <div class="form-group fg-2" id="champsJours">
                <label>Nombre de jours ouvrables <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="number" name="nb_jours_demandes" id="nbJours"
                           value="{{ old('nb_jours_demandes') }}" min="1" max="365">
                </div>
                @error('nb_jours_demandes') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- Date reprise calculée (administratif / technique) --}}
            <div class="form-group fg-2" id="champReprise">
                <label>Date de reprise (calculée)</label>
                <div class="input-wrapper">
                    <input type="text" id="dateReprise" readonly
                           placeholder="Saisir date début + nb jours"
                           style="background:var(--col-bg);color:var(--col-text-2)">
                </div>
            </div>

            {{-- Aperçu calculé (maternité uniquement) --}}
            <div class="form-group fg-2" id="champDateFin" style="display:none">
                <label>Date de reprise (calculée)</label>
                <div class="input-wrapper">
                    <input type="text" id="dateFinMaternite" readonly
                           placeholder="Saisir la date de début"
                           style="background:var(--col-bg);color:var(--col-text-2)">
                </div>
            </div>
        </div>

        <div id="infoMaternite" class="aside-card aside-info" style="display:none;margin-top:10px">
            <p style="font-size:.83rem">Congé maternité : <strong>98 jours calendaires (14 semaines)</strong>, réservé au personnel féminin. La date de reprise est calculée automatiquement (98 jours + premier jour ouvrable suivant).</p>
        </div>
    </div>

    {{-- OBSERVATIONS --}}
    <div class="form-section">
        <div class="form-group">
            <label>Observations</label>
            <div class="input-wrapper">
                <textarea name="observations" rows="2"
                    style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('observations') }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-submit-bar">
        <a href="{{ route('conges.index') }}" class="btn-ghost">Annuler</a>
        <button type="submit" name="action" value="brouillon" class="btn-ghost">Sauvegarder</button>
        <button type="submit" name="action" value="soumettre" class="btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Soumettre au DRH
        </button>
    </div>
</form>
</div>

<div class="conge-form-aside">
    <div class="aside-card aside-info" id="soldeCard">
        <h4>Solde de congés</h4>
        <p style="font-size:.82rem;color:var(--col-text-2)">Sélectionnez un agent pour voir son solde.</p>
    </div>
</div>
</div>

@endsection

<style>
    .type-picker-item.disabled { opacity: .45; cursor: not-allowed; pointer-events: none; }
</style>

@push('scripts')
<script>
// ── Type de congé → affichage conditionnel ────────────────────────────────
document.querySelectorAll('.type-radio-hidden').forEach(r => {
    r.addEventListener('change', function() {
        document.querySelectorAll('.type-picker-item').forEach(el => el.classList.remove('selected'));
        this.closest('.type-picker-item').classList.add('selected');
        toggleMaternite(this.value === 'maternite');
    });
});

function toggleMaternite(isMaternite) {
    document.getElementById('champsJours').style.display    = isMaternite ? 'none' : '';
    document.getElementById('champReprise').style.display   = isMaternite ? 'none' : '';
    document.getElementById('champDateFin').style.display   = isMaternite ? '' : 'none';
    document.getElementById('infoMaternite').style.display  = isMaternite ? '' : 'none';

    document.querySelector('input[name="nb_jours_demandes"]').required = !isMaternite;

    if (isMaternite) calcMaternite();
}

// Init selon valeur courante
const current = document.querySelector('.type-radio-hidden:checked');
if (current) toggleMaternite(current.value === 'maternite');

// ── Restriction : congé maternité réservé au personnel féminin ────────────
function majRestrictionMaternite() {
    const option   = document.getElementById('personnelSelect').selectedOptions[0];
    const sexe     = option ? option.dataset.sexe : null;
    const itemMat  = document.getElementById('type-item-maternite');
    const radioMat = itemMat ? itemMat.querySelector('input[type="radio"]') : null;
    if (!itemMat || !radioMat) return;

    if (sexe && sexe !== 'F') {
        itemMat.classList.add('disabled');
        radioMat.disabled = true;
        if (radioMat.checked) {
            document.querySelector('.type-radio-hidden[value="administratif"]').click();
        }
    } else {
        itemMat.classList.remove('disabled');
        radioMat.disabled = false;
    }
}
document.getElementById('personnelSelect').addEventListener('change', majRestrictionMaternite);
majRestrictionMaternite();

// ── Calcul automatique date de reprise (administratif / technique) ────────
function calcReprise() {
    const debut  = document.getElementById('dateDebut').value;
    const nbj    = document.getElementById('nbJours').value;
    const output = document.getElementById('dateReprise');

    if (!debut || !nbj || parseInt(nbj) < 1) { output.value = ''; return; }

    fetch(`{{ route('conges.calcul-date-fin') }}?date_debut=${debut}&nb_jours_demandes=${nbj}&type_conge=administratif`)
        .then(r => r.json())
        .then(d => { if (d.date_fin_fr) output.value = d.date_fin_fr; })
        .catch(() => {});
}

// ── Calcul automatique congé maternité (98 jours) ──────────────────────────
function calcMaternite() {
    const debut  = document.getElementById('dateDebut').value;
    const output = document.getElementById('dateFinMaternite');
    if (!debut || !output) return;

    fetch(`{{ route('conges.calcul-date-fin') }}?date_debut=${debut}&type_conge=maternite`)
        .then(r => r.json())
        .then(d => { if (d.date_fin_fr) output.value = d.date_fin_fr; })
        .catch(() => {});
}

document.getElementById('dateDebut').addEventListener('change', function () {
    calcReprise();
    const isMaternite = document.querySelector('.type-radio-hidden:checked')?.value === 'maternite';
    if (isMaternite) calcMaternite();
});
document.getElementById('nbJours').addEventListener('input',  calcReprise);

// ── Solde agent ───────────────────────────────────────────────────────────
document.getElementById('personnelSelect').addEventListener('change', function() {
    const id    = this.value;
    const annee = document.querySelector('input[name="annee"]')?.value || new Date().getFullYear();
    const card  = document.getElementById('soldeCard');

    if (!id) { card.innerHTML = '<h4>Solde de congés</h4><p style="font-size:.82rem;color:var(--col-text-2)">Sélectionnez un agent.</p>'; return; }

    card.innerHTML = '<h4>Solde de congés</h4><p style="font-size:.82rem;color:var(--col-text-2)">Chargement...</p>';

    fetch(`/conges/calcul-date-fin?personnel_id=${id}&annee=${annee}&solde=1`)
        .then(r => r.json())
        .then(d => {
            if (d.solde !== undefined) {
                card.innerHTML = `<h4>Solde de congés</h4>
                    <div style="font-size:1.6rem;font-weight:700;color:var(--col-primary);margin:8px 0">${d.solde}j</div>
                    <div style="font-size:.78rem;color:var(--col-text-2)">Acquis : ${d.acquis}j — Pris : ${d.pris}j</div>`;
            }
        })
        .catch(() => {});
});
</script>
@endpush
