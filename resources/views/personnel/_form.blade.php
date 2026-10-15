{{-- SECTION 1 : IDENTITÉ --}}
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
                <input type="text" name="nom" value="{{ old('nom', $personnel->nom ?? '') }}" placeholder="DUPONT" class="{{ $errors->has('nom') ? 'is-invalid' : '' }}">
            </div>
            @error('nom') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group fg-3">
            <label>Prénoms <span class="req">*</span></label>
            <div class="input-wrapper">
                <input type="text" name="prenoms" value="{{ old('prenoms', $personnel->prenoms ?? '') }}" placeholder="Jean-Paul" class="{{ $errors->has('prenoms') ? 'is-invalid' : '' }}">
            </div>
            @error('prenoms') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group fg-3">
            <label>Email <span style="font-size:.72rem;color:var(--col-text-3)">(identifiant unique)</span></label>
            <div class="input-wrapper">
                <input type="email" name="email" value="{{ old('email', $personnel->email ?? '') }}" placeholder="agent@csvh.bj" class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
            </div>
            @error('email') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group fg-2">
            <label>Date de naissance</label>
            <div class="input-wrapper">
                <input type="date" name="date_naissance" value="{{ old('date_naissance', isset($personnel->date_naissance) ? $personnel->date_naissance->format('Y-m-d') : '') }}">
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Lieu de naissance</label>
            <div class="input-wrapper">
                <input type="text" name="lieu_naissance" value="{{ old('lieu_naissance', $personnel->lieu_naissance ?? '') }}" placeholder="Cotonou">
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Nationalité</label>
            <div class="input-wrapper">
                <input type="text" name="nationalite" value="{{ old('nationalite', $personnel->nationalite ?? 'Béninoise') }}" placeholder="Béninoise">
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Résidence habituelle</label>
            <div class="input-wrapper">
                <input type="text" name="residence" value="{{ old('residence', $personnel->residence ?? '') }}" placeholder="Ex: Missèkplé Ste Rita">
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Sexe <span class="req">*</span></label>
            <div class="input-wrapper select-wrapper">
                <select name="sexe" class="{{ $errors->has('sexe') ? 'is-invalid' : '' }}">
                    <option value="">—</option>
                    <option value="M" {{ old('sexe', $personnel->sexe ?? '') === 'M' ? 'selected' : '' }}>Masculin</option>
                    <option value="F" {{ old('sexe', $personnel->sexe ?? '') === 'F' ? 'selected' : '' }}>Féminin</option>
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
                        <option value="{{ $s }}" {{ old('situation_matrimoniale', $personnel->situation_matrimoniale ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Téléphone</label>
            <div class="input-wrapper">
                <input type="tel" name="telephone" value="{{ old('telephone', $personnel->telephone ?? '') }}" placeholder="+229 97 00 00 00">
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Diplôme</label>
            <div class="input-wrapper">
                <input type="text" name="diplome" value="{{ old('diplome', $personnel->diplome ?? '') }}" placeholder="Ex: BEPC, BAC, Licence...">
            </div>
        </div>

        {{-- Photo --}}
        <div class="form-group fg-2">
            <label>Photo</label>
            @if(isset($personnel) && $personnel->photo_url)
            <div style="margin-bottom:8px">
                <img src="{{ $personnel->photo_url }}" alt="Photo"
                     style="width:56px;height:56px;object-fit:cover;border-radius:50%;border:2px solid var(--col-border-lg)">
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
                           {{ old('autorisation_clientele_privee', $personnel->autorisation_clientele_privee ?? false) ? 'checked' : '' }}>
                    <span class="switch-slider"></span>
                </label>
            </label>
        </div>
    </div>
</div>

{{-- SECTION 2 : POSTE --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-green">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <h3>Poste & Affectation</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-3">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                <label style="margin-bottom:0;">Corporation / Titre-Fonction</label>
                @if(auth()->user()->isAssistantRH() || auth()->user()->isDRH() || auth()->user()->isGlobal())
                    <button type="button" onclick="ouvrirModalNouvelleCorporation()"
                            style="background:none; border:none; color:var(--col-primary, #1a5c45); font-size:0.75rem; font-weight:600; cursor:pointer; text-decoration:underline; padding:0;">
                        + Nouvelle
                    </button>
                @endif
            </div>
            <div class="input-wrapper select-wrapper">
                <select name="corporation" id="select-corporation">
                    <option value="">— Sélectionner —</option>
                    @foreach($corporations as $c)
                        <option value="{{ $c }}" {{ old('corporation', $personnel->corporation ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-3">
            <label>Service</label>
            <div class="input-wrapper select-wrapper">
                <select name="service">
                    <option value="">— Sélectionner —</option>
                    @foreach($services as $s)
                        <option value="{{ $s }}" {{ old('service', $personnel->service ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-2">
            <label>Statut</label>
            <div class="input-wrapper select-wrapper">
                <select name="statut">
                    <option value="actif"    {{ old('statut', $personnel->statut ?? 'actif') === 'actif'    ? 'selected' : '' }}>Actif</option>
                    <option value="inactif"  {{ old('statut', $personnel->statut ?? '') === 'inactif'  ? 'selected' : '' }}>Inactif</option>
                    <option value="en_conge" {{ old('statut', $personnel->statut ?? '') === 'en_conge' ? 'selected' : '' }}>En congé</option>
                    <option value="retraite" {{ old('statut', $personnel->statut ?? '') === 'retraite' ? 'selected' : '' }}>Retraité</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 3 : DATES --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-amber">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <h3>Dates</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-2"><label>Date d'embauche dans le Centre</label><div class="input-wrapper"><input type="date" name="date_embauche_centre" value="{{ old('date_embauche_centre', isset($personnel->date_embauche_centre) ? $personnel->date_embauche_centre->format('Y-m-d') : '') }}"></div></div>
        <div class="form-group fg-2"><label>Date d'embauche dans les ISD</label><div class="input-wrapper"><input type="date" name="date_embauche_isd" value="{{ old('date_embauche_isd', isset($personnel->date_embauche_isd) ? $personnel->date_embauche_isd->format('Y-m-d') : '') }}"></div></div>
        <div class="form-group fg-2"><label>N° CNSS</label><div class="input-wrapper"><input type="text" name="numero_cnss" value="{{ old('numero_cnss', $personnel->numero_cnss ?? '') }}" placeholder="CN123456" class="mono-input"></div></div>
        <div class="form-group fg-2">
            <label>Matricule <span style="font-size:.72rem;color:var(--col-text-3)">(N° interne)</span></label>
            <div class="input-wrapper">
                <input type="text" name="matricule" value="{{ old('matricule', $personnel->matricule ?? '') }}" placeholder="Ex: MAT-2024-001" class="mono-input">
            </div>
            @error('matricule') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group fg-2"><label>Date de départ à la retraite</label><div class="input-wrapper"><input type="date" name="date_depart_retraite" value="{{ old('date_depart_retraite', isset($personnel->date_depart_retraite) ? $personnel->date_depart_retraite->format('Y-m-d') : '') }}"></div></div>
    </div>

    <p style="font-size:.78rem;color:var(--col-text-3);margin-top:6px">
        Le type de contrat, la catégorie/échelon, la date de fin et le débauchage se gèrent désormais par contrat, depuis la fiche du personnel une fois enregistré.
    </p>
</div>

{{-- SECTION 4 : CONGÉ --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-purple">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        </div>
        <h3>Congé</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-2"><label>Année de congé</label><div class="input-wrapper"><input type="text" name="conge_annee" value="{{ old('conge_annee', $personnel->conge_annee ?? '') }}" placeholder="{{ date('Y') }}" maxlength="10"></div></div>
        <div class="form-group fg-2"><label>Nombre de jours</label><div class="input-wrapper"><input type="number" name="conge_jours" min="0" max="365" value="{{ old('conge_jours', $personnel->conge_jours ?? '') }}" placeholder="30"></div></div>
    </div>
</div>

{{-- SECTION 5 : AFFECTATION --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-teal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
        </div>
        <h3>Affectation</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-4">
            <label>Centre d'affectation <span class="req">*</span></label>
            <div class="input-wrapper select-wrapper">
                <select name="centre_id" id="centre_id" required>
                    <option value="">— Sélectionner un centre —</option>
                    @foreach($centres as $centre)
                        <option value="{{ $centre->id }}" {{ (string) old('centre_id', $personnel->centre_id ?? '') === (string) $centre->id ? 'selected' : '' }}>{{ $centre->nom }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group fg-2"><label>Date d'affectation</label><div class="input-wrapper"><input type="date" name="date_affectation" value="{{ old('date_affectation', isset($personnel->date_affectation) ? $personnel->date_affectation->format('Y-m-d') : '') }}"></div></div>
    </div>
</div>

{{-- SECTION 6 : CONTACT URGENCE --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-red">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.7 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.61 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9a16 16 0 0 0 6.29 6.29l.13-.14a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </div>
        <h3>Contact en cas d'urgence</h3>
    </div>
    <div class="form-grid">
        <div class="form-group fg-3"><label>Nom et prénoms</label><div class="input-wrapper"><input type="text" name="contact_urgence_nom" value="{{ old('contact_urgence_nom', $personnel->contact_urgence_nom ?? '') }}" placeholder="Ex: Marie DUPONT"></div></div>
        <div class="form-group fg-2"><label>Téléphone</label><div class="input-wrapper"><input type="tel" name="contact_urgence_telephone" value="{{ old('contact_urgence_telephone', $personnel->contact_urgence_telephone ?? '') }}" placeholder="+229 97 00 00 00"></div></div>
    </div>
</div>

{{-- SECTION 7 : AYANTS DROITS --}}
<div class="form-section">
    <div class="form-section-header">
        <div class="section-icon section-purple">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h3>Ayants droits</h3>
    </div>

    {{-- Conjoint(s) --}}
    <p style="font-weight:600;font-size:.85rem;margin-bottom:8px">Conjoint(s)</p>
    <div id="conjoints-list">
        @php $oldConjoints = old('conjoints', isset($personnel) ? $personnel->conjoints->map(fn($c) => ['nom' => $c->nom, 'prenom' => $c->prenom])->toArray() : []); @endphp
        @forelse($oldConjoints as $i => $cj)
        <div class="form-grid ayant-droit-row">
            <div class="form-group fg-3"><label>Nom</label><div class="input-wrapper"><input type="text" name="conjoints[{{ $i }}][nom]" value="{{ $cj['nom'] ?? '' }}" placeholder="Nom du conjoint"></div></div>
            <div class="form-group fg-3"><label>Prénom</label><div class="input-wrapper"><input type="text" name="conjoints[{{ $i }}][prenom]" value="{{ $cj['prenom'] ?? '' }}" placeholder="Prénom du conjoint"></div></div>
            <div class="form-group fg-1 fg-align-center"><button type="button" class="btn-ghost btn-sm" onclick="this.closest('.ayant-droit-row').remove()">Retirer</button></div>
        </div>
        @empty
        @endforelse
    </div>
    <button type="button" class="btn-ghost btn-sm" onclick="ajouterConjoint()" style="margin-bottom:20px">+ Ajouter un conjoint</button>

    {{-- Enfants --}}
    <p style="font-weight:600;font-size:.85rem;margin:16px 0 8px">Enfants <span style="font-weight:400;color:var(--col-text-3)">(ayants droit jusqu'à 21 ans)</span></p>
    <div id="enfants-list">
        @php $oldEnfants = old('enfants', isset($personnel) ? $personnel->enfants->map(fn($e) => ['nom'=>$e->nom,'prenom'=>$e->prenom,'sexe'=>$e->sexe,'date_naissance'=>optional($e->date_naissance)->format('Y-m-d')])->toArray() : []); @endphp
        @forelse($oldEnfants as $i => $en)
        <div class="form-grid ayant-droit-row">
            <div class="form-group fg-2"><label>Nom</label><div class="input-wrapper"><input type="text" name="enfants[{{ $i }}][nom]" value="{{ $en['nom'] ?? '' }}" placeholder="Nom"></div></div>
            <div class="form-group fg-2"><label>Prénom</label><div class="input-wrapper"><input type="text" name="enfants[{{ $i }}][prenom]" value="{{ $en['prenom'] ?? '' }}" placeholder="Prénom"></div></div>
            <div class="form-group fg-1">
                <label>Sexe</label>
                <div class="input-wrapper select-wrapper">
                    <select name="enfants[{{ $i }}][sexe]">
                        <option value="M" {{ ($en['sexe'] ?? '') === 'M' ? 'selected' : '' }}>M</option>
                        <option value="F" {{ ($en['sexe'] ?? '') === 'F' ? 'selected' : '' }}>F</option>
                    </select>
                </div>
            </div>
            <div class="form-group fg-2"><label>Date de naissance</label><div class="input-wrapper"><input type="date" name="enfants[{{ $i }}][date_naissance]" value="{{ $en['date_naissance'] ?? '' }}"></div></div>
            <div class="form-group fg-1 fg-align-center"><button type="button" class="btn-ghost btn-sm" onclick="this.closest('.ayant-droit-row').remove()">Retirer</button></div>
        </div>
        @empty
        @endforelse
    </div>
    <button type="button" class="btn-ghost btn-sm" onclick="ajouterEnfant()">+ Ajouter un enfant</button>
</div>

<script>
    let conjointIndex = document.querySelectorAll('#conjoints-list .ayant-droit-row').length;
    let enfantIndex   = document.querySelectorAll('#enfants-list .ayant-droit-row').length;

    function ajouterConjoint() {
        const div = document.createElement('div');
        div.className = 'form-grid ayant-droit-row';
        div.innerHTML = `
            <div class="form-group fg-3"><label>Nom</label><div class="input-wrapper"><input type="text" name="conjoints[${conjointIndex}][nom]" placeholder="Nom du conjoint"></div></div>
            <div class="form-group fg-3"><label>Prénom</label><div class="input-wrapper"><input type="text" name="conjoints[${conjointIndex}][prenom]" placeholder="Prénom du conjoint"></div></div>
            <div class="form-group fg-1 fg-align-center"><button type="button" class="btn-ghost btn-sm" onclick="this.closest('.ayant-droit-row').remove()">Retirer</button></div>`;
        document.getElementById('conjoints-list').appendChild(div);
        conjointIndex++;
    }

    function ajouterEnfant() {
        const div = document.createElement('div');
        div.className = 'form-grid ayant-droit-row';
        div.innerHTML = `
            <div class="form-group fg-2"><label>Nom</label><div class="input-wrapper"><input type="text" name="enfants[${enfantIndex}][nom]" placeholder="Nom"></div></div>
            <div class="form-group fg-2"><label>Prénom</label><div class="input-wrapper"><input type="text" name="enfants[${enfantIndex}][prenom]" placeholder="Prénom"></div></div>
            <div class="form-group fg-1">
                <label>Sexe</label>
                <div class="input-wrapper select-wrapper">
                    <select name="enfants[${enfantIndex}][sexe]">
                        <option value="M">M</option>
                        <option value="F">F</option>
                    </select>
                </div>
            </div>
            <div class="form-group fg-2"><label>Date de naissance</label><div class="input-wrapper"><input type="date" name="enfants[${enfantIndex}][date_naissance]"></div></div>
            <div class="form-group fg-1 fg-align-center"><button type="button" class="btn-ghost btn-sm" onclick="this.closest('.ayant-droit-row').remove()">Retirer</button></div>`;
        document.getElementById('enfants-list').appendChild(div);
        enfantIndex++;
    }

    function ouvrirModalNouvelleCorporation() {
        const modal = document.getElementById('modalInlineCorp');
        if (!modal) return;
        document.getElementById('inline_corp_nom').value = '';
        document.getElementById('inline_corp_error').style.display = 'none';
        modal.showModal();
        setTimeout(() => document.getElementById('inline_corp_nom').focus(), 100);
    }

    async function soumettreNouvelleCorporation() {
        const input = document.getElementById('inline_corp_nom');
        const errDiv = document.getElementById('inline_corp_error');
        const btn = document.getElementById('btn-save-inline-corp');
        const val = input.value.trim();

        if (!val) {
            errDiv.textContent = 'Veuillez renseigner le nom de la corporation.';
            errDiv.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Enregistrement...';
        errDiv.style.display = 'none';

        try {
            const resp = await fetch('{{ route('corporations.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    nom: val,
                    centre_id: '{{ isset($personnel) && $personnel->centre_id ? $personnel->centre_id : auth()->user()->centre_id }}'
                })
            });

            const data = await resp.json();

            if (!resp.ok) {
                throw new Error(data.error || data.message || 'Erreur lors de la création de la corporation.');
            }

            const corpNom = data.corporation ? data.corporation.nom : val.toUpperCase();
            const select = document.getElementById('select-corporation');

            // Vérifier si déjà présent dans le select
            let found = false;
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === corpNom) {
                    select.selectedIndex = i;
                    found = true;
                    break;
                }
            }

            if (!found) {
                const opt = document.createElement('option');
                opt.value = corpNom;
                opt.textContent = corpNom;
                opt.selected = true;
                select.appendChild(opt);
            }

            document.getElementById('modalInlineCorp').close();
        } catch (err) {
            errDiv.textContent = err.message;
            errDiv.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Ajouter & Sélectionner';
        }
    }
</script>

{{-- Modal Inline pour Création Rapide de Corporation --}}
<dialog id="modalInlineCorp" style="border:none; border-radius:12px; padding:0; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2), 0 10px 10px -5px rgba(0,0,0,0.04); max-width:440px; width:92%;">
    <div style="background:#fff; border-radius:12px; overflow:hidden;">
        <div style="background:#f8fafc; padding:1rem 1.25rem; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="margin:0; font-size:1rem; font-weight:700; color:#1e293b;">Nouvelle Corporation</h3>
                <small style="color:#64748b; font-size:0.75rem;">
                    @if(auth()->user()->centre)
                        Pour votre centre : <strong>{{ auth()->user()->centre->nom }}</strong>
                    @else
                        Standard général
                    @endif
                </small>
            </div>
            <button type="button" onclick="document.getElementById('modalInlineCorp').close()" style="background:none; border:none; font-size:1.25rem; cursor:pointer; color:#94a3b8;">&times;</button>
        </div>
        <div style="padding:1.25rem;">
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.825rem; font-weight:600; color:#334155; margin-bottom:0.35rem;">Nom du métier / fonction *</label>
                <input type="text" id="inline_corp_nom" placeholder="Ex: AIDE-SOIGNANT DE NUIT, CAISSIER ADJOINT..." 
                       style="width:100%; padding:0.6rem 0.75rem; border:1px solid #cbd5e1; border-radius:6px; font-size:0.875rem; text-transform:uppercase;">
                <div id="inline_corp_error" style="color:#ef4444; font-size:0.75rem; margin-top:0.35rem; display:none;"></div>
            </div>
            @if(auth()->user()->centre)
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:0.65rem 0.75rem; font-size:0.75rem; color:#166534; margin-bottom:1rem;">
                    Cette corporation sera rattachée <strong>exclusivement à votre centre</strong> ({{ auth()->user()->centre->nom }}) et n'apparaîtra nulle part ailleurs.
                </div>
            @endif
            <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                <button type="button" onclick="document.getElementById('modalInlineCorp').close()" class="btn-ghost" style="padding:0.5rem 0.85rem; font-size:0.825rem;">Annuler</button>
                <button type="button" id="btn-save-inline-corp" onclick="soumettreNouvelleCorporation()" class="btn-primary" style="padding:0.5rem 1rem; font-size:0.825rem;">Ajouter & Sélectionner</button>
            </div>
        </div>
    </div>
</dialog>

