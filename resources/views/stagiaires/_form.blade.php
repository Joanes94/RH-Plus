{{-- Partial _form.blade.php stagiaires --}}

{{-- ═══ IDENTITÉ ══════════════════════════════════════════════════════════════ --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-blue">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h3>Identité</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-2">
            <label>Nom <span class="req">*</span></label>
            <div class="input-wrapper">
                <input type="text" name="nom" value="{{ old('nom', $stagiaire->nom ?? '') }}"
                       placeholder="DUPONT" class="{{ $errors->has('nom') ? 'is-invalid' : '' }}">
            </div>
            @error('nom') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group fg-4">
            <label>Prénoms <span class="req">*</span></label>
            <div class="input-wrapper">
                <input type="text" name="prenoms" value="{{ old('prenoms', $stagiaire->prenoms ?? '') }}"
                       placeholder="Jean-Paul" class="{{ $errors->has('prenoms') ? 'is-invalid' : '' }}">
            </div>
            @error('prenoms') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group fg-3">
            <label>Email</label>
            <div class="input-wrapper">
                <input type="email" name="email" value="{{ old('email', $stagiaire->email ?? '') }}"
                       placeholder="stagiaire@email.com"
                       class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
            </div>
            @error('email') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group fg-3">
            <label>Téléphone</label>
            <div class="input-wrapper">
                <input type="tel" name="telephone" value="{{ old('telephone', $stagiaire->telephone ?? '') }}"
                       placeholder="+229 97 00 00 00">
            </div>
        </div>

        <div class="form-group fg-2">
            <label>Date de naissance</label>
            <div class="input-wrapper">
                <input type="date" name="date_naissance"
                       value="{{ old('date_naissance', isset($stagiaire->date_naissance) ? $stagiaire->date_naissance->format('Y-m-d') : '') }}">
            </div>
        </div>
        <div class="form-group fg-4">
            <label>Lieu de naissance</label>
            <div class="input-wrapper">
                <input type="text" name="lieu_naissance"
                       value="{{ old('lieu_naissance', $stagiaire->lieu_naissance ?? '') }}"
                       placeholder="Cotonou">
            </div>
        </div>

        <div class="form-group fg-2">
            <label>Sexe <span class="req">*</span></label>
            <div class="input-wrapper select-wrapper">
                <select name="sexe" class="{{ $errors->has('sexe') ? 'is-invalid' : '' }}">
                    <option value="">—</option>
                    <option value="M" {{ old('sexe', $stagiaire->sexe ?? '') === 'M' ? 'selected' : '' }}>Masculin</option>
                    <option value="F" {{ old('sexe', $stagiaire->sexe ?? '') === 'F' ? 'selected' : '' }}>Féminin</option>
                </select>
            </div>
            @error('sexe') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group fg-2">
            <label>Situation matrimoniale</label>
            <div class="input-wrapper select-wrapper">
                <select name="situation_matrimoniale">
                    <option value="">—</option>
                    @foreach($situations as $s)
                        <option value="{{ $s }}" {{ old('situation_matrimoniale', $stagiaire->situation_matrimoniale ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Photo --}}
        <div class="form-group fg-2">
            <label>Photo</label>
            @if(isset($stagiaire) && $stagiaire->photo_url)
                <div style="margin-bottom:8px">
                    <img src="{{ $stagiaire->photo_url }}" alt="Photo actuelle"
                         style="width:60px;height:60px;object-fit:cover;border-radius:50%;border:2px solid var(--col-border-lg)">
                </div>
            @endif
            <div class="input-wrapper">
                <input type="file" name="photo" accept="image/jpeg,image/png" class="file-input-std">
            </div>
            @error('photo') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group fg-4 fg-align-center">
            <label class="checkbox-switch-label">
                <span>Autorisation d'exercice en clientèle privée</span>
                <label class="switch">
                    <input type="hidden" name="autorisation_clientele_privee" value="0">
                    <input type="checkbox" name="autorisation_clientele_privee" value="1"
                           {{ old('autorisation_clientele_privee', $stagiaire->autorisation_clientele_privee ?? false) ? 'checked' : '' }}>
                    <span class="switch-slider"></span>
                </label>
            </label>
        </div>
    </div>
</div>

{{-- ═══ FORMATION ══════════════════════════════════════════════════════════════ --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-green">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <h3>Formation</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-3">
            <label>Titre / Fonction</label>
            <div class="input-wrapper">
                <input type="text" name="titre"
                       value="{{ old('titre', $stagiaire->titre ?? '') }}"
                       placeholder="Ex: Infirmier stagiaire">
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Niveau d'étude</label>
            <div class="input-wrapper select-wrapper">
                <select name="niveau_etude">
                    <option value="">— Sélectionner —</option>
                    @foreach($niveaux as $n)
                        <option value="{{ $n }}" {{ old('niveau_etude', $stagiaire->niveau_etude ?? '') === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Diplôme</label>
            <div class="input-wrapper">
                <input type="text" name="diplome"
                       value="{{ old('diplome', $stagiaire->diplome ?? '') }}"
                       placeholder="Ex: BTS Infirmier, Licence Sciences de la santé…">
            </div>
        </div>
        <div class="form-group fg-3">
            <label>École / Établissement de formation</label>
            <div class="input-wrapper">
                <input type="text" name="ecole_formation"
                       value="{{ old('ecole_formation', $stagiaire->ecole_formation ?? '') }}"
                       placeholder="Ex: ISMA, EPAC, FSS Bénin…">
            </div>
        </div>
    </div>
</div>

{{-- ═══ STAGE ═══════════════════════════════════════════════════════════════════ --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-amber">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <h3>Période de stage</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-3">
            <label>Service d'accueil</label>
            <div class="input-wrapper select-wrapper">
                <select name="service">
                    <option value="">— Sélectionner —</option>
                    @foreach($services as $s)
                        <option value="{{ $s }}" {{ old('service', $stagiaire->service ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Date de début</label>
            <div class="input-wrapper">
                <input type="date" name="date_debut_stage"
                       value="{{ old('date_debut_stage', isset($stagiaire->date_debut_stage) ? $stagiaire->date_debut_stage->format('Y-m-d') : '') }}">
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Date de fin</label>
            <div class="input-wrapper">
                <input type="date" name="date_fin_stage"
                       value="{{ old('date_fin_stage', isset($stagiaire->date_fin_stage) ? $stagiaire->date_fin_stage->format('Y-m-d') : '') }}">
            </div>
        </div>
        <div class="form-group fg-2">
    <label>Type de stage</label>
    <div class="input-wrapper select-wrapper">
        <select name="type_stage" class="{{ $errors->has('type_stage') ? 'is-invalid' : '' }}">
            <option value="">— Sélectionner —</option>
            @foreach(\App\Models\Stagiaire::typesStage() as $t)
                <option value="{{ $t }}" {{ old('type_stage', $stagiaire->type_stage ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    @error('type_stage') <span class="field-error">{{ $message }}</span> @enderror
</div>
        <div class="form-group fg-2">
            <label>Statut</label>
            <div class="input-wrapper select-wrapper">
                <select name="statut">
                    <option value="en_cours"  {{ old('statut', $stagiaire->statut ?? 'en_cours') === 'en_cours'  ? 'selected' : '' }}>En cours</option>
                    <option value="termine"   {{ old('statut', $stagiaire->statut ?? '') === 'termine'   ? 'selected' : '' }}>Terminé</option>
                    <option value="abandonne" {{ old('statut', $stagiaire->statut ?? '') === 'abandonne' ? 'selected' : '' }}>Abandonné</option>
                </select>
            </div>
        </div>
        <div class="form-group fg-6">
            <label>Observations</label>
            <div class="input-wrapper">
                <textarea name="observations" rows="2"
                    style="width:100%;padding:10px 14px;background:var(--col-bg);border:1.5px solid var(--col-border-lg);border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:.875rem;resize:vertical;outline:none;">{{ old('observations', $stagiaire->observations ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- ═══ CONTACT URGENCE ═══════════════════════════════════════════════════════ --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-red">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.7 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.61 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9a16 16 0 0 0 6.29 6.29l.13-.14a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </div>
        <h3>Personne à contacter en cas d'urgence</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-3">
            <label>Nom et prénoms</label>
            <div class="input-wrapper">
                <input type="text" name="contact_urgence_nom"
                       value="{{ old('contact_urgence_nom', $stagiaire->contact_urgence_nom ?? '') }}"
                       placeholder="Ex: Marie DUPONT">
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Téléphone</label>
            <div class="input-wrapper">
                <input type="tel" name="contact_urgence_telephone"
                       value="{{ old('contact_urgence_telephone', $stagiaire->contact_urgence_telephone ?? '') }}"
                       placeholder="+229 97 00 00 00">
            </div>
        </div>
    </div>
</div>
