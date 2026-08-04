@extends('layouts.app')
@section('title', $stagiaire->nom_complet)
@section('page-title', 'Fiche stagiaire')

@section('content')
<div class="page-header-bar">
    <a href="{{ route('stagiaires.index') }}" class="btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Retour à la liste
    </a>
    <div class="header-actions">
        <a href="{{ route('stagiaires.documents.choisir', $stagiaire) }}" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Documents
        </a>
        <a href="{{ route('stagiaires.edit', $stagiaire) }}" class="btn-ghost btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Modifier
        </a>
    </div>
</div>

{{-- En-tête fiche --}}
<div class="fiche-hero">
    @if($stagiaire->photo_url)
        <img src="{{ $stagiaire->photo_url }}" alt="Photo"
             style="width:64px;height:64px;object-fit:cover;border-radius:50%;border:2px solid var(--col-border-lg);flex-shrink:0">
    @else
        <div class="fiche-avatar">{{ $stagiaire->initiales }}</div>
    @endif
    <div class="fiche-hero-info">
        <h2>{{ $stagiaire->nom_complet }}</h2>
        <div class="fiche-tags">
            <span class="status-badge status-{{ $stagiaire->statut_color }}">{{ $stagiaire->statut_label }}</span>
            @if($stagiaire->titre)
                <span class="fiche-tag tag-blue">{{ $stagiaire->titre }}</span>
            @endif
            @if($stagiaire->niveau_etude)
                <span class="fiche-tag tag-green">{{ $stagiaire->niveau_etude }}</span>
            @endif
            @if($stagiaire->sexe === 'F')
                <span class="badge badge-warn" style="font-size:.68rem">Femme</span>
            @else
                <span class="badge badge-blue" style="font-size:.68rem">Homme</span>
            @endif
        </div>
    </div>
    <div class="fiche-hero-meta">
        @if($stagiaire->duree_stage)
        <div class="fiche-meta-item">
            <span class="meta-label">Durée du stage</span>
            <span class="meta-val">{{ $stagiaire->duree_stage }}</span>
        </div>
        @endif
        @if($stagiaire->type_stage)
        <div class="fiche-meta-item">
            <span class="meta-label">Type de stage</span>
            <span class="meta-val">{{ $stagiaire->type_stage }}</span>
        </div>
        @endif
        @if($stagiaire->createdBy)
        <div class="fiche-meta-item">
            <span class="meta-label">Enregistré par</span>
            <span class="meta-val">{{ $stagiaire->createdBy->prenoms }}</span>
        </div>
        @endif
    </div>
</div>

<div class="fiche-grid">
    <div class="fiche-col">

        {{-- Identité --}}
        <div class="dash-card">
            <div class="card-header"><h3>Identité</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Date de naissance</dt><dd>{{ $stagiaire->date_naissance ? $stagiaire->date_naissance->format('d/m/Y') : '—' }}</dd></div>
                <div class="dl-row"><dt>Lieu de naissance</dt><dd>{{ $stagiaire->lieu_naissance ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Sexe</dt><dd>{{ $stagiaire->sexe === 'M' ? 'Masculin' : 'Féminin' }}</dd></div>
                <div class="dl-row"><dt>Situation matrimoniale</dt><dd>{{ $stagiaire->situation_matrimoniale ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Téléphone</dt><dd>{{ $stagiaire->telephone ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Email</dt><dd>{{ $stagiaire->email ?: '—' }}</dd></div>
                <div class="dl-row">
                    <dt>Clientèle privée</dt>
                    <dd>
                        @if($stagiaire->autorisation_clientele_privee)
                            <span class="badge badge-green">Autorisé</span>
                        @else
                            <span class="badge badge-warn">Non autorisé</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Contact urgence --}}
        <div class="dash-card">
            <div class="card-header"><h3>Contact d'urgence</h3></div>
            @if($stagiaire->contact_urgence_nom || $stagiaire->contact_urgence_telephone)
            <dl class="detail-list">
                <div class="dl-row"><dt>Nom</dt><dd>{{ $stagiaire->contact_urgence_nom ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Téléphone</dt><dd>{{ $stagiaire->contact_urgence_telephone ?: '—' }}</dd></div>
            </dl>
            @else
                <p class="empty-inline">Aucun contact renseigné.</p>
            @endif
        </div>

    </div>

    <div class="fiche-col">

        {{-- Formation --}}
        <div class="dash-card">
            <div class="card-header"><h3>Formation</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Titre / Fonction</dt><dd>{{ $stagiaire->titre ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Niveau d'étude</dt><dd>{{ $stagiaire->niveau_etude ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Diplôme</dt><dd>{{ $stagiaire->diplome ?: '—' }}</dd></div>
                <div class="dl-row"><dt>École / Établissement</dt><dd>{{ $stagiaire->ecole_formation ?: '—' }}</dd></div>
            </dl>
        </div>

        {{-- Stage --}}
        <div class="dash-card">
            <div class="card-header"><h3>Période de stage</h3></div>
            <dl class="detail-list">
                <div class="dl-row"><dt>Service d'accueil</dt><dd>{{ $stagiaire->service ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Date de début</dt><dd>{{ $stagiaire->date_debut_stage ? $stagiaire->date_debut_stage->isoFormat('D MMMM YYYY') : '—' }}</dd></div>
                <div class="dl-row"><dt>Date de fin</dt><dd>{{ $stagiaire->date_fin_stage ? $stagiaire->date_fin_stage->isoFormat('D MMMM YYYY') : '—' }}</dd></div>
                <div class="dl-row"><dt>Durée</dt><dd>{{ $stagiaire->duree_stage ?: '—' }}</dd></div>
                <div class="dl-row"><dt>Type de stage</dt><dd>{{ $stagiaire->type_stage ?: '—' }}</dd></div>
                @if($stagiaire->observations)
                <div class="dl-row"><dt>Observations</dt><dd>{{ $stagiaire->observations }}</dd></div>
                @endif
            </dl>
        </div>

    </div>
</div>

{{-- Zone danger --}}
<div class="dash-card danger-zone" style="max-width:600px;margin-top:8px">
    <div class="card-header"><h3 class="danger-title">Zone de danger</h3></div>
    <p class="danger-text">Archiver ce stagiaire le masquera de la liste. L'action est réversible.</p>
    <form method="POST" action="{{ route('stagiaires.destroy', $stagiaire) }}"
          onsubmit="return confirm('Archiver {{ $stagiaire->nom_complet }} ?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn-danger">Archiver ce stagiaire</button>
    </form>
</div>

@endsection
