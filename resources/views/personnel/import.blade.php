@extends('layouts.app')
@section('title', 'Import Excel')
@section('page-title', 'Importer du personnel')

@section('content')

<div class="page-header-bar">
    <a href="{{ route('personnel.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>
</div>

@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

<div style="max-width: 700px;">

    {{-- Instructions --}}
    <div class="dash-card import-card">
        <div class="import-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
        <h2>Import depuis Excel / CSV</h2>
        <p>Importez plusieurs agents en une seule opération à partir d'un fichier <strong>.xlsx</strong>, <strong>.xls</strong> ou <strong>.csv</strong> (séparateur point-virgule).</p>

        <div class="import-rules">
            <div class="rule-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>La première ligne doit contenir les <strong>entêtes</strong> (voir modèle)</span>
            </div>
            <div class="rule-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Champs obligatoires : <code>nom</code>, <code>prenoms</code>, <code>sexe</code> (M ou F)</span>
            </div>
            <div class="rule-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Les dates doivent être au format <code>AAAA-MM-JJ</code></span>
            </div>
            <div class="rule-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Pour CSV : séparateur <code>;</code> (point-virgule)</span>
            </div>
        </div>

        <a href="{{ route('personnel.template') }}" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Télécharger le modèle CSV
        </a>
    </div>

    {{-- Formulaire upload --}}
    <div class="dash-card">
        <div class="card-header"><h3>Sélectionner le fichier</h3></div>

        <form method="POST" action="{{ route('personnel.import') }}" enctype="multipart/form-data">
            @csrf

            <div class="file-drop-zone" id="dropZone">
                <input type="file" name="fichier" id="fichierInput" accept=".xlsx,.xls,.csv" class="file-input-hidden">
                <div class="drop-zone-content" id="dropContent">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="color:var(--col-text-3)"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="12" x2="12" y2="18"/><polyline points="9 15 12 12 15 15"/></svg>
                    <p>Glissez votre fichier ici ou <span class="drop-link">parcourir</span></p>
                    <span class="drop-hint">.xlsx, .xls, .csv — max 5 Mo</span>
                </div>
                <div class="drop-zone-selected" id="dropSelected" style="display:none">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span id="fileName">fichier.xlsx</span>
                    <button type="button" class="drop-remove" id="dropRemove">✕</button>
                </div>
            </div>
            @error('fichier') <span class="field-error" style="margin-top:6px;display:block">{{ $message }}</span> @enderror

            <div class="form-actions" style="margin-top: 20px;">
                <a href="{{ route('personnel.index') }}" class="btn-ghost">Annuler</a>
                <button type="submit" class="btn-primary" id="importBtn" disabled>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Lancer l'import
                </button>
            </div>
        </form>
    </div>

    {{-- Aperçu des colonnes attendues --}}
    <div class="dash-card">
        <div class="card-header"><h3>Colonnes reconnues</h3></div>
        <div class="col-grid">
            @php
            $cols = [
                'nom' => 'Nom',
                'prenoms' => 'Prénoms',
                'date_naissance' => 'Date de naissance',
                'lieu_naissance' => 'Lieu de naissance',
                'sexe' => 'Sexe (M/F)',
                'telephone' => 'Téléphone',
                'situation_matrimoniale' => 'Situation matrimoniale',
                'diplome' => 'Diplôme',
                'autorisation_clientele_privee' => 'Autorisation clientèle privée',
                'corporation' => 'Corporation',
                'service' => 'Service',
                'type_contrat' => 'Type de contrat',
                'categorie_echelon' => 'Catégorie/Échelon',
                'date_embauche_centre' => 'Date embauche Centre',
                'date_embauche_isd' => 'Date embauche ISD',
                'numero_cnss' => 'N° CNSS',
                'date_fin_contrat' => 'Date fin contrat',
                'contact_urgence_nom' => 'Contact urgence — Nom',
                'contact_urgence_telephone' => 'Contact urgence — Tél',
            ];
            @endphp
            @foreach($cols as $key => $label)
            <div class="col-item">
                <code>{{ $key }}</code>
                <span>{{ $label }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const dropZone   = document.getElementById('dropZone');
const fileInput  = document.getElementById('fichierInput');
const dropContent= document.getElementById('dropContent');
const dropSelected=document.getElementById('dropSelected');
const fileName   = document.getElementById('fileName');
const dropRemove = document.getElementById('dropRemove');
const importBtn  = document.getElementById('importBtn');

dropContent.addEventListener('click', () => fileInput.click());
dropContent.querySelector('.drop-link').addEventListener('click', e => { e.stopPropagation(); fileInput.click(); });

fileInput.addEventListener('change', () => selectFile(fileInput.files[0]));

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    if (e.dataTransfer.files[0]) selectFile(e.dataTransfer.files[0]);
});

dropRemove.addEventListener('click', () => {
    fileInput.value = '';
    dropContent.style.display = '';
    dropSelected.style.display = 'none';
    importBtn.disabled = true;
});

function selectFile(file) {
    if (!file) return;
    fileName.textContent = file.name;
    dropContent.style.display = 'none';
    dropSelected.style.display = 'flex';
    importBtn.disabled = false;
}
</script>
@endpush
