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
                        <option value="">— Choisir —</option>
                        @foreach($personnels as $p)
                            <option value="{{ $p->id }}" {{ old('personnel_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nom_complet }} — {{ $p->service ?: $p->corporation ?: '—' }}
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

        <div class="form-grid" style="margin-top:14px">
            <div class="form-group fg-3">
                <label>Date de début <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="date" name="date_debut" id="dateDebutAbs"
                           value="{{ old('date_debut') }}" required>
                </div>
                @error('date_debut') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group fg-3" id="dateFinGroup">
                <label>Date de fin <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="date" name="date_fin" id="dateFinAbs"
                           value="{{ old('date_fin') }}" required>
                </div>
                @error('date_fin') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div id="dureeInfo" class="calc-result" style="display:none; margin-top:10px">
            <div class="cr-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Durée : <strong id="dureeVal">—</strong></span>
            </div>
        </div>

        <div class="form-group" style="margin-top:14px">
            <label>Motif / Précision</label>
            <div class="input-wrapper">
                <input type="text" name="motif" value="{{ old('motif') }}"
                       placeholder="Ex: Décès de M. Jean Dupont, père de l'employé">
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
                <div class="at-section-title at-non-ded">Non déductibles</div>
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
const typeSelect  = document.getElementById('typeAbsenceSelect');
const dateDebut   = document.getElementById('dateDebutAbs');
const dateFin     = document.getElementById('dateFinAbs');
const typeInfo    = document.getElementById('typeInfo');
const dureeInfo   = document.getElementById('dureeInfo');

typeSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) { typeInfo.style.display = 'none'; return; }

    const jours     = opt.dataset.jours;
    const deductible= opt.dataset.deductible === '1';

    if (jours) {
        typeInfo.innerHTML = `<div class="ti-badge ti-green">Durée réglementaire : <strong>${jours} jours calendaires</strong></div>`;
        // Auto-remplir date fin
        if (dateDebut.value) {
            const d = new Date(dateDebut.value);
            d.setDate(d.getDate() + parseInt(jours) - 1);
            dateFin.value = d.toISOString().slice(0,10);
            dateFin.readOnly = true;
            updateDuree();
        }
    } else {
        typeInfo.innerHTML = `<div class="ti-badge ti-amber">Absence déductible : les jours seront déduits du solde de congés de l'agent.</div>`;
        dateFin.readOnly = false;
    }
    typeInfo.style.display = 'block';
});

dateDebut.addEventListener('change', function() {
    const opt   = typeSelect.options[typeSelect.selectedIndex];
    const jours = opt?.dataset?.jours;
    if (jours) {
        const d = new Date(this.value);
        d.setDate(d.getDate() + parseInt(jours) - 1);
        dateFin.value = d.toISOString().slice(0,10);
    }
    updateDuree();
});

dateFin.addEventListener('change', updateDuree);

function updateDuree() {
    if (!dateDebut.value || !dateFin.value) { dureeInfo.style.display = 'none'; return; }
    const d1 = new Date(dateDebut.value), d2 = new Date(dateFin.value);
    if (d2 < d1) { dureeInfo.style.display = 'none'; return; }
    const days = Math.floor((d2 - d1) / (86400000)) + 1;
    document.getElementById('dureeVal').textContent = days + ' jour(s) calendaire(s)';
    dureeInfo.style.display = 'flex';
}
</script>
@endpush
