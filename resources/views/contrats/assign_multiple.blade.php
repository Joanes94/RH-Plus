@extends('layouts.app')
@section('title', 'Assigner des contrats')
@section('page-title', 'Assignation de contrats en masse')

@section('content')

<div class="page-header-bar">
    <a href="{{ route('personnel.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>
</div>

{{-- Messages flash --}}
@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1rem;">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-error" style="margin-bottom:1rem;">❌ {{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error" style="margin-bottom:1rem;">
        <strong>Erreurs :</strong>
        <ul style="margin:6px 0 0 18px; padding:0">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if(session('assign_errors'))
    <div class="alert alert-error" style="margin-bottom:1rem;">
        <strong>Agents ignorés :</strong>
        <ul style="margin:6px 0 0 18px; padding:0">
            @foreach(session('assign_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('contrats.assign-multiple') }}" id="assignForm">
    @csrf
    <div style="display:grid; grid-template-columns: 360px 1fr; gap:1.5rem; align-items:start;">

        {{-- PANNEAU GAUCHE : Paramètres du contrat --}}
        <div class="dash-card" style="padding:1.5rem;">
            <div class="card-header" style="margin-bottom:1.25rem;">
                <h3 style="font-size:1rem; font-weight:700; margin:0;">⚙️ Paramètres du contrat</h3>
            </div>

            <div class="form-group" style="margin-bottom:1rem;">
                <label style="font-size:.82rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
                    Type de contrat <span style="color:#ef4444;">*</span>
                </label>
                <div class="input-wrapper select-wrapper">
                    <select name="type_contrat" id="typeContrat" required onchange="toggleDuree(this.value)"
                            style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #d1d5db; font-size:.9rem;">
                        <option value="">— Sélectionner —</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ old('type_contrat') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                @error('type_contrat') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" style="margin-bottom:1rem;">
                <label style="font-size:.82rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
                    Date de début <span style="color:#ef4444;">*</span>
                </label>
                <div class="input-wrapper">
                    <input type="date" name="date_debut" value="{{ old('date_debut', date('Y-m-d')) }}" required
                           style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #d1d5db; font-size:.9rem;">
                </div>
                @error('date_debut') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" id="dureeGroup" style="margin-bottom:1rem; display:none;">
                <label style="font-size:.82rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
                    Durée (mois) <span style="font-size:.72rem; color:#6b7280;">(CDD / Prestataire)</span>
                </label>
                <div class="input-wrapper">
                    <input type="number" name="duree_mois" value="{{ old('duree_mois') }}" min="1" max="60"
                           placeholder="Ex: 12"
                           style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #d1d5db; font-size:.9rem;">
                </div>
                @error('duree_mois') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- Filtre centre (global uniquement) --}}
            @if(auth()->user()->isGlobal())
            <div class="form-group" style="margin-bottom:1rem;">
                <label style="font-size:.82rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
                    Filtrer par centre
                </label>
                <div class="input-wrapper select-wrapper">
                    <select name="centre_id" onchange="this.form.action='{{ route('contrats.assign-multiple.form') }}'; this.form.method='GET'; this.form.submit();"
                            style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #d1d5db; font-size:.9rem;">
                        <option value="">— Tous les centres —</option>
                        @foreach($centres as $c)
                            <option value="{{ $c->id }}" {{ request('centre_id') == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <div style="margin-top:1.5rem; padding-top:1rem; border-top:1px solid #e5e7eb;">
                <div id="selectionCount" style="font-size:.82rem; color:#6b7280; margin-bottom:.75rem; text-align:center;">
                    0 agent(s) sélectionné(s)
                </div>
                <button type="submit" id="submitBtn" disabled
                        style="width:100%; padding:10px; background:#1a5c45; color:#fff; border:none; border-radius:10px; font-size:.9rem; font-weight:700; cursor:pointer; opacity:.5; transition:opacity .2s;"
                        onclick="return confirm('Confirmer l\'assignation du contrat pour les agents sélectionnés ? Les contrats actifs existants seront marqués comme terminés.')">
                    ✅ Attribuer les contrats
                </button>
            </div>
        </div>

        {{-- PANNEAU DROITE : Liste des agents --}}
        <div class="dash-card" style="padding:1.5rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; flex-wrap:wrap; gap:.5rem;">
                <h3 style="font-size:1rem; font-weight:700; margin:0;">👥 Sélection des agents</h3>
                <div style="display:flex; gap:.5rem;">
                    <button type="button" onclick="selectAll(true)"
                            style="padding:5px 12px; font-size:.78rem; font-weight:600; background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; border-radius:7px; cursor:pointer;">
                        Tout sélectionner
                    </button>
                    <button type="button" onclick="selectAll(false)"
                            style="padding:5px 12px; font-size:.78rem; font-weight:600; background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:7px; cursor:pointer;">
                        Tout désélectionner
                    </button>
                </div>
            </div>

            {{-- Recherche rapide --}}
            <div style="margin-bottom:.75rem;">
                <input type="text" id="searchAgent" placeholder="🔍 Rechercher un agent..." oninput="filterAgents(this.value)"
                       style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid #d1d5db; font-size:.85rem; box-sizing:border-box;">
            </div>

            @if($personnels->isEmpty())
                <div style="text-align:center; padding:3rem; color:#9ca3af;">
                    <div style="font-size:2rem; margin-bottom:.5rem;">📋</div>
                    <div style="font-weight:600;">Aucun agent disponible dans ce centre.</div>
                </div>
            @else
                <div style="max-height:480px; overflow-y:auto; border:1px solid #e5e7eb; border-radius:10px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f9fafb; border-bottom:1.5px solid #e5e7eb; position:sticky; top:0;">
                                <th style="padding:.65rem 1rem; text-align:center; width:40px;"><input type="checkbox" id="checkAll" onchange="selectAll(this.checked)"></th>
                                <th style="padding:.65rem 1rem; text-align:left; font-size:.75rem; font-weight:700; color:#4b5563; text-transform:uppercase;">Agent</th>
                                <th style="padding:.65rem 1rem; text-align:left; font-size:.75rem; font-weight:700; color:#4b5563; text-transform:uppercase;">Centre</th>
                                <th style="padding:.65rem 1rem; text-align:left; font-size:.75rem; font-weight:700; color:#4b5563; text-transform:uppercase;">Contrat actuel</th>
                            </tr>
                        </thead>
                        <tbody id="agentTableBody">
                            @foreach($personnels as $p)
                            <tr class="agent-row" data-name="{{ strtolower($p->nom_complet) }}"
                                style="border-bottom:1px solid #f3f4f6; transition:background .12s;">
                                <td style="padding:.6rem 1rem; text-align:center;">
                                    <input type="checkbox" name="personnel_ids[]" value="{{ $p->id }}"
                                           class="agent-check"
                                           onchange="updateCount()"
                                           {{ is_array(old('personnel_ids')) && in_array($p->id, old('personnel_ids')) ? 'checked' : '' }}>
                                </td>
                                <td style="padding:.6rem 1rem;">
                                    <div style="font-weight:700; font-size:.88rem; color:#111827;">{{ $p->nom_complet }}</div>
                                    <div style="font-size:.75rem; color:#6b7280;">{{ $p->corporation ?: ($p->service ?: '—') }}</div>
                                </td>
                                <td style="padding:.6rem 1rem; font-size:.82rem; color:#374151;">
                                    {{ $p->centre?->nom ?? '—' }}
                                </td>
                                <td style="padding:.6rem 1rem;">
                                    @php $contratActif = $p->contrat_actif; @endphp
                                    @if($contratActif)
                                        <span style="display:inline-block; font-size:.72rem; font-weight:700; padding:2px 8px; border-radius:99px;
                                            {{ $contratActif->type_contrat === 'CDI' ? 'background:#d1fae5; color:#065f46;' : ($contratActif->type_contrat === 'CDD' ? 'background:#dbeafe; color:#1e40af;' : 'background:#f3e8ff; color:#6b21a8;') }}">
                                            {{ $contratActif->type_contrat }}
                                        </span>
                                    @else
                                        <span style="font-size:.75rem; color:#9ca3af; font-style:italic;">Aucun</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="font-size:.75rem; color:#6b7280; margin-top:.5rem;">
                    {{ $personnels->count() }} agent(s) au total
                </div>
            @endif
        </div>
    </div>
</form>

<script>
function toggleDuree(type) {
    const group = document.getElementById('dureeGroup');
    group.style.display = (type === 'CDD' || type === 'Prestataire') ? 'block' : 'none';
}

function updateCount() {
    const checked = document.querySelectorAll('.agent-check:checked').length;
    document.getElementById('selectionCount').textContent = checked + ' agent(s) sélectionné(s)';
    const btn = document.getElementById('submitBtn');
    btn.disabled = checked === 0;
    btn.style.opacity = checked === 0 ? '.5' : '1';
    btn.style.cursor = checked === 0 ? 'not-allowed' : 'pointer';
    document.getElementById('checkAll').indeterminate = (checked > 0 && checked < document.querySelectorAll('.agent-check').length);
    document.getElementById('checkAll').checked = (checked === document.querySelectorAll('.agent-check').length);
}

function selectAll(checked) {
    document.querySelectorAll('.agent-check:not([style*="display:none"])').forEach(cb => {
        const row = cb.closest('.agent-row');
        if (!row || row.style.display !== 'none') cb.checked = checked;
    });
    updateCount();
}

function filterAgents(q) {
    const lq = q.toLowerCase();
    document.querySelectorAll('.agent-row').forEach(row => {
        const name = row.dataset.name || '';
        row.style.display = name.includes(lq) ? '' : 'none';
    });
}

// Init
toggleDuree(document.getElementById('typeContrat').value);
updateCount();
</script>
@endsection
