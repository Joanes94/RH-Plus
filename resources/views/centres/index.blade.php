@extends('layouts.app')

@section('title', 'Gestion des centres')
@section('page-title', 'Centres de santé & Identité officielle')

@section('content')
{{-- ── En-tête de la page ────────────────────────────────────────────────── --}}
<div class="page-header">
    <div>
        <h1 class="page-heading">Centres de santé</h1>
        <p class="page-subtitle">Gestion de l'ordre d'affichage, des identités visuelles et des accès pour tous les établissements</p>
    </div>
    <div class="header-actions">
        <button class="btn btn-primary" onclick="document.getElementById('modalAjout').showModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouveau centre
        </button>
    </div>
</div>

{{-- ── KPI Cards / Résumé Synthétique ──────────────────────────────────────── --}}
@php
    $totalCentres = $centres->count();
    $centresActifs = $centres->where('actif', true)->count();
    $totalAgents = $centres->sum('effectif_actif');
    $drhDedieCount = $centres->where('a_drh_dedie', true)->count();
@endphp
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(26, 92, 69, 0.1); color: var(--col-primary, #1a5c45);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div class="kpi-info">
            <span class="kpi-value">{{ $totalCentres }}</span>
            <span class="kpi-label">Centres enregistrés ({{ $centresActifs }} actifs)</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.1); color: #2563eb;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="kpi-info">
            <span class="kpi-value">{{ $totalAgents }}</span>
            <span class="kpi-label">Agents actifs sous gestion</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(139, 92, 246, 0.1); color: #7c3aed;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M16 11l2 2 4-4"/></svg>
        </div>
        <div class="kpi-info">
            <span class="kpi-value">{{ $drhDedieCount }} / {{ $totalCentres }}</span>
            <span class="kpi-label">Centres avec DRH dédié</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
        </div>
        <div class="kpi-info">
            <span class="kpi-value">{{ $centres->whereNotNull('logo_path')->count() }}</span>
            <span class="kpi-label">Identités & Visuels configurés</span>
        </div>
    </div>
</div>

{{-- ── Barre d'outils et de filtres ────────────────────────────────────────── --}}
<div class="centres-toolbar">
    <div class="search-box">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="searchCentre" placeholder="Rechercher un centre par nom, code ou email..." onkeyup="filterCentres()">
    </div>

    <div class="toolbar-right">
        <div class="view-toggle">
            <button class="view-btn active" id="btnGridView" onclick="setViewMode('grid')" title="Vue Cartes (Glisser pour réordonner)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Cartes
            </button>
            <button class="view-btn" id="btnTableView" onclick="setViewMode('table')" title="Vue Tableau">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Tableau
            </button>
        </div>
    </div>
</div>

{{-- ── Vue Cartes (Grid View avec Drag & Drop et Boutons d'Ordre) ───────────── --}}
<div class="centres-grid-wrapper" id="gridViewSection">
    <div class="reorder-info-banner">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>Glissez les cartes pour réorganiser l'ordre dans la barre latérale, ou utilisez les flèches <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg> sur chaque carte.</span>
    </div>

    <div class="centres-grid" id="centresGrid">
        @foreach($centres as $index => $centre)
        <div class="centre-card {{ $centre->actif ? '' : 'centre-inactif' }}" data-id="{{ $centre->id }}" data-search="{{ strtolower($centre->nom . ' ' . $centre->code . ' ' . $centre->email) }}">
            <div class="centre-card-header">
                <div class="order-badge" title="Position d'affichage N° {{ $index + 1 }}">#{{ $index + 1 }}</div>
                
                @if($centre->logo_url)
                    <img src="{{ $centre->logo_url }}" alt="Logo {{ $centre->nom }}" class="centre-logo-img">
                @else
                    <div class="centre-icon" style="background: {{ ['#1a5c45','#3b82f6','#8b5cf6','#f59e0b','#ec4899','#06b6d4','#10b981','#6366f1'][$loop->index % 8] }}">
                        {{ substr($centre->code, 0, 2) }}
                    </div>
                @endif

                <div class="centre-meta">
                    <h3 class="centre-nom">{{ $centre->nom }}</h3>
                    <span class="centre-code">{{ $centre->code }}</span>
                </div>

                <div class="header-controls">
                    {{-- Flèches de déplacement rapide --}}
                    <div class="arrow-group">
                        @if(!$loop->first)
                        <form action="{{ route('centres.move', [$centre, 'up']) }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn-arrow" title="Monter d'une position">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                            </button>
                        </form>
                        @endif
                        @if(!$loop->last)
                        <form action="{{ route('centres.move', [$centre, 'down']) }}" method="POST" style="display:inline">
                            @csrf
                            <button type="submit" class="btn-arrow" title="Descendre d'une position">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>

                    <button class="btn-icon" onclick='openEditModal(@json($centre))' title="Modifier le centre">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                </div>
            </div>

            <div class="centre-card-body">
                <div class="centre-stat">
                    <span class="stat-value">{{ $centre->effectif_actif }}</span>
                    <span class="stat-label">agents actifs en poste</span>
                </div>

                <div class="centre-infos">
                    @if($centre->email)
                    <div class="info-row">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <span>{{ $centre->email }}</span>
                    </div>
                    @endif
                    @if($centre->telephone)
                    <div class="info-row">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span>{{ $centre->telephone }}</span>
                    </div>
                    @endif
                    @if($centre->reference_suffix)
                    <div class="info-row">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span class="ref-code">Réf: {{ $centre->reference_suffix }}</span>
                    </div>
                    @endif
                </div>

                <div class="centre-card-footer">
                    <div class="centre-badges">
                        @if($centre->a_drh_dedie)
                            <span class="badge badge-drh" title="Un DRH dédié gère les demandes de ce centre">DRH dédié</span>
                        @else
                            <span class="badge badge-dir" title="Le Directeur assure la gestion RH">Directeur = DRH</span>
                        @endif
                    </div>

                    <div class="footer-actions">
                        <form action="{{ route('centres.toggle-status', $centre) }}" method="POST" style="display:inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="status-toggle-btn {{ $centre->actif ? 'is-active' : 'is-inactive' }}" 
                                    title="{{ $centre->actif ? 'Cliquer pour désactiver le centre' : 'Cliquer pour activer le centre' }}">
                                <span class="toggle-switch"></span>
                                <span class="toggle-label">{{ $centre->actif ? 'Actif' : 'Inactif' }}</span>
                            </button>
                        </form>

                        <a href="{{ route('dashboard.centre', ['centre_id' => $centre->id]) }}" class="btn-dashboard" title="Ouvrir le tableau de bord du centre">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            Accéder
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Vue Tableau (Table View) ───────────────────────────────────────────── --}}
<div class="centres-table-wrapper" id="tableViewSection" style="display: none;">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 70px;">Ordre</th>
                    <th>Centre de Santé</th>
                    <th>Code</th>
                    <th>Effectif</th>
                    <th>Mode Gestion RH</th>
                    <th>Suffixe Réf.</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($centres as $index => $c)
                <tr class="table-row-item {{ $c->actif ? '' : 'tr-inactif' }}" data-search="{{ strtolower($c->nom . ' ' . $c->code . ' ' . $c->email) }}">
                    <td>
                        <div class="table-order-cell">
                            <span class="order-num">#{{ $index + 1 }}</span>
                            <div class="table-arrows">
                                @if(!$loop->first)
                                <form action="{{ route('centres.move', [$c, 'up']) }}" method="POST" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn-arrow-sm" title="Monter"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg></button>
                                </form>
                                @endif
                                @if(!$loop->last)
                                <form action="{{ route('centres.move', [$c, 'down']) }}" method="POST" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn-arrow-sm" title="Descendre"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg></button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="table-centre-cell">
                            @if($c->logo_url)
                                <img src="{{ $c->logo_url }}" alt="" class="table-logo-img">
                            @else
                                <div class="table-icon-avatar" style="background: {{ ['#1a5c45','#3b82f6','#8b5cf6','#f59e0b','#ec4899','#06b6d4'][$loop->index % 6] }}">
                                    {{ substr($c->code, 0, 2) }}
                                </div>
                            @endif
                            <div>
                                <div class="table-nom">{{ $c->nom }}</div>
                                <div class="table-email">{{ $c->email ?: 'Aucun email' }}</div>
                            </div>
                        </div>
                    </td>
                    <td><code class="code-pill">{{ $c->code }}</code></td>
                    <td><strong>{{ $c->effectif_actif }}</strong> <span class="unit">agents</span></td>
                    <td>
                        @if($c->a_drh_dedie)
                            <span class="badge badge-drh">DRH dédié</span>
                        @else
                            <span class="badge badge-dir">Directeur = DRH</span>
                        @endif
                    </td>
                    <td><span class="ref-code">{{ $c->reference_suffix ?: '—' }}</span></td>
                    <td>
                        <form action="{{ route('centres.toggle-status', $c) }}" method="POST" style="display:inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="badge {{ $c->actif ? 'badge-actif' : 'badge-inactif' }}" style="border:none; cursor:pointer;" title="Cliquer pour changer">
                                {{ $c->actif ? '● Actif' : '○ Inactif' }}
                            </button>
                        </form>
                    </td>
                    <td style="text-align: right;">
                        <div class="table-actions">
                            <button class="btn-icon" onclick='openEditModal(@json($c))' title="Modifier">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <a href="{{ route('dashboard.centre', ['centre_id' => $c->id]) }}" class="btn-icon" title="Ouvrir le tableau de bord">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── Modal Création de Centre ────────────────────────────────────────────── --}}
<dialog id="modalAjout" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2>Nouveau centre de santé</h2>
                <p class="modal-subtitle">Enregistrez l'établissement et son identité officielle</p>
            </div>
            <button type="button" onclick="document.getElementById('modalAjout').close()" class="modal-close">&times;</button>
        </div>
        <form action="{{ route('centres.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom">Nom du centre *</label>
                        <input type="text" name="nom" id="nom" required class="form-control" placeholder="Ex: Hôpital Saint Luc (CSVH)">
                    </div>
                    <div class="form-group">
                        <label for="code">Code unique *</label>
                        <input type="text" name="code" id="code" required class="form-control" placeholder="Ex: ST_LUC" maxlength="30">
                    </div>
                    <div class="form-group">
                        <label for="email">Adresse Email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="contact@centre.org">
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="text" name="telephone" id="telephone" class="form-control" placeholder="+229 ...">
                    </div>
                    <div class="form-group">
                        <label for="ifu">N° IFU du centre</label>
                        <input type="text" name="ifu" id="ifu" class="form-control" placeholder="1234567890">
                    </div>
                    <div class="form-group">
                        <label for="numero_cnss">N° CNSS Employeur</label>
                        <input type="text" name="numero_cnss" id="numero_cnss" class="form-control" placeholder="98765-43">
                    </div>
                    <div class="form-group full-width">
                        <label for="adresse">Adresse géographique complète</label>
                        <input type="text" name="adresse" id="adresse" class="form-control" placeholder="Quartier, Ville, Commune...">
                    </div>
                    <div class="form-group full-width">
                        <label for="logo">Logo / Emblème officiel du centre</label>
                        <input type="file" name="logo" id="logo" accept="image/*" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="entete_image">Image d'en-tête officielle (Pleine largeur - Remplace le logo et le texte d'en-tête s'il est fourni)</label>
                        <input type="file" name="entete_image" id="entete_image" accept="image/*" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="reference_suffix">Suffixe de référence officiel pour les documents</label>
                        <input type="text" name="reference_suffix" id="reference_suffix" class="form-control" placeholder="Ex: CSVHHSL/DIR/DRH">
                    </div>
                    <div class="form-group full-width">
                        <label for="entete_texte">Texte d'en-tête officiel (Imprimé sur les lettres)</label>
                        <textarea name="entete_texte" id="entete_texte" rows="2" class="form-control" placeholder="CENTRE SANITAIRE ET DE SANTÉ SAINT LUC..."></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="pied_page_texte">Pied de page / Mentions légales</label>
                        <textarea name="pied_page_texte" id="pied_page_texte" rows="2" class="form-control" placeholder="N° IFU: ... - Tél: ... - Email: ..."></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label class="checkbox-card">
                            <input type="hidden" name="a_drh_dedie" value="0">
                            <input type="checkbox" name="a_drh_dedie" value="1">
                            <div>
                                <strong>Ce centre possède un DRH dédié</strong>
                                <small>Si décoché, le Directeur du centre fait office de DRH pour les validations.</small>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('modalAjout').close()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer le centre</button>
            </div>
        </form>
    </div>
</dialog>

{{-- ── Modal Édition de Centre ────────────────────────────────────────────── --}}
<dialog id="modalEdit" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h2>Modifier le centre</h2>
                <p class="modal-subtitle">Mise à jour des coordonnées et paramètres d'en-tête</p>
            </div>
            <button type="button" onclick="document.getElementById('modalEdit').close()" class="modal-close">&times;</button>
        </div>
        <form id="formEditCentre" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_nom">Nom du centre *</label>
                        <input type="text" name="nom" id="edit_nom" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Adresse Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_telephone">Téléphone</label>
                        <input type="text" name="telephone" id="edit_telephone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_ifu">N° IFU du centre</label>
                        <input type="text" name="ifu" id="edit_ifu" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_numero_cnss">N° CNSS Employeur</label>
                        <input type="text" name="numero_cnss" id="edit_numero_cnss" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_reference_suffix">Suffixe de référence officiel</label>
                        <input type="text" name="reference_suffix" id="edit_reference_suffix" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_adresse">Adresse complète</label>
                        <input type="text" name="adresse" id="edit_adresse" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_logo">Nouveau Logo / Emblème (Laissez vide pour conserver l'actuel)</label>
                        <input type="file" name="logo" id="edit_logo" accept="image/*" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_entete_image">Nouvelle Image d'en-tête officielle (Laissez vide pour conserver l'actuel)</label>
                        <input type="file" name="entete_image" id="edit_entete_image" accept="image/*" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_entete_texte">Texte d'en-tête officiel</label>
                        <textarea name="entete_texte" id="edit_entete_texte" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_pied_page_texte">Pied de page / Mentions légales</label>
                        <textarea name="pied_page_texte" id="edit_pied_page_texte" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label class="checkbox-card">
                            <input type="hidden" name="a_drh_dedie" value="0">
                            <input type="checkbox" name="a_drh_dedie" id="edit_a_drh_dedie" value="1">
                            <div>
                                <strong>Ce centre possède un DRH dédié</strong>
                                <small>Si décoché, le Directeur du centre fait office de DRH pour les validations.</small>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('modalEdit').close()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</dialog>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
function openEditModal(centre) {
    const form = document.getElementById('formEditCentre');
    form.action = `/centres/${centre.id}`;

    document.getElementById('edit_nom').value = centre.nom || '';
    document.getElementById('edit_email').value = centre.email || '';
    document.getElementById('edit_telephone').value = centre.telephone || '';
    document.getElementById('edit_ifu').value = centre.ifu || '';
    document.getElementById('edit_numero_cnss').value = centre.numero_cnss || '';
    document.getElementById('edit_reference_suffix').value = centre.reference_suffix || '';
    document.getElementById('edit_adresse').value = centre.adresse || '';
    document.getElementById('edit_entete_texte').value = centre.entete_texte || '';
    document.getElementById('edit_pied_page_texte').value = centre.pied_page_texte || '';
    document.getElementById('edit_a_drh_dedie').checked = Boolean(centre.a_drh_dedie);

    document.getElementById('modalEdit').showModal();
}

function setViewMode(mode) {
    const gridSection = document.getElementById('gridViewSection');
    const tableSection = document.getElementById('tableViewSection');
    const btnGrid = document.getElementById('btnGridView');
    const btnTable = document.getElementById('btnTableView');

    if (mode === 'grid') {
        gridSection.style.display = 'block';
        tableSection.style.display = 'none';
        btnGrid.classList.add('active');
        btnTable.classList.remove('active');
    } else {
        gridSection.style.display = 'none';
        tableSection.style.display = 'block';
        btnGrid.classList.remove('active');
        btnTable.classList.add('active');
    }
}

function filterCentres() {
    const query = document.getElementById('searchCentre').value.toLowerCase().trim();

    // Filtre des cartes
    document.querySelectorAll('.centre-card').forEach(card => {
        const searchText = card.dataset.search || '';
        card.style.display = searchText.includes(query) ? 'flex' : 'none';
    });

    // Filtre des lignes de tableau
    document.querySelectorAll('.table-row-item').forEach(row => {
        const searchText = row.dataset.search || '';
        row.style.display = searchText.includes(query) ? 'table-row' : 'none';
    });
}

function showToast(message, type = 'success') {
    const existing = document.querySelector('.reorder-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'reorder-toast reorder-toast-' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('centresGrid');
    if (!grid) return;

    Sortable.create(grid, {
        animation: 250,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        forceFallback: true,
        fallbackClass: 'sortable-fallback',
        fallbackOnBody: true,
        swapThreshold: 0.65,
        easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
        filter: '.btn-icon, .btn-primary, .btn-arrow, .status-toggle-btn, .btn-dashboard, dialog, .modal',
        preventOnFilter: false,
        onStart: function() {
            grid.classList.add('is-sorting');
        },
        onEnd: function (evt) {
            grid.classList.remove('is-sorting');

            const item = evt.item;
            item.classList.add('just-dropped');
            setTimeout(() => item.classList.remove('just-dropped'), 400);

            const ids = [...grid.querySelectorAll('.centre-card')].map(el => el.dataset.id);

            fetch('{{ route("centres.reorder") }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ ordre: ids }),
            }).then(r => {
                if (r.ok) {
                    showToast('Ordre des centres mis à jour \u2713');
                } else {
                    showToast('Erreur de sauvegarde', 'error');
                }
            }).catch(() => showToast('Erreur réseau', 'error'));
        },
    });
});
</script>
@endpush

@push('styles')
<style>
/* ── Barre d'outils centres ────────────────────────────────────────────────── */
.centres-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 280px;
    max-width: 460px;
}
.search-box svg {
    position: absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}
.search-box input {
    width: 100%;
    padding: 0.55rem 0.85rem 0.55rem 2.25rem;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    font-size: 0.88rem;
    background: #fff;
    transition: all 0.2s ease;
}
.search-box input:focus {
    outline: none;
    border-color: var(--col-primary, #1a5c45);
    box-shadow: 0 0 0 3px rgba(26, 92, 69, 0.12);
}

.toolbar-right { display: flex; align-items: center; gap: 0.75rem; }

.view-toggle {
    display: flex;
    background: #e5e7eb;
    padding: 3px;
    border-radius: 10px;
    gap: 2px;
}
.view-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 6px 12px;
    border: none;
    border-radius: 7px;
    font-size: 0.8rem; font-weight: 500;
    color: #4b5563;
    background: transparent;
    cursor: pointer;
    transition: all 0.15s ease;
}
.view-btn.active {
    background: #fff;
    color: #111827;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    font-weight: 600;
}

/* ── Bannière info réordonnancement ─────────────────────────────────────── */
.reorder-info-banner {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.65rem 1rem;
    background: rgba(59, 130, 246, 0.07);
    border: 1px solid rgba(59, 130, 246, 0.2);
    border-radius: 10px;
    font-size: 0.78rem;
    color: #1d4ed8;
    margin-bottom: 1.25rem;
}
.reorder-info-banner svg { flex-shrink: 0; }

/* ── Grille & Cartes ─────────────────────────────────────────────────────── */
.centres-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
    gap: 1.25rem;
    align-items: start;
}

.centre-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    cursor: grab;
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1),
                box-shadow 0.3s cubic-bezier(0.22, 1, 0.36, 1),
                border-color 0.3s ease;
    will-change: transform;
    user-select: none;
}
.centre-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 36px rgba(0,0,0,0.09);
    border-color: #cbd5e1;
}
.centre-inactif { opacity: 0.6; }

/* Sortable states */
.sortable-ghost {
    opacity: 0.25;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border: 2px dashed var(--col-primary, #1a5c45) !important;
    border-radius: 16px;
    box-shadow: none !important;
}
.sortable-chosen {
    box-shadow: 0 20px 50px rgba(26, 92, 69, 0.20) !important;
    border-color: var(--col-primary, #1a5c45) !important;
    transform: rotate(-1deg) scale(1.02) !important;
    z-index: 100;
    cursor: grabbing;
}
.sortable-fallback {
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.2) !important;
    border-color: var(--col-primary, #1a5c45) !important;
    transform: rotate(-1.5deg) scale(1.03) !important;
    border-radius: 16px;
    opacity: 0.95 !important;
    cursor: grabbing !important;
}

.centre-card-header {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1.15rem 1.25rem 0.65rem;
    position: relative;
}
.order-badge {
    position: absolute;
    top: 8px; left: 12px;
    font-size: 0.68rem; font-weight: 700;
    color: #9ca3af; font-family: monospace;
}
.centre-logo-img {
    width: 44px; height: 44px;
    object-fit: contain; border-radius: 10px;
    border: 1px solid #f0f0f0; padding: 2px;
    pointer-events: none; margin-top: 8px;
}
.centre-icon {
    width: 44px; height: 44px;
    border-radius: 12px; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 700;
    flex-shrink: 0; pointer-events: none; margin-top: 8px;
}

.centre-meta { flex: 1; min-width: 0; margin-top: 8px; }
.centre-nom { font-size: 0.95rem; font-weight: 600; color: #111827; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.centre-code { font-size: 0.72rem; color: #9ca3af; font-family: monospace; }

.header-controls { display: flex; align-items: center; gap: 0.4rem; margin-top: 8px; }
.arrow-group { display: flex; flex-direction: column; gap: 2px; }
.btn-arrow {
    width: 20px; height: 16px;
    border-radius: 4px; border: 1px solid #e5e7eb;
    background: #f9fafb; color: #6b7280;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; padding: 0;
    transition: all 0.15s;
}
.btn-arrow:hover { background: #3b82f6; color: #fff; border-color: #3b82f6; }

.btn-icon {
    width: 32px; height: 32px;
    border-radius: 8px; border: 1px solid #e5e7eb;
    background: transparent;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: #6b7280;
    transition: all 0.15s;
}
.btn-icon:hover { background: #f3f4f6; color: #111827; border-color: #d1d5db; }

.centre-card-body { padding: 0 1.25rem 1.25rem; }
.centre-stat { text-align: center; padding: 0.65rem 0; background: #f9fafb; border-radius: 10px; margin-bottom: 0.75rem; }
.stat-value { display: block; font-size: 1.8rem; font-weight: 700; color: var(--col-primary, #1a5c45); line-height: 1.2; }
.stat-label { font-size: 0.73rem; color: #6b7280; }

.centre-infos { display: flex; flex-direction: column; gap: 0.25rem; margin-bottom: 0.85rem; }
.info-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem; color: #6b7280; }
.ref-code { font-family: monospace; font-size: 0.73rem; background: #f3f4f6; padding: 1px 5px; border-radius: 4px; }

.centre-card-footer {
    display: flex; justify-content: space-between; align-items: center;
    padding-top: 0.75rem; border-top: 1px solid #f3f4f6;
    gap: 0.5rem;
}
.centre-badges { display: flex; gap: 0.3rem; flex-wrap: wrap; }
.badge-drh { background: rgba(59,130,246,0.1); color: #1d4ed8; font-size: 0.68rem; padding: 3px 8px; border-radius: 99px; font-weight: 600; }
.badge-dir { background: rgba(245,158,11,0.1); color: #b45309; font-size: 0.68rem; padding: 3px 8px; border-radius: 99px; font-weight: 600; }
.badge-actif { background: rgba(16,185,129,0.1); color: #047857; font-size: 0.68rem; padding: 3px 8px; border-radius: 99px; font-weight: 600; }
.badge-inactif { background: rgba(239,68,68,0.1); color: #b91c1c; font-size: 0.68rem; padding: 3px 8px; border-radius: 99px; font-weight: 600; }

.footer-actions { display: flex; align-items: center; gap: 0.4rem; }

.status-toggle-btn {
    display: flex; align-items: center; gap: 5px;
    padding: 3px 8px; border-radius: 99px;
    font-size: 0.72rem; font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer; transition: all 0.2s ease;
}
.status-toggle-btn.is-active { background: rgba(16,185,129,0.1); color: #047857; border-color: rgba(16,185,129,0.3); }
.status-toggle-btn.is-inactive { background: rgba(239,68,68,0.1); color: #b91c1c; border-color: rgba(239,68,68,0.3); }
.status-toggle-btn:hover { transform: scale(1.04); }

.btn-dashboard {
    display: flex; align-items: center; gap: 4px;
    padding: 5px 10px; border-radius: 8px;
    font-size: 0.75rem; font-weight: 600;
    background: var(--col-primary, #1a5c45); color: #fff;
    text-decoration: none; transition: background 0.15s ease;
}
.btn-dashboard:hover { background: #134433; color: #fff; text-decoration: none; }

/* ── Vue Tableau ─────────────────────────────────────────────────────────── */
.table-container {
    background: #fff; border-radius: 16px;
    border: 1px solid #e5e7eb; overflow: hidden;
}
.data-table { width: 100%; border-collapse: collapse; text-align: left; }
.data-table th {
    background: #f9fafb; padding: 0.85rem 1rem;
    font-size: 0.75rem; font-weight: 600; color: #6b7280;
    text-transform: uppercase; letter-spacing: 0.04em;
    border-bottom: 1px solid #e5e7eb;
}
.data-table td { padding: 0.85rem 1rem; border-bottom: 1px solid #f3f4f6; font-size: 0.85rem; vertical-align: middle; }
.tr-inactif { opacity: 0.55; }

.table-order-cell { display: flex; align-items: center; gap: 6px; }
.order-num { font-weight: 700; color: #9ca3af; font-family: monospace; font-size: 0.8rem; }
.table-arrows { display: flex; flex-direction: column; gap: 1px; }
.btn-arrow-sm {
    width: 16px; height: 13px; border-radius: 3px; border: 1px solid #e5e7eb;
    background: #fff; color: #6b7280; cursor: pointer; padding: 0;
    display: flex; align-items: center; justify-content: center;
}
.btn-arrow-sm:hover { background: #3b82f6; color: #fff; border-color: #3b82f6; }

.table-centre-cell { display: flex; align-items: center; gap: 0.75rem; }
.table-logo-img { width: 34px; height: 34px; object-fit: contain; border-radius: 8px; border: 1px solid #f0f0f0; }
.table-icon-avatar {
    width: 34px; height: 34px; border-radius: 8px; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem; font-weight: 700; flex-shrink: 0;
}
.table-nom { font-weight: 600; color: #111827; }
.table-email { font-size: 0.75rem; color: #9ca3af; }
.code-pill { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 0.78rem; }
.table-actions { display: flex; justify-content: flex-end; gap: 0.3rem; }

/* ── Modals & Formulaires ───────────────────────────────────────────────── */
.modal { border: none; border-radius: 20px; padding: 0; max-width: 640px; width: 92vw; box-shadow: 0 25px 70px rgba(0,0,0,0.2); }
.modal::backdrop { background: rgba(0,0,0,0.45); backdrop-filter: blur(5px); }
.modal-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; }
.modal-header h2 { margin: 0; font-size: 1.15rem; font-weight: 700; color: #111827; }
.modal-subtitle { margin: 2px 0 0; font-size: 0.78rem; color: #6b7280; }
.modal-close { background: none; border: none; font-size: 1.6rem; cursor: pointer; color: #9ca3af; padding: 0; line-height: 1; }
.modal-body { padding: 1.5rem; max-height: 75vh; overflow-y: auto; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.75rem; background: #fafafa; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.full-width { grid-column: 1 / -1; }
.form-group label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.form-control { width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem; box-sizing: border-box; }
.form-control:focus { outline: none; border-color: var(--col-primary, #1a5c45); box-shadow: 0 0 0 3px rgba(26, 92, 69, 0.12); }

.checkbox-card {
    display: flex; align-items: flex-start; gap: 0.75rem;
    padding: 0.85rem 1rem; background: #f9fafb; border: 1px solid #e5e7eb;
    border-radius: 10px; cursor: pointer;
}
.checkbox-card input { margin-top: 3px; }
.checkbox-card strong { display: block; font-size: 0.85rem; color: #111827; }
.checkbox-card small { display: block; font-size: 0.75rem; color: #6b7280; margin-top: 2px; }

/* ── Toast notification ──────────────────────────────────────────────────── */
.reorder-toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%) translateY(20px);
    padding: 10px 24px; border-radius: 12px;
    font-size: 0.85rem; font-weight: 600; z-index: 9999;
    opacity: 0; transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    pointer-events: none; backdrop-filter: blur(8px);
}
.reorder-toast-success { background: rgba(16, 185, 129, 0.95); color: #fff; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3); }
.reorder-toast-error { background: rgba(239, 68, 68, 0.95); color: #fff; box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3); }
.reorder-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>
@endpush
