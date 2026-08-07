@extends('layouts.app')

@section('title', 'Gestion des centres')
@section('page-title', 'Centres de santé & Identité officielle')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-heading">Centres de santé</h1>
        <p class="page-subtitle">{{ $centres->count() }} centres enregistrés avec en-têtes et visuels officiels</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('modalAjout').showModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau centre
    </button>
</div>

<div class="centres-grid" id="centresGrid">
    @foreach($centres as $centre)
    <div class="centre-card {{ $centre->actif ? '' : 'centre-inactif' }}" data-id="{{ $centre->id }}">
        <div class="centre-card-header">
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
            <button class="btn-icon" onclick='openEditModal(@json($centre))' title="Modifier l'identité et les coordonnées">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
        </div>

        <div class="centre-card-body">
            <div class="centre-stat">
                <span class="stat-value">{{ $centre->effectif_actif }}</span>
                <span class="stat-label">agents actifs</span>
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
                    <span style="font-family:monospace; font-size:0.75rem;">Réf: {{ $centre->reference_suffix }}</span>
                </div>
                @endif
            </div>

            <div class="centre-badges">
                @if($centre->a_drh_dedie)
                    <span class="badge badge-drh">DRH dédié</span>
                @else
                    <span class="badge badge-dir">Directeur = DRH</span>
                @endif
                <span class="badge {{ $centre->actif ? 'badge-actif' : 'badge-inactif' }}">
                    {{ $centre->actif ? 'Actif' : 'Inactif' }}
                </span>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Modal ajout centre --}}
<dialog id="modalAjout" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Ajouter un centre</h2>
            <button type="button" onclick="document.getElementById('modalAjout').close()" class="modal-close">&times;</button>
        </div>
        <form action="{{ route('centres.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom">Nom du centre *</label>
                        <input type="text" name="nom" id="nom" required class="form-control" placeholder="CSVH ...">
                    </div>
                    <div class="form-group">
                        <label for="code">Code *</label>
                        <input type="text" name="code" id="code" required class="form-control" placeholder="EX: ST_LUC" maxlength="30">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="contact@centre.org">
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="text" name="telephone" id="telephone" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="adresse">Adresse complète</label>
                        <input type="text" name="adresse" id="adresse" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="logo">Logo / Visuel officiel du centre</label>
                        <input type="file" name="logo" id="logo" accept="image/*" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="reference_suffix">Suffixe de référence officiel (ex: CSVHHSL/DIR/DRH)</label>
                        <input type="text" name="reference_suffix" id="reference_suffix" class="form-control" placeholder="CSVHHSL/DIR/DRH">
                    </div>
                    <div class="form-group full-width">
                        <label for="entete_texte">Texte d'en-tête officiel pour les documents</label>
                        <textarea name="entete_texte" id="entete_texte" rows="2" class="form-control" placeholder="CENTRE SANITAIRE ET DE SANTÉ..."></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="pied_page_texte">Pied de page / Mentions légales pour les documents</label>
                        <textarea name="pied_page_texte" id="pied_page_texte" rows="2" class="form-control" placeholder="N° IFU: ... - Tél: ... - Email: ..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="hidden" name="a_drh_dedie" value="0">
                            <input type="checkbox" name="a_drh_dedie" value="1">
                            Ce centre a un DRH dédié
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

{{-- Modal édition centre --}}
<dialog id="modalEdit" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Modifier l'identité du centre</h2>
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
                        <label for="edit_email">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_telephone">Téléphone</label>
                        <input type="text" name="telephone" id="edit_telephone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_reference_suffix">Suffixe de référence officiel</label>
                        <input type="text" name="reference_suffix" id="edit_reference_suffix" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_adresse">Adresse</label>
                        <input type="text" name="adresse" id="edit_adresse" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_logo">Nouveau Logo / Visuel (Laissez vide pour conserver l'actuel)</label>
                        <input type="file" name="logo" id="edit_logo" accept="image/*" class="form-control">
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_entete_texte">Texte d'en-tête officiel</label>
                        <textarea name="entete_texte" id="edit_entete_texte" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_pied_page_texte">Pied de page / Mentions légales</label>
                        <textarea name="pied_page_texte" id="edit_pied_page_texte" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="hidden" name="a_drh_dedie" value="0">
                            <input type="checkbox" name="a_drh_dedie" id="edit_a_drh_dedie" value="1">
                            Ce centre a un DRH dédié
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
function openEditModal(centre) {
    const form = document.getElementById('formEditCentre');
    form.action = `/centres/${centre.id}`;

    document.getElementById('edit_nom').value = centre.nom || '';
    document.getElementById('edit_email').value = centre.email || '';
    document.getElementById('edit_telephone').value = centre.telephone || '';
    document.getElementById('edit_reference_suffix').value = centre.reference_suffix || '';
    document.getElementById('edit_adresse').value = centre.adresse || '';
    document.getElementById('edit_entete_texte').value = centre.entete_texte || '';
    document.getElementById('edit_pied_page_texte').value = centre.pied_page_texte || '';
    document.getElementById('edit_a_drh_dedie').checked = Boolean(centre.a_drh_dedie);

    document.getElementById('modalEdit').showModal();
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
        filter: '.btn-icon, .btn-primary, dialog, .modal',
        preventOnFilter: false,
        onStart: function() {
            grid.classList.add('is-sorting');
        },
        onEnd: function (evt) {
            grid.classList.remove('is-sorting');

            // Bounce animation on dropped item
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
                    showToast('Ordre mis à jour \u2713');
                } else {
                    showToast('Erreur lors de la sauvegarde', 'error');
                }
            }).catch(() => showToast('Erreur réseau', 'error'));
        },
    });
});
</script>
@endpush

@push('styles')
<style>
.centres-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
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
                border-color 0.3s ease,
                opacity 0.3s ease;
    will-change: transform;
    user-select: none;
    -webkit-user-select: none;
}
.centre-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.10);
    border-color: #d1d5db;
}
.centre-card:active { cursor: grabbing; }
.centre-inactif { opacity: 0.55; }

/* Sortable states */
.sortable-ghost {
    opacity: 0.25;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border: 2px dashed var(--col-primary, #1a5c45) !important;
    border-radius: 16px;
    box-shadow: none !important;
    transform: none !important;
}
.sortable-chosen {
    box-shadow: 0 20px 50px rgba(26, 92, 69, 0.20) !important;
    border-color: var(--col-primary, #1a5c45) !important;
    transform: rotate(-1deg) scale(1.03) !important;
    z-index: 100;
    cursor: grabbing;
}
.sortable-drag {
    opacity: 0 !important;
}
.sortable-fallback {
    box-shadow: 0 24px 60px rgba(26, 92, 69, 0.25) !important;
    border-color: var(--col-primary, #1a5c45) !important;
    transform: rotate(-1.5deg) scale(1.04) !important;
    border-radius: 16px;
    opacity: 0.95 !important;
    cursor: grabbing !important;
}

.is-sorting .centre-card:not(.sortable-chosen):not(.sortable-ghost) {
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}

@keyframes bounceIn {
    0%   { transform: scale(0.95); }
    50%  { transform: scale(1.03); }
    100% { transform: scale(1); }
}
.just-dropped {
    animation: bounceIn 0.4s cubic-bezier(0.22, 1, 0.36, 1);
}

/* Toast notification */
.reorder-toast {
    position: fixed;
    bottom: 24px; left: 50%;
    transform: translateX(-50%) translateY(20px);
    padding: 10px 24px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 500;
    z-index: 9999;
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
    pointer-events: none;
    backdrop-filter: blur(8px);
}
.reorder-toast-success {
    background: rgba(16, 185, 129, 0.9);
    color: #fff;
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
}
.reorder-toast-error {
    background: rgba(239, 68, 68, 0.9);
    color: #fff;
    box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
}
.reorder-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

.centre-card-header {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1.25rem 1.25rem 0.75rem;
}
.centre-logo-img {
    width: 44px; height: 44px;
    object-fit: contain; border-radius: 10px;
    border: 1px solid #f0f0f0; padding: 2px;
    transition: transform 0.2s ease;
    pointer-events: none;
}
.centre-card:hover .centre-logo-img { transform: scale(1.05); }
.centre-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 700;
    flex-shrink: 0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    pointer-events: none;
}
.centre-card:hover .centre-icon {
    transform: scale(1.08);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.centre-meta { flex: 1; min-width: 0; }
.centre-nom { font-size: 0.95rem; font-weight: 600; color: #111827; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.centre-code { font-size: 0.72rem; color: #9ca3af; font-family: monospace; }

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

.centre-stat { text-align: center; padding: 0.75rem 0; }
.stat-value { display: block; font-size: 2rem; font-weight: 700; color: var(--col-primary, #1a5c45); transition: transform 0.2s; }
.centre-card:hover .stat-value { transform: scale(1.05); }
.stat-label { font-size: 0.75rem; color: #6b7280; }

.centre-infos { margin-top: 0.5rem; }
.info-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem; color: #6b7280; padding: 0.25rem 0; }

.centre-badges { display: flex; gap: 0.4rem; margin-top: 0.75rem; flex-wrap: wrap; }
.badge-drh  { background: rgba(59,130,246,0.1); color: #1d4ed8; font-size: 0.7rem; padding: 3px 8px; border-radius: 99px; }
.badge-dir  { background: rgba(245,158,11,0.1); color: #b45309; font-size: 0.7rem; padding: 3px 8px; border-radius: 99px; }
.badge-actif  { background: rgba(16,185,129,0.1); color: #047857; font-size: 0.7rem; padding: 3px 8px; border-radius: 99px; }
.badge-inactif { background: rgba(239,68,68,0.1); color: #b91c1c; font-size: 0.7rem; padding: 3px 8px; border-radius: 99px; }

.modal { border: none; border-radius: 20px; padding: 0; max-width: 620px; width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
.modal::backdrop { background: rgba(0,0,0,0.4); backdrop-filter: blur(4px); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; }
.modal-header h2 { margin: 0; font-size: 1.1rem; font-weight: 600; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #9ca3af; padding: 0; line-height: 1; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.75rem; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.full-width { grid-column: 1 / -1; }
.checkbox-label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; cursor: pointer; }
.form-control { width: 100%; padding: 0.5rem; border-radius: 8px; border: 1px solid #d1d5db; font-size: 0.88rem; }
</style>
@endpush
