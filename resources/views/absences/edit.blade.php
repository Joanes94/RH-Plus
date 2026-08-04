@extends('layouts.app')
@section('title', 'Modifier la demande d\'absence')
@section('page-title', 'Modifier la demande d\'absence')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('absences.show', $absence) }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la demande
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
<form method="POST" action="{{ route('absences.update', $absence) }}" id="absenceForm">
    @csrf @method('PUT')

    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
            <h3>Agent : {{ $absence->personnel->nom_complet }}</h3>
        </div>
        <p style="font-size:.83rem;color:var(--col-text-2)">
            {{ $absence->personnel->corporation ?: '—' }} · {{ $absence->personnel->service ?: '—' }}
        </p>
    </div>

    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-red"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg></div>
            <h3>Motif et dates</h3>
        </div>

        <div class="form-grid">
            <div class="form-group fg-6">
                <label>Type d'absence <span class="req">*</span></label>
                <div class="input-wrapper select-wrapper">
                    <select name="type_absence" id="typeAbsenceSelect" required>
                        <option value="">— Sélectionner —</option>
                        <optgroup label="Absences non déductibles">
                            @foreach($types as $key => $meta)
                                @if(!$meta['deductible'])
                                <option value="{{ $key }}"
                                    data-deductible="0"
                                    data-jours="{{ $meta['jours'] }}"
                                    {{ old('type_absence', $absence->type_absence) === $key ? 'selected' : '' }}>
                                    {{ $meta['label'] }} ({{ $meta['jours'] }} jours)
                                </option>
                                @endif
                            @endforeach
                        </optgroup>
                        <optgroup label="Absences déductibles">
                            @foreach($types as $key => $meta)
                                @if($meta['deductible'])
                                <option value="{{ $key }}"
                                    data-deductible="1"
                                    data-jours=""
                                    {{ old('type_absence', $absence->type_absence) === $key ? 'selected' : '' }}>
                                    {{ $meta['label'] }}
                                </option>
                                @endif
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-grid" style="margin-top:14px">
            <div class="form-group fg-3">
                <label>Date de début <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="date" name="date_debut" id="dateDebutAbs"
                           value="{{ old('date_debut', $absence->date_debut->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="form-group fg-3">
                <label>Date de fin <span class="req">*</span></label>
                <div class="input-wrapper">
                    <input type="date" name="date_fin" id="dateFinAbs"
                           value="{{ old('date_fin', $absence->date_fin->format('Y-m-d')) }}" required>
                </div>
            </div>
        </div>

        <div id="dureeInfo" class="calc-result" style="margin-top:10px">
            <div class="cr-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Durée : <strong id="dureeVal">{{ $absence->nb_jours }} jour(s)</strong></span>
            </div>
        </div>

        <div class="form-group" style="margin-top:14px">
            <label>Motif / Précision</label>
            <div class="input-wrapper">
                <input type="text" name="motif" value="{{ old('motif', $absence->motif) }}" placeholder="Précision sur le motif">
            </div>
        </div>
        <div class="form-group" style="margin-top:10px">
            <label>Observations</label>
            <div class="input-wrapper">
                <textarea name="observations" rows="2"
                    style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('observations', $absence->observations) }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-submit-bar">
        <a href="{{ route('absences.show', $absence) }}" class="btn-ghost">Annuler</a>
        <button type="submit" name="action" value="brouillon" class="btn-ghost">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/></svg>
            Sauvegarder
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
            <div class="at-section" style="margin-top:10px">
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
const typeSelect = document.getElementById('typeAbsenceSelect');
const dateDebut  = document.getElementById('dateDebutAbs');
const dateFin    = document.getElementById('dateFinAbs');

typeSelect.addEventListener('change', function() {
    const opt   = this.options[this.selectedIndex];
    const jours = opt?.dataset?.jours;
    if (jours && dateDebut.value) {
        const d = new Date(dateDebut.value);
        d.setDate(d.getDate() + parseInt(jours) - 1);
        dateFin.value = d.toISOString().slice(0,10);
        dateFin.readOnly = true;
    } else {
        dateFin.readOnly = false;
    }
    updateDuree();
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
    if (!dateDebut.value || !dateFin.value) return;
    const d1   = new Date(dateDebut.value), d2 = new Date(dateFin.value);
    const days = Math.floor((d2 - d1) / 86400000) + 1;
    document.getElementById('dureeVal').textContent = days + ' jour(s) calendaire(s)';
    document.getElementById('dureeInfo').style.display = 'flex';
}
</script>
@endpush
