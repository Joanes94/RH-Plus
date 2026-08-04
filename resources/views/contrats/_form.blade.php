@php
    $typeActuel = old('type_contrat', $contrat->type_contrat ?? 'CDI');
@endphp

<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-blue">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <h3>Nature du contrat</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-2">
            <label>Type de contrat <span class="req">*</span></label>
            <div class="input-wrapper select-wrapper">
                <select name="type_contrat" id="type_contrat" onchange="toggleContratFields()">
                    @foreach($types as $t)
                        <option value="{{ $t }}" {{ $typeActuel === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            @error('type_contrat') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group fg-3">
            <label>Fonction / poste occupé</label>
            <div class="input-wrapper">
                <input type="text" name="fonction" value="{{ old('fonction', $contrat->fonction ?? $personnel->corporation) }}" placeholder="Ex: Infirmier">
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Service</label>
            <div class="input-wrapper">
                <input type="text" name="service" value="{{ old('service', $contrat->service ?? $personnel->service) }}" placeholder="Ex: Médecine">
            </div>
        </div>
        <div class="form-group fg-1">
            <label>Catégorie</label>
            <div class="input-wrapper select-wrapper">
                <select name="categorie" id="categorie_select" onchange="majApercuSalaire()">
                    <option value="">—</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->categorie }}" {{ old('categorie', $contrat->categorie ?? '') === $cat->categorie ? 'selected' : '' }}>{{ $cat->categorie }} — {{ $cat->libelle }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-1">
            <label>Échelon</label>
            <div class="input-wrapper select-wrapper">
                <select name="echelon" id="echelon_select" onchange="majApercuSalaire()">
                    <option value="">—</option>
                    @for($e = 1; $e <= 11; $e++)
                        <option value="{{ $e }}" {{ (string) old('echelon', $contrat->echelon ?? '') === (string) $e ? 'selected' : '' }}>Éch. {{ $e }}</option>
                    @endfor
                </select>
            </div>
            <span id="apercu-salaire-grille" style="font-size:.72rem;color:var(--col-text-3)"></span>
        </div>
        <div class="form-group fg-2">
            <label>Échelon en vigueur depuis le</label>
            <div class="input-wrapper">
                <input type="date" name="date_effet_echelon" value="{{ old('date_effet_echelon', isset($contrat->date_effet_echelon) ? $contrat->date_effet_echelon->format('Y-m-d') : '') }}">
            </div>
            <span style="font-size:.72rem;color:var(--col-text-3)">Sert de départ pour le calcul du prochain avancement (2 ans).</span>
        </div>
        <div class="form-group fg-4">
            <label>Centre / Institution</label>
            <div class="input-wrapper">
                <input type="text" name="centre" value="{{ old('centre', $contrat->centre ?? $personnel->affectation) }}" placeholder="Ex: CSVH Saint Luc">
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Lieu de congé</label>
            <div class="input-wrapper">
                <input type="text" name="lieu_conge" value="{{ old('lieu_conge', $contrat->lieu_conge ?? $personnel->residence) }}" placeholder="Ex: Cotonou">
            </div>
        </div>
        <div class="form-group fg-2" id="champ-salaire-base">
            <label>Salaire de base mensuel (FCFA)</label>
            <div class="input-wrapper">
                <input type="number" step="1" min="0" name="salaire_base" id="salaire_base_input" value="{{ old('salaire_base', $contrat->salaire_base ?? '') }}" placeholder="Ex: 68983">
            </div>
            <span style="font-size:.72rem;color:var(--col-text-3)">Pré-rempli depuis la grille, modifiable si besoin.</span>
        </div>
        <div class="form-group fg-1" id="champ-honoraire-garde" style="display:none">
            <label>Honoraire garde (FCFA)</label>
            <div class="input-wrapper">
                <input type="number" step="1" min="0" name="honoraire_garde" value="{{ old('honoraire_garde', $contrat->honoraire_garde ?? '') }}" placeholder="Ex: 14000">
            </div>
        </div>
        <div class="form-group fg-1" id="champ-honoraire-permanence" style="display:none">
            <label>Honoraire permanence (FCFA)</label>
            <div class="input-wrapper">
                <input type="number" step="1" min="0" name="honoraire_permanence" value="{{ old('honoraire_permanence', $contrat->honoraire_permanence ?? '') }}" placeholder="Ex: 10000">
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Statut</label>
            <div class="input-wrapper select-wrapper">
                <select name="statut">
                    <option value="actif"   {{ old('statut', $contrat->statut ?? 'actif') === 'actif'   ? 'selected' : '' }}>Actif</option>
                    <option value="termine" {{ old('statut', $contrat->statut ?? '') === 'termine' ? 'selected' : '' }}>Terminé</option>
                    <option value="rompu"   {{ old('statut', $contrat->statut ?? '') === 'rompu'   ? 'selected' : '' }}>Rompu</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-amber">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <h3>Dates</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-2">
            <label>Date de début du contrat <span class="req">*</span></label>
            <div class="input-wrapper">
                <input type="date" name="date_debut" value="{{ old('date_debut', isset($contrat->date_debut) ? $contrat->date_debut->format('Y-m-d') : '') }}">
            </div>
            @error('date_debut') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group fg-2" id="champ-duree-mois" style="display:none">
            <label>Durée du contrat (en mois) <span class="req">*</span></label>
            <div class="input-wrapper">
                <input type="number" min="1" max="120" name="duree_mois" value="{{ old('duree_mois', $contrat->duree_mois ?? '') }}" placeholder="Ex: 6">
            </div>
            <span style="font-size:.72rem;color:var(--col-text-3)">La date de fin est calculée automatiquement.</span>
            @error('duree_mois') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group fg-2" id="champ-date-fin">
            <label>Date de fin <span style="font-size:.72rem;color:var(--col-text-3)" id="lbl-date-fin-note"></span></label>
            <div class="input-wrapper">
                <input type="date" name="date_fin" id="date_fin"
                       value="{{ old('date_fin', isset($contrat->date_fin) ? $contrat->date_fin->format('Y-m-d') : '') }}">
            </div>
            @error('date_fin') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group fg-2"><label>Date de débauchage</label><div class="input-wrapper"><input type="date" name="date_debauchage" value="{{ old('date_debauchage', isset($contrat->date_debauchage) ? $contrat->date_debauchage->format('Y-m-d') : '') }}"></div></div>
        <div class="form-group fg-4"><label>Motif de débauchage</label><div class="input-wrapper"><input type="text" name="motif_debauchage" value="{{ old('motif_debauchage', $contrat->motif_debauchage ?? '') }}" placeholder="Ex: Démission, Fin de contrat..."></div></div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-green">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        </div>
        <h3>Signature & enregistrement (pour le document imprimable)</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-2"><label>Lieu de signature</label><div class="input-wrapper"><input type="text" name="lieu_signature" value="{{ old('lieu_signature', $contrat->lieu_signature ?? $personnel->residence) }}" placeholder="Ex: Cotonou"></div></div>
        <div class="form-group fg-2"><label>Date de signature</label><div class="input-wrapper"><input type="date" name="date_signature" value="{{ old('date_signature', isset($contrat->date_signature) ? $contrat->date_signature->format('Y-m-d') : '') }}"></div></div>
        <div class="form-group fg-4"><label>N° d'enregistrement (visa MTFP)</label><div class="input-wrapper"><input type="text" name="numero_visa" value="{{ old('numero_visa', $contrat->numero_visa ?? '') }}" placeholder="Laisser vide si pas encore visé"></div></div>
        <div class="form-group fg-2"><label>Date du visa</label><div class="input-wrapper"><input type="date" name="date_visa" value="{{ old('date_visa', isset($contrat->date_visa) ? $contrat->date_visa->format('Y-m-d') : '') }}"></div></div>
    </div>
</div>

<script>
function toggleContratFields() {
    const type = document.getElementById('type_contrat').value;
    const dureeField  = document.getElementById('champ-duree-mois');
    const finField    = document.getElementById('champ-date-fin');
    const finInput    = document.getElementById('date_fin');
    const finNote     = document.getElementById('lbl-date-fin-note');
    const salaireField    = document.getElementById('champ-salaire-base');
    const honoGardeField  = document.getElementById('champ-honoraire-garde');
    const honoPermField   = document.getElementById('champ-honoraire-permanence');

    if (type === 'CDD') {
        dureeField.style.display = '';
        finInput.readOnly = true;
        finNote.textContent = '(calculée à partir de la durée)';
    } else if (type === 'CDI') {
        dureeField.style.display = 'none';
        finInput.readOnly = true;
        finNote.textContent = '(calculée automatiquement : 60e anniversaire de l\'agent)';
    } else if (type === 'Prestataire') {
        dureeField.style.display = '';
        finInput.readOnly = true;
        finNote.textContent = '(calculée à partir de la durée)';
    } else {
        dureeField.style.display = 'none';
        finInput.readOnly = false;
        finNote.textContent = '';
    }

    const estPrestataire = type === 'Prestataire';
    salaireField.style.display   = estPrestataire ? 'none' : '';
    honoGardeField.style.display = estPrestataire ? '' : 'none';
    honoPermField.style.display  = estPrestataire ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleContratFields);
document.addEventListener('DOMContentLoaded', majApercuSalaire);

const GRILLE_SALARIALE = @json($grille ?? []);

function majApercuSalaire() {
    const cat = document.getElementById('categorie_select')?.value;
    const ech = document.getElementById('echelon_select')?.value;
    const apercu = document.getElementById('apercu-salaire-grille');
    if (!apercu) return;

    if (cat && ech && GRILLE_SALARIALE[cat] && GRILLE_SALARIALE[cat][ech] !== undefined) {
        const data = GRILLE_SALARIALE[cat][ech];
        apercu.textContent = 'Grille : ' + data.salaire.toLocaleString('fr-FR') + ' FCFA (coef. ' + data.coefficient + ')';
        const salaireInput = document.getElementById('salaire_base_input');
        if (salaireInput && !salaireInput.dataset.modifieManuellement) {
            salaireInput.value = data.salaire;
        }
    } else {
        apercu.textContent = '';
    }
}
document.getElementById('salaire_base_input')?.addEventListener('input', function () {
    this.dataset.modifieManuellement = '1';
});
</script>
