@extends('layouts.app')
@section('title', 'Documents — ' . $stagiaire->nom_complet)
@section('page-title', 'Générer un document')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('stagiaires.show', $stagiaire) }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la fiche
    </a>
    <span class="breadcrumb-name">{{ $stagiaire->nom_complet }}</span>
</div>

<div class="conge-form-layout">
<div class="conge-form-main">

<form method="GET" id="docForm" action="">
    @csrf

    {{-- TYPE DE DOCUMENT --}}
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-blue">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <h3>Type de document</h3>
        </div>
        <div class="tpg-items">
            <label class="type-picker-item {{ old('doc_type') === 'autorisation' ? 'selected' : '' }}">
                <input type="radio" name="doc_type" value="autorisation" class="type-radio-hidden" required
                       {{ old('doc_type') === 'autorisation' ? 'checked' : '' }}>
                <span class="tpi-label">Autorisation de stage</span>
                <span class="tpi-genre">Délivrée avant le stage</span>
            </label>
            <label class="type-picker-item {{ old('doc_type') === 'attestation' ? 'selected' : '' }}">
                <input type="radio" name="doc_type" value="attestation" class="type-radio-hidden"
                       {{ old('doc_type') === 'attestation' ? 'checked' : '' }}>
                <span class="tpi-label">Attestation de stage</span>
                <span class="tpi-genre">Délivrée après le stage</span>
            </label>
            {{-- NOUVEAU : Évaluation --}}
            <label class="type-picker-item {{ old('doc_type') === 'evaluation' ? 'selected' : '' }}">
                <input type="radio" name="doc_type" value="evaluation" class="type-radio-hidden"
                       {{ old('doc_type') === 'evaluation' ? 'checked' : '' }}>
                <span class="tpi-label">Fiche d'évaluation</span>
                <span class="tpi-genre">Évaluation du stagiaire</span>
            </label>
        </div>
    </div>

    {{-- TYPE DE STAGE --}}
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-green">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <h3>Type de stage</h3>
        </div>
        <div class="tpg-items">
            <label class="type-picker-item">
                <input type="radio" name="type" value="professionnel" class="type-radio-hidden" checked>
                <span class="tpi-label">Professionnel</span>
            </label>
            <label class="type-picker-item">
                <input type="radio" name="type" value="academique" class="type-radio-hidden">
                <span class="tpi-label">Académique</span>
            </label>
            <label class="type-picker-item">
                <input type="radio" name="type" value="decouverte" class="type-radio-hidden">
                <span class="tpi-label">Académique de découverte</span>
            </label>
        </div>
    </div>

    {{-- SERVICES --}}
    <div class="form-section">
        <div class="form-section-header">
            <div class="section-icon section-amber">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
            <h3>Service(s) concerné(s)</h3>
        </div>
        <p style="font-size:.82rem;color:var(--col-text-2);margin-bottom:12px">
            Cochez un ou plusieurs services. Si plusieurs, les périodes seront réparties automatiquement sur la durée du stage.
        </p>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
            @foreach($services as $svc)
            <label style="display:flex;align-items:center;gap:8px;font-size:.83rem;cursor:pointer;padding:6px 10px;border:1px solid var(--col-border);border-radius:6px">
                <input type="checkbox" name="services[]" value="{{ $svc }}"
                       {{ $stagiaire->service === $svc ? 'checked' : '' }}>
                {{ $svc }}
            </label>
            @endforeach
        </div>
    </div>

    {{-- RÉFÉRENCE --}}
    <div class="form-section">
        <div class="form-group fg-4">
            <label>Référence N/REF <span style="font-size:.72rem;color:var(--col-text-3)">(optionnel)</span></label>
            <div class="input-wrapper">
                <input type="text" name="reference"
                       placeholder="{{ now()->format('m').'-'.now()->format('y') }}/AC/DDIS/CSVHHSL/DIR/DRH/ARH">
            </div>
        </div>
    </div>

    <div class="form-submit-bar">
        <button type="button" onclick="genererDoc()" class="btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            @if(auth()->user()->isDRH())
                Générer le document
            @else
                Soumettre au DRH
            @endif
        </button>
    </div>
</form>

</div>
<div class="conge-form-aside">
    <div class="aside-card aside-info">
        <h4>Stagiaire</h4>
        <p><strong>{{ $stagiaire->nom_complet }}</strong></p>
        @if($stagiaire->ecole_formation)<p style="font-size:.8rem;color:var(--col-text-2)">{{ $stagiaire->ecole_formation }}</p>@endif
        @if($stagiaire->niveau_etude)<p style="font-size:.8rem;color:var(--col-text-2)">{{ $stagiaire->niveau_etude }}</p>@endif
        @if($stagiaire->date_debut_stage && $stagiaire->date_fin_stage)
        <p style="font-size:.8rem;margin-top:6px">
            {{ $stagiaire->date_debut_stage->format('d/m/Y') }} → {{ $stagiaire->date_fin_stage->format('d/m/Y') }}
        </p>
        @endif
    </div>
</div>
</div>

@endsection

@push('scripts')
<script>
function genererDoc() {
    const docType = document.querySelector('input[name="doc_type"]:checked');
    const stageType = document.querySelector('input[name="type"]:checked');
    const services = Array.from(document.querySelectorAll('input[name="services[]"]:checked')).map(c => c.value);
    const ref = document.querySelector('input[name="reference"]').value;

    if (!docType) { alert('Veuillez choisir un type de document.'); return; }
    if (services.length === 0) { alert('Veuillez sélectionner au moins un service.'); return; }

    // Si c'est une évaluation, rediriger vers le formulaire d'évaluation
    if (docType.value === 'evaluation') {
        const params = new URLSearchParams();
        params.set('stagiaire_id', '{{ $stagiaire->id }}');
        window.location.href = '{{ route("evaluations.create") }}?' + params.toString();
        return;
    }

    // Pour autorisation et attestation : soumission POST
    const form = document.getElementById('docForm');
    form.action = docType.value === 'autorisation'
        ? '{{ route("stagiaires.documents.autorisation", $stagiaire) }}'
        : '{{ route("stagiaires.documents.attestation", $stagiaire) }}';
    form.method = 'POST';
    form.submit();
}

// Highlight radio pickers
document.querySelectorAll('.type-radio-hidden').forEach(radio => {
    radio.addEventListener('change', function() {
        const group = this.closest('.tpg-items') || this.closest('.type-picker-grid');
        if (group) group.querySelectorAll('.type-picker-item').forEach(el => el.classList.remove('selected'));
        this.closest('.type-picker-item')?.classList.add('selected');
    });
});
</script>
@endpush