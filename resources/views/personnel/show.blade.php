@extends('layouts.app')
@section('title', $personnel->nom_complet)
@section('page-title', 'Fiche personnel')

@section('content')

<div class="page-header-bar">
    <a href="{{ route('personnel.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>
    <div class="header-actions">
        @if(!auth()->user()->isReadOnly())
        <a href="{{ route('personnel.edit', $personnel) }}" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Modifier
        </a>
        <button class="btn-primary btn-sm" onclick="document.getElementById('modal-affecter').style.display='flex'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
            Affecter
        </button>
        @endif
        <a href="{{ route('personnel.historique', $personnel) }}" class="btn btn-outline" title="Historique">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
            Historique
        </a>
    </div>
</div>

@if(auth()->user()->isCRH())
<div class="crh-actions" style="display: flex; gap: 0.5rem; margin-top: 0.75rem; flex-wrap: wrap;">
    @if(!$personnel->est_ancien_travailleur)
    <a href="{{ route('personnel.transferer', $personnel) }}" class="btn btn-outline btn-sm" title="Transférer">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/></svg>
        Transférer
    </a>
    <form method="POST" action="{{ route('personnel.passer-cdi', $personnel) }}" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Confirmer le passage en CDI ?')" title="Passage en CDI">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Passage CDI
        </button>
    </form>
    <a href="{{ route('personnel.desactiver', $personnel) }}" class="btn btn-outline btn-sm" style="border-color: #fca5a5; color: #ef4444;" title="Désactiver">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        Désactiver
    </a>
    @else
    <form method="POST" action="{{ route('personnel.reactiver', $personnel) }}" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Confirmer la réactivation ?')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            Réactiver
        </button>
    </form>
    @endif
</div>
@endif

{{-- ── En-tête fiche ──────────────────────────────────────────────────────── --}}
<div class="fiche-hero" style="display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap;">
    <div class="fb-profile-photo-container" onclick="openPhotoModal()">
        @if($personnel->photo_url)
            <img src="{{ $personnel->photo_url }}" alt="{{ $personnel->nom_complet }}" class="fb-profile-photo" id="profileImg">
        @else
            <img src="{{ $personnel->avatar_url }}" alt="{{ $personnel->initiales }}" class="fb-profile-photo" id="profileImg">
        @endif
        <div class="fb-photo-badge" title="Cliquer pour agrandir la photo">🔍</div>
    </div>

    <div class="fiche-hero-info">
        <h2>{{ $personnel->nom_complet }}</h2>
        <div class="fiche-tags">
            <span class="statut-pill statut-{{ $personnel->statut }}">{{ $personnel->statut_label }}</span>
            @if($personnel->corporation)
                <span class="fiche-tag tag-blue">{{ $personnel->corporation }}</span>
            @endif
            @if($personnel->service)
                <span class="fiche-tag tag-green">{{ $personnel->service }}</span>
            @endif
            @if($personnel->type_contrat_actuel)
                <span class="contrat-tag contrat-{{ strtolower($personnel->type_contrat_actuel) }}">{{ $personnel->type_contrat_actuel }}</span>
            @endif
        </div>
    </div>
    <div class="fiche-hero-meta">
        @if($personnel->centre)
        <div class="info-item">
            <span class="info-label">Centre</span>
            <span class="info-value">{{ $personnel->centre->nom }}</span>
        </div>
        @endif
        <div class="fiche-meta-item">
            <span class="meta-label">Enregistré le</span>
            <span class="meta-val">{{ $personnel->created_at->format('d/m/Y') }}</span>
        </div>
        @if($personnel->createdBy)
        <div class="fiche-meta-item">
            <span class="meta-label">Par</span>
            <span class="meta-val">{{ $personnel->createdBy->prenoms }}</span>
        </div>
        @endif
    </div>
</div>

@if($personnel->est_ancien_travailleur && !auth()->user()->isReadOnly())
<div class="dash-card" style="border-left:4px solid var(--col-text-3);margin-top:8px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
    <div style="font-size:.85rem;color:var(--col-text-2)">
        📦 <strong>Ancien travailleur</strong> — {{ $personnel->motif_depart_label }}
        @if($personnel->date_depart) depuis le {{ $personnel->date_depart->format('d/m/Y') }} @endif
    </div>
    <form method="POST" action="{{ route('personnel.restaurer', $personnel) }}" onsubmit="return confirm('Restaurer {{ $personnel->nom_complet }} comme personnel actif ?')">
        @csrf
        <button type="submit" class="btn-primary btn-sm">Restaurer cet agent</button>
    </form>
</div>
@endif

{{-- ── Corps fiche (2 colonnes) ───────────────────────────────────────────── --}}
<div class="fiche-grid">

    {{-- Colonne gauche --}}
    <div class="fiche-col">

        <div class="dash-card">
            <div class="card-header"><h3>Identité</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Date de naissance</dt><dd>{{ $personnel->date_naissance ? $personnel->date_naissance->format('d/m/Y') : '—' }}</dd></div>
                <div class="dl-row"><dt>Lieu de naissance</dt><dd>{{ $personnel->lieu_naissance ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Sexe</dt><dd>{{ $personnel->sexe === 'M' ? 'Masculin' : 'Féminin' }}</dd></div>
                <div class="dl-row"><dt>Situation matrimoniale</dt><dd>{{ $personnel->situation_matrimoniale ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Téléphone</dt><dd>{{ $personnel->telephone ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Diplôme</dt><dd>{{ $personnel->diplome ?: '—' }}</dd></div>
                <div class="dl-row">
                    <dt>Clientèle privée</dt>
                    <dd>
                        @if($personnel->autorisation_clientele_privee)
                            <span class="badge badge-green">Autorisé</span>
                        @else
                            <span class="badge badge-warn">Non autorisé</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="dash-card">
            <div class="card-header"><h3>Contact d'urgence</h3></div>
            @if($personnel->contact_urgence_nom || $personnel->contact_urgence_telephone)
            <dl class="detail-list">
                <div class="dl-row"><dt>Nom</dt><dd>{{ $personnel->contact_urgence_nom ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Téléphone</dt><dd>{{ $personnel->contact_urgence_telephone ?: '—' }}</dd></div>
            </dl>
            @else
                <p class="empty-inline">Aucun contact renseigné.</p>
            @endif
        </div>

    </div>

    {{-- Colonne droite --}}
    <div class="fiche-col">

        <div class="dash-card">
            <div class="card-header"><h3>Carrière</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>N° CNSS</dt><dd class="mono-text">{{ $personnel->numero_cnss ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Matricule</dt><dd class="mono-text">{{ $personnel->matricule ?: '—' }}</dd></div>

                <div class="dl-row"><dt>Embauche dans le Centre</dt><dd>{{ $personnel->date_embauche_centre ? $personnel->date_embauche_centre->format('d/m/Y') : '—' }}</dd></div>
                <div class="dl-row"><dt>Embauche dans les ISD</dt><dd>{{ $personnel->date_embauche_isd ? $personnel->date_embauche_isd->format('d/m/Y') : '—' }}</dd></div>
                <div class="dl-row"><dt>Départ à la retraite</dt><dd>{{ $personnel->date_depart_retraite ? $personnel->date_depart_retraite->format('d/m/Y') : '—' }}</dd></div>
                <div class="dl-row"><dt>Nationalité</dt><dd>{{ $personnel->nationalite ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Résidence</dt><dd>{{ $personnel->residence ?: '—' }}</dd></div>
            </dl>
        </div>

        <div class="dash-card">
            <div class="card-header"><h3>Ayants droits</h3></div>
            <p style="font-weight:600;font-size:.8rem;margin-bottom:6px">Conjoint(s)</p>
            @forelse($personnel->conjoints as $conjoint)
                <div class="dl-row"><dt>{{ $loop->iteration }}.</dt><dd>{{ $conjoint->prenom }} {{ strtoupper($conjoint->nom) }}</dd></div>
            @empty
                <p class="empty-inline">Aucun conjoint renseigné.</p>
            @endforelse

            <p style="font-weight:600;font-size:.8rem;margin:14px 0 6px">Enfants</p>
            @forelse($personnel->enfants as $enfant)
                <div class="dl-row">
                    <dt>{{ $enfant->sexe === 'M' ? 'G.' : 'F.' }}{{ $loop->iteration }}</dt>
                    <dd>
                        {{ $enfant->prenom }} {{ strtoupper($enfant->nom) }}
                        <span style="color:var(--col-text-3)">
                            · {{ $enfant->sexe === 'M' ? 'Masculin' : 'Féminin' }}
                            @if($enfant->date_naissance)
                                · né{{ $enfant->sexe === 'F' ? 'e' : '' }} le {{ $enfant->date_naissance->format('d/m/Y') }} ({{ $enfant->age }} ans)
                            @endif
                            @if($enfant->age !== null && $enfant->age > \App\Models\Personnel::AGE_LIMITE_ENFANT)
                                <span class="badge badge-warn">Hors limite d'âge</span>
                            @endif
                        </span>
                    </dd>
                </div>
            @empty
                <p class="empty-inline">Aucun enfant renseigné.</p>
            @endforelse
        </div>

        @if($personnel->conge_annee || $personnel->conge_jours)
        <div class="dash-card">
            <div class="card-header"><h3>Congé</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Année</dt><dd>{{ $personnel->conge_annee ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Nombre de jours</dt><dd>{{ $personnel->conge_jours ?? '—' }} j</dd></div>
            </dl>
        </div>
        @endif

        @if($personnel->affectation)
        <div class="dash-card">
            <div class="card-header"><h3>Affectation</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Structure</dt><dd>{{ $personnel->affectation }}</dd></div>
                @if($personnel->date_affectation)
                <div class="dl-row"><dt>Date</dt><dd>{{ $personnel->date_affectation->format('d/m/Y') }}</dd></div>
                @endif
            </dl>
        </div>
        @endif

    </div>
</div>

{{-- ── Contrats ────────────────────────────────────────────────────────────── --}}
<div class="dash-card" style="margin-top:8px">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3>Contrats ({{ $personnel->contrats->count() }})</h3>
        @if(!auth()->user()->isReadOnly())
        <a href="{{ route('contrats.create', $personnel) }}" class="btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouveau contrat
        </a>
        @endif
    </div>

    @forelse($personnel->contrats as $contrat)
    <div class="dl-row" style="align-items:flex-start;padding:14px 0;border-bottom:1px solid var(--col-border)">
        <dd style="width:100%">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:6px">
                <div>
                    <span class="contrat-tag contrat-{{ strtolower($contrat->type_contrat) }}">{{ $contrat->type_contrat }}</span>
                    <span class="badge {{ $contrat->est_en_cours ? 'badge-green' : 'badge-warn' }}">{{ $contrat->statut_label }}</span>
                    @if($contrat->fonction) <strong>{{ $contrat->fonction }}</strong> @endif
                    @if($contrat->centre) <span style="color:var(--col-text-3)">— {{ $contrat->centre }}</span> @endif
                </div>
                <div style="display:flex;gap:8px">
                    @if(in_array($contrat->type_contrat, ['CDD','CDI','Prestataire']))
                        <a href="{{ route('contrats.document', [$personnel, $contrat]) }}" target="_blank" class="btn-ghost btn-sm">Voir le document</a>
                    @endif
                    @if(!auth()->user()->isReadOnly())
                    <a href="{{ route('contrats.edit', [$personnel, $contrat]) }}" class="btn-ghost btn-sm">Modifier</a>
                    <form method="POST" action="{{ route('contrats.destroy', [$personnel, $contrat]) }}" onsubmit="return confirm('Supprimer ce contrat ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-ghost btn-sm" style="color:var(--col-danger, #c0392b)">Supprimer</button>
                    </form>
                    @endif
                </div>
            </div>
            <div style="font-size:.82rem;color:var(--col-text-2)">
                Du {{ $contrat->date_debut->format('d/m/Y') }}
                {{ $contrat->date_fin ? 'au ' . $contrat->date_fin->format('d/m/Y') : '· durée indéterminée' }}
                @if($contrat->salaire_base) · {{ number_format((float)$contrat->salaire_base, 0, ',', ' ') }} FCFA/mois @endif
                @if($contrat->type_contrat === 'Prestataire' && ($contrat->honoraire_garde || $contrat->honoraire_permanence))
                    · Garde : {{ $contrat->honoraire_garde ? number_format((float)$contrat->honoraire_garde, 0, ',', ' ') . ' FCFA' : '—' }}
                    · Permanence : {{ $contrat->honoraire_permanence ? number_format((float)$contrat->honoraire_permanence, 0, ',', ' ') . ' FCFA' : '—' }}
                @endif
                @if($contrat->categorie && $contrat->echelon) · {{ $contrat->categorie }}-{{ $contrat->echelon }} @elseif($contrat->categorie_echelon) · {{ $contrat->categorie_echelon }} @endif
            </div>
        </dd>
    </div>
    @empty
        <p class="empty-inline">Aucun contrat enregistré pour le moment.</p>
    @endforelse
</div>

{{-- ── Avancements (échelon / bonification) ───────────────────────────────── --}}
<div class="dash-card" style="margin-top:8px">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3>Avancements ({{ $personnel->avancements->count() }})</h3>
        <form method="POST" action="{{ route('avancements.verifier-personnel', $personnel) }}">
            @csrf
            <button type="submit" class="btn-ghost btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                Vérifier son avancement
            </button>
        </form>
    </div>
    @forelse($personnel->avancements as $av)
    <div class="dl-row" style="align-items:flex-start;padding:12px 0;border-bottom:1px solid var(--col-border)">
        <dd style="width:100%">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px">
                <div>
                    <span class="badge {{ $av->type === 'bonification' ? 'badge-purple' : 'badge-green' }}">{{ $av->type_label }}</span>
                    <span style="font-size:.82rem;color:var(--col-text-2)">{{ $av->date_effet->format('d/m/Y') }}</span>
                    @if($av->type === 'echelon')
                        — {{ $av->ancienne_categorie }}-{{ $av->ancien_echelon }} → <strong>{{ $av->nouvelle_categorie }}-{{ $av->nouvel_echelon }}</strong>
                    @endif
                    — <strong>{{ number_format($av->nouveau_salaire, 0, ',', ' ') }} FCFA</strong>
                </div>
                <div>
                    <a href="{{ route('avancements.document', $av) }}" target="_blank" class="btn-ghost btn-sm">Voir la lettre</a>
                </div>
            </div>
            <div style="font-size:.75rem;color:var(--col-text-3);margin-top:2px">Réf. {{ $av->numero_reference }}</div>
        </dd>
    </div>
    @empty
        <p class="empty-inline">Aucun avancement enregistré pour le moment. Ils sont générés automatiquement (tous les 2 ans d'ancienneté, ou à 58 ans).</p>
    @endforelse
</div>

{{-- Zone danger --}}
@if(!auth()->user()->isReadOnly())
<div class="dash-card danger-zone" style="max-width:600px; margin-top:8px;">
    <div class="card-header"><h3 class="danger-title">Zone de danger</h3></div>
    @if($personnel->est_ancien_travailleur)
        <p class="danger-text">Cet agent est déjà classé « Ancien travailleur ». Utilisez le bouton « Restaurer cet agent » plus haut pour le remettre actif.</p>
    @else
        <p class="danger-text">Archiver cet agent le déplacera vers « Anciens travailleurs ». L'action est réversible (restauration possible).</p>
        <button type="button" class="btn-danger" onclick="document.getElementById('modal-archiver').style.display='flex'">Archiver cet agent</button>
    @endif
</div>
@endif

{{-- ── Modal Archiver (Ancien travailleur) ────────────────────────────────── --}}
<div id="modal-archiver" class="modal-backdrop" style="display:none" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Archiver {{ $personnel->prenoms }} {{ strtoupper($personnel->nom) }}</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('modal-archiver').style.display='none'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('personnel.archiver', $personnel) }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Motif du départ <span class="req">*</span></label>
                    <div class="input-wrapper select-wrapper">
                        <select name="motif_depart" required>
                            <option value="">— Sélectionner —</option>
                            @foreach(\App\Models\Personnel::motifsDepart() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Date de départ <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <input type="date" name="date_depart" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <p style="font-size:.78rem;color:var(--col-text-2)">
                    Cet agent apparaîtra dans « Anciens travailleurs ». Vous pourrez le restaurer à tout moment depuis sa fiche.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal-archiver').style.display='none'">Annuler</button>
                <button type="submit" class="btn-danger">Confirmer l'archivage</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal Affectation ──────────────────────────────────────────────────── --}}
<div id="modal-affecter" class="modal-backdrop" style="display:none" onclick="if(event.target===this)this.style.display='none'">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Affecter {{ $personnel->prenoms }} {{ strtoupper($personnel->nom) }}</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('modal-affecter').style.display='none'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('personnel.affecter', $personnel) }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Centre d'affectation <span class="req">*</span></label>
                    <div class="input-wrapper select-wrapper">
                        <select name="centre_id" required>
                            <option value="">— Sélectionner un centre —</option>
                            @foreach($centres as $centre)
                                <option value="{{ $centre->id }}" {{ (string) $personnel->centre_id === (string) $centre->id ? 'selected' : '' }}>{{ $centre->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row-2col">
                    <div class="form-group">
                        <label>Nouveau service</label>
                        <div class="input-wrapper select-wrapper">
                            <select name="service">
                                <option value="">Inchangé</option>
                                @foreach(\App\Models\Personnel::services() as $s)
                                    <option value="{{ $s }}" {{ $personnel->service === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Date d'affectation</label>
                        <div class="input-wrapper">
                            <input type="date" name="date_affectation"
                                   value="{{ $personnel->date_affectation ? $personnel->date_affectation->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modal-affecter').style.display='none'">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer l'affectation</button>
            </div>
        </form>
    </div>
</div>

{{-- Modale Facebook Lightbox Photo --}}
<dialog id="modalFbPhoto" style="border: none; border-radius: 16px; padding: 0; max-width: 560px; background: rgba(17, 24, 39, 0.95); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); backdrop-filter: blur(8px);">
    <div style="position: relative; padding: 1.5rem; text-align: center;">
        <button onclick="document.getElementById('modalFbPhoto').close()" style="position: absolute; top: 12px; right: 16px; background: rgba(255,255,255,0.2); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">&times;</button>
        <div style="font-weight: 600; color: #fff; font-size: 1.1rem; margin-bottom: 1rem;">Photo de profil — {{ $personnel->nom_complet }}</div>
        <img id="fbModalImg" src="" alt="Photo" style="max-width: 100%; max-height: 70vh; border-radius: 12px; border: 3px solid rgba(255,255,255,0.3); box-shadow: 0 10px 30px rgba(0,0,0,0.5); object-fit: contain;">
        <div style="margin-top: 1rem; font-size: 0.82rem; color: #9ca3af;">{{ $personnel->corporation ?? 'Personnel RH Plus' }}</div>
    </div>
</dialog>

@push('styles')
<style>
.fb-profile-photo-container {
    position: relative;
    width: 168px;
    height: 168px;
    flex-shrink: 0;
    cursor: pointer;
}
.fb-profile-photo {
    width: 168px !important;
    height: 168px !important;
    border-radius: 50% !important;
    object-fit: cover !important;
    border: 4px solid #ffffff !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15) !important;
    transition: transform 0.25s ease, box-shadow 0.25s ease !important;
    background: #f3f4f6;
}
.fb-profile-photo-container:hover .fb-profile-photo {
    transform: scale(1.03);
    box-shadow: 0 6px 22px rgba(0, 0, 0, 0.22) !important;
}
.fb-photo-badge {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: #ffffff;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    border: 1px solid #e5e7eb;
}
</style>
@endpush

@push('scripts')
<script>
function openPhotoModal() {
    const src = document.getElementById('profileImg').src;
    document.getElementById('fbModalImg').src = src;
    document.getElementById('modalFbPhoto').showModal();
}
</script>
@endpush
@endsection