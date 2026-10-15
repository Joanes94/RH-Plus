@extends('layouts.app')
@section('title', 'Nouvelle autorisation d\'absence')
@section('page-title', 'Nouvelle autorisation d\'absence')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('absences.index') }}" class="btn-back">
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
<form method="POST" action="{{ route('absences.store') }}" id="absenceForm">
    @csrf

    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <h3>Agent concerné</h3>
        </div>
        <div class="form-grid">
            <div class="form-group fg-6">
                <label>Sélectionner l'agent <span class="req">*</span></label>
                <div class="input-wrapper select-wrapper">
                    <select name="personnel_id" required>
                        <option value="">— Choisir un agent (ordre alphabétique) —</option>
                        @foreach($personnels as $p)
                            <option value="{{ $p->id }}" {{ old('personnel_id') == $p->id ? 'selected' : '' }}>
                                {{ strtoupper($p->nom) }} {{ $p->prenoms }} — {{ $p->service ?: $p->corporation ?: '—' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('personnel_id') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-red"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <h3>Motif et dates</h3>
        </div>

        <div class="form-grid">
            <div class="form-group fg-6">
                <label>Type d'absence <span class="req">*</span></label>
                <div class="input-wrapper select-wrapper">
                    <select name="type_absence" id="typeAbsenceSelect" required>
                        <option value="">— Sélectionner le motif —</option>
                        <optgroup label="Absences non déductibles des congés">
                            @foreach($types as $key => $meta)
                                @if(!$meta['deductible'])
                                <option value="{{ $key }}"
                                    data-deductible="0"
                                    data-jours="{{ $meta['jours'] }}"
                                    {{ old('type_absence') === $key ? 'selected' : '' }}>
                                    {{ $meta['label'] }} ({{ $meta['jours'] }} jours)
                                </option>
                                @endif
                            @endforeach
                        </optgroup>
                        <optgroup label="Absences déductibles des congés">
                            @foreach($types as $key => $meta)
                                @if($meta['deductible'])
                                <option value="{{ $key }}"
                                    data-deductible="1"
                                    data-jours=""
                                    {{ old('type_absence') === $key ? 'selected' : '' }}>
                                    {{ $meta['label'] }}
                                </option>
                                @endif
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                @error('type_absence') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Info badge type --}}
        <div id="typeInfo" class="type-info-box" style="display:none"></div>

        {{-- Choix du mode de dates --}}
        <div class="form-group fg-6" style="margin-top:14px" id="modeSelectionContainer">
            <label style="font-weight:600;margin-bottom:6px;display:block;">Mode de sélection des dates</label>
            <div style="display:flex;gap:14px;flex-wrap:wrap;">
                <label class="mode-tab-label" style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--col-surface);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);cursor:pointer;font-weight:600;font-size:.875rem;">
                    <input type="radio" name="mode_selection" value="plage" id="modePlage" {{ old('mode_selection', 'plage') === 'plage' ? 'checked' : '' }}>
                    <span>📅 Période continue (Du ... Au ...)</span>
                </label>
                <label class="mode-tab-label" style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--col-surface);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);cursor:pointer;font-weight:600;font-size:.875rem;">
                    <input type="radio" name="mode_selection" value="specifique" id="modeSpecifique" {{ old('mode_selection') === 'specifique' ? 'checked' : '' }}>
                    <span>📌 Jours spécifiques (Dates précises)</span>
                </label>
            </div>
        </div>

        {{-- SECTION 1 : MODE PLAGE CONTINUE --}}
        <div id="sectionPlage" style="{{ old('mode_selection', 'plage') === 'specifique' ? 'display:none;' : '' }}">
            <div class="form-grid" style="margin-top:14px">
                <div class="form-group fg-2">
                    <label>Date de début <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <input type="date" name="date_debut" id="dateDebutAbs"
                               value="{{ old('date_debut') }}">
                    </div>
                    @error('date_debut') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group fg-2" id="dateFinGroup">
                    <label>Date de fin <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <input type="date" name="date_fin" id="dateFinAbs"
                               value="{{ old('date_fin') }}">
                    </div>
                    @error('date_fin') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group fg-2" id="dateRepriseGroup">
                    <label>Date de reprise (calculée)</label>
                    <div class="input-wrapper">
                        <input type="text" id="dateRepriseAbs" readonly
                               placeholder="Saisir les dates"
                               style="background:var(--col-bg);color:var(--col-text-2)">
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2 : MODE JOURS SPÉCIFIQUES --}}
        <div id="sectionSpecifique" style="{{ old('mode_selection') === 'specifique' ? '' : 'display:none;' }}; margin-top:14px;">
            <div style="background:var(--col-surface);border:1.5px dashed var(--col-border-lg);border-radius:var(--radius);padding:14px;">
                <label style="font-weight:600;display:block;margin-bottom:6px;">Sélectionner et ajouter des dates spécifiques :</label>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <input type="date" id="pickerDateSpecifique" style="padding:8px 12px;border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:inherit;font-size:.875rem;">
                    <button type="button" id="btnAddDateSpecifique" class="btn-primary" style="padding:8px 16px;font-size:.85rem;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Ajouter cette date
                    </button>
                    <span style="font-size:.8rem;color:var(--col-text-2);">(Cliquez pour ajouter chaque jour d'absence demandé)</span>
                </div>

                {{-- Liste visuelle des dates sélectionnées --}}
                <div id="containerDatesBadges" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;min-height:36px;">
                    <span id="noDatesMsg" style="font-size:.82rem;color:var(--col-text-3);font-style:italic;">Aucune date spécifique ajoutée pour l'instant.</span>
                </div>
                <div id="hiddenInputsDates"></div>
                @error('jours_specifiques') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div id="dureeInfo" class="calc-result" style="display:none; margin-top:10px">
            <div class="cr-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Durée totale : <strong id="dureeVal">—</strong></span>
            </div>
            <div class="cr-item" id="repriseInfoSpecifique" style="display:none;margin-left:14px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Reprise prévue : <strong id="repriseVal">—</strong></span>
            </div>
        </div>

        <div class="form-group" style="margin-top:14px">
            <label>Motif / Précision</label>
            <div class="input-wrapper">
                <input type="text" name="motif" value="{{ old('motif') }}"
                       placeholder="Ex: Demande de repos, convenance personnelle, obligation familiale...">
            </div>
        </div>
        <div class="form-group" style="margin-top:10px">
            <label>Observations</label>
            <div class="input-wrapper">
                <textarea name="observations" rows="2" placeholder="Informations complémentaires…"
                    style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('observations') }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-submit-bar">
        <a href="{{ route('absences.index') }}" class="btn-ghost">Annuler</a>
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
        <h4>Types d'absence</h4>
        <div class="absence-type-list">
            <div class="at-section">
                <div class="at-section-title at-non-ded">Non déductibles (Événements)</div>
                @foreach($types as $key => $meta)
                    @if(!$meta['deductible'])
                    <div class="at-item">
                        <span class="at-label">{{ $meta['label'] }}</span>
                        <span class="at-jours">{{ $meta['jours'] }}j</span>
                    </div>
                    @endif
                @endforeach
            </div>
            <div class="at-section">
                <div class="at-section-title at-ded">Déductibles</div>
                <div class="at-item">
                    <span class="at-label">Toute autre absence</span>
                    <span class="at-jours at-ded-tag">Déduit des congés</span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection

@push('scripts')
<script>
const typeSelect      = document.getElementById('typeAbsenceSelect');
const dateDebut       = document.getElementById('dateDebutAbs');
const dateFin         = document.getElementById('dateFinAbs');
const dateReprise     = document.getElementById('dateRepriseAbs');
const typeInfo        = document.getElementById('typeInfo');
const dureeInfo       = document.getElementById('dureeInfo');
const modePlageRadio  = document.getElementById('modePlage');
const modeSpecRadio   = document.getElementById('modeSpecifique');
const sectionPlage    = document.getElementById('sectionPlage');
const sectionSpec     = document.getElementById('sectionSpecifique');
const pickerSpec      = document.getElementById('pickerDateSpecifique');
const btnAddSpec      = document.getElementById('btnAddDateSpecifique');
const badgesContainer = document.getElementById('containerDatesBadges');
const hiddenInputsDiv = document.getElementById('hiddenInputsDates');
const noDatesMsg      = document.getElementById('noDatesMsg');
const repriseSpecInfo = document.getElementById('repriseInfoSpecifique');
const repriseVal      = document.getElementById('repriseVal');

let datesSpecifiques = [];

// Gestion du basculement de mode
function toggleMode() {
    const isSpec = modeSpecRadio.checked;
    if (isSpec) {
        sectionPlage.style.display = 'none';
        sectionSpec.style.display  = 'block';
        dateDebut.required = false;
        dateFin.required   = false;
        renderDatesSpecifiques();
    } else {
        sectionPlage.style.display = 'block';
        sectionSpec.style.display  = 'none';
        dateDebut.required = true;
        dateFin.required   = true;
        updateDetailsPlage();
    }
}
modePlageRadio.addEventListener('change', toggleMode);
modeSpecRadio.addEventListener('change', toggleMode);

// Ajout de date spécifique
btnAddSpec.addEventListener('click', function(e) {
    e.preventDefault();
    const val = pickerSpec.value;
    if (!val) return;
    if (!datesSpecifiques.includes(val)) {
        datesSpecifiques.push(val);
        datesSpecifiques.sort();
        renderDatesSpecifiques();
        pickerSpec.value = '';
    }
});

// Supprimer date spécifique
function removeDateSpecifique(dateStr) {
    datesSpecifiques = datesSpecifiques.filter(d => d !== dateStr);
    renderDatesSpecifiques();
}

function renderDatesSpecifiques() {
    badgesContainer.innerHTML = '';
    hiddenInputsDiv.innerHTML = '';

    if (datesSpecifiques.length === 0) {
        badgesContainer.appendChild(noDatesMsg);
        dureeInfo.style.display = 'none';
        repriseSpecInfo.style.display = 'none';
        return;
    }

    datesSpecifiques.forEach(dateStr => {
        // Badge
        const parts = dateStr.split('-');
        const frDate = parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : dateStr;
        const badge = document.createElement('div');
        badge.style = 'display:inline-flex;align-items:center;gap:6px;background:var(--col-primary-lt,#ecfdf5);color:var(--col-primary,#1a5c45);padding:4px 10px;border-radius:20px;font-size:.83rem;font-weight:600;border:1px solid var(--col-primary-md,#a7f3d0);';
        badge.innerHTML = `<span>📅 ${frDate}</span> <button type="button" style="background:none;border:none;cursor:pointer;color:inherit;font-weight:700;padding:0 2px;">✕</button>`;
        badge.querySelector('button').addEventListener('click', () => removeDateSpecifique(dateStr));
        badgesContainer.appendChild(badge);

        // Hidden input
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'jours_specifiques[]';
        input.value = dateStr;
        hiddenInputsDiv.appendChild(input);
    });

    const nb = datesSpecifiques.length;
    document.getElementById('dureeVal').textContent = nb + (nb > 1 ? ' jours' : ' jour');
    dureeInfo.style.display = 'flex';

    // Calcul date de reprise (jour suivant la dernière date)
    const maxDate = datesSpecifiques[datesSpecifiques.length - 1];
    const d = new Date(maxDate);
    d.setDate(d.getDate() + 1);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    repriseVal.textContent = `${day}/${month}/${year}`;
    repriseSpecInfo.style.display = 'flex';
}

typeSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) { typeInfo.style.display = 'none'; return; }

    const jours     = opt.dataset.jours;
    const deductible= opt.dataset.deductible === '1';

    if (jours) {
        typeInfo.innerHTML = `<div class="ti-badge ti-green">Durée événement : <strong>${jours} jours</strong> (week-ends et fériés inclus)</div>`;
        dateFin.readOnly = true;
    } else {
        typeInfo.innerHTML = `<div class="ti-badge ti-amber">Absence déductible : les jours seront déduits du solde de congés de l'agent.</div>`;
        dateFin.readOnly = false;
    }
    typeInfo.style.display = 'block';

    if (modeSpecRadio.checked) {
        renderDatesSpecifiques();
    } else {
        updateDetailsPlage();
    }
});

dateDebut.addEventListener('change', updateDetailsPlage);
dateFin.addEventListener('change', updateDetailsPlage);

function updateDetailsPlage() {
    const type = typeSelect.value;
    const debut = dateDebut.value;
    const fin = dateFin.value;

    if (!type || !debut) {
        dureeInfo.style.display = 'none';
        dateReprise.value = '';
        return;
    }

    let url = `{{ route('absences.calcul-details') }}?mode_selection=plage&type_absence=${type}&date_debut=${debut}`;
    if (fin) {
        url += `&date_fin=${fin}`;
    }

    fetch(url)
        .then(r => r.json())
        .then(d => {
            if (d.error) {
                dureeInfo.style.display = 'none';
                dateReprise.value = '';
                return;
            }
            if (d.date_fin && dateFin.readOnly) {
                dateFin.value = d.date_fin;
            }
            if (d.nb_jours > 0) {
                document.getElementById('dureeVal').textContent = d.nb_jours + (d.nb_jours > 1 ? ' jours' : ' jour');
                dureeInfo.style.display = 'flex';
                repriseSpecInfo.style.display = 'none';
            } else {
                dureeInfo.style.display = 'none';
            }
            if (d.date_reprise_fr) {
                dateReprise.value = d.date_reprise_fr;
            } else {
                dateReprise.value = '';
            }
        })
        .catch(() => {});
}
</script>
@endpush
