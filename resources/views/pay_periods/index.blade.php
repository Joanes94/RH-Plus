@extends('layouts.app')

@section('title', 'Gestion des Périodes de Paie')

@section('content')
<div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title" style="font-size: 1.8rem; font-weight: 700; color: #111827; margin: 0 0 0.25rem 0;">Gestion des Périodes de Paie</h1>
        <p class="page-subtitle" style="font-size: 0.95rem; color: #6b7280; margin: 0;">
            @if($user->isGlobal())
                Supervision de l'ouverture et du calcul des paies pour tous les centres de santé.
            @else
                Périodes de paie et calcul des salaires — <strong>{{ $user->centre?->nom ?? 'Mon Centre' }}</strong>
            @endif
        </p>
    </div>
    @if(!auth()->user()->isReadOnly())
    <div>
        <button class="btn-premium" onclick="document.getElementById('modalNewPeriod').showModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 0.25rem;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Démarrer un mois de paie
        </button>
    </div>
    @endif
</div>

{{-- Filtre par centre si rôle global --}}
@if($user->isGlobal())
<div class="card premium-card" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
    <form method="GET" action="{{ route('pay-periods.index') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <label for="filter_centre" style="font-weight: 600; font-size: 0.95rem; color: #374151;">🏥 Filtrer par Centre :</label>
        <select name="centre_id" id="filter_centre" class="form-control" style="width: auto; min-width: 240px; padding: 7px 12px; font-size: 0.95rem;" onchange="this.form.submit()">
            <option value="">— Tous les centres —</option>
            @foreach($centres as $c)
                <option value="{{ $c->id }}" {{ request('centre_id') == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
            @endforeach
        </select>
        @if(request('centre_id'))
            <a href="{{ route('pay-periods.index') }}" class="action-btn-back" style="padding: 7px 12px; font-size: 0.85rem;">✕ Effacer le filtre</a>
        @endif
    </form>
    <div style="font-size: 0.88rem; color: #6b7280;">
        <strong>{{ $periods->count() }}</strong> période(s) trouvée(s)
    </div>
</div>
@endif

{{-- Tableau des périodes --}}
<div class="card premium-card" style="margin-top: 1.5rem;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; border-bottom: 1px solid #e5e7eb;">
        <h3 class="card-title" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #111827;">Mois de traitement salarial</h3>
    </div>
    
    <div class="table-responsive">
        <table class="table premium-table">
            <thead>
                <tr>
                    @if($user->isGlobal())
                        <th>Centre de santé</th>
                    @endif
                    <th>Mois (Code)</th>
                    <th>Désignation</th>
                    <th>Statut de la période</th>
                    <th>Créé par</th>
                    <th>Date d'ouverture</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periods as $period)
                <tr>
                    @if($user->isGlobal())
                        <td>
                            <strong style="color: #1a5c45; font-size: 0.95rem;">
                                {{ $period->centre?->nom ?? '— Non assigné —' }}
                            </strong>
                        </td>
                    @endif
                    <td><strong style="font-family: monospace; font-size: 0.95rem; color: #111827;">{{ $period->code }}</strong></td>
                    <td style="font-weight: 600; color: #374151;">{{ $period->label }}</td>
                    <td>
                        @if($period->statut === 'ouvert')
                            <span class="status-pill status-ouvert">Ouvert (Calculs modifiables)</span>
                        @else
                            <span class="status-pill status-cloture">Clôturé & Verrouillé</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight: 500; color: #4b5563;">{{ $period->createdBy?->nom_complet ?? 'Système' }}</div>
                    </td>
                    <td style="color: #6b7280; font-size: 0.85rem;">{{ $period->created_at->format('d/m/Y à H:i') }}</td>
                    <td class="text-right">
                        <a href="{{ route('pay-periods.show', $period->id) }}" class="action-btn-manage">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.2rem;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Gérer la paie
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $user->isGlobal() ? 7 : 6 }}" style="text-align: center; color: #9ca3af; padding: 4rem;">
                        <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">📅</div>
                        Aucune période de paie n'a encore été initialisée pour ce centre.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modale pour démarrer un nouveau mois --}}
<dialog id="modalNewPeriod" class="modal" style="border: none; border-radius: 20px; padding: 0; max-width: 500px; width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,0.2);">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: linear-gradient(135deg, #1a5c45 0%, #227055 100%); color: #fff;">
        <h2 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #fff;">Démarrer un nouveau mois de paie</h2>
        <button onclick="document.getElementById('modalNewPeriod').close()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: rgba(255,255,255,0.7); padding: 0;">&times;</button>
    </div>
    <form action="{{ route('pay-periods.store') }}" method="POST">
        @csrf
        <div class="modal-body" style="padding: 1.5rem;">
            @if($user->isGlobal())
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="centre_id" class="form-label">Centre de Santé *</label>
                    <select name="centre_id" id="centre_id" class="form-input" required>
                        <option value="">— Sélectionner le centre —</option>
                        @foreach($centres as $c)
                            <option value="{{ $c->id }}">{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label">Centre de Santé</label>
                    <input type="text" class="form-input" value="{{ $user->centre?->nom ?? 'Mon Centre' }}" disabled style="background:#f3f4f6; color:#4b5563; font-weight:600;">
                </div>
            @endif

            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="code" class="form-label">Code Période (AAAA-MM) *</label>
                <input type="text" name="code" id="code" class="form-input" placeholder="{{ date('Y-m') }}" required>
                <small style="color: #6b7280; font-size: 0.75rem; display: block; margin-top: 0.25rem;">Format : Année-Mois (ex. 2026-08 pour Août 2026).</small>
            </div>
            
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="label" class="form-label">Désignation du mois *</label>
                <input type="text" name="label" id="label" class="form-input" placeholder="Août 2026" required>
            </div>
        </div>
        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button type="button" class="action-btn-back" onclick="document.getElementById('modalNewPeriod').close()">Annuler</button>
            <button type="submit" class="action-btn-submit">Initialiser le mois</button>
        </div>
    </form>
</dialog>


@push('styles')
<style>
/* CSS Périodes de Paie Index Premium */
.premium-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    overflow: hidden;
}

.premium-table {
    width: 100%;
    border-collapse: collapse;
}

.premium-table th {
    background-color: #f9fafb;
    border-bottom: 1.5px solid #e5e7eb;
    color: #4b5563;
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 1rem 1.25rem;
}

.premium-table td {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.premium-table tr:hover td {
    background-color: #f9fafb;
}

/* Status pills */
.status-pill {
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 99px;
    font-weight: 600;
    display: inline-block;
    border: 1px solid transparent;
}
.status-ouvert {
    background: #ecfdf5;
    color: #047857;
    border-color: #a7f3d0;
}
.status-cloture {
    background: #f3f4f6;
    color: #4b5563;
    border-color: #d1d5db;
}

/* Buttons style */
.btn-premium {
    background: linear-gradient(135deg, #1a5c45 0%, #227055 100%);
    color: #ffffff;
    border: none;
    padding: 0.55rem 1.25rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s ease;
    box-shadow: 0 4px 10px rgba(26, 92, 69, 0.15);
}
.btn-premium:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(26, 92, 69, 0.25);
}

.action-btn-manage {
    padding: 6px 12px;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 8px;
    background-color: #ffffff;
    color: var(--col-primary, #1a5c45);
    border: 1px solid var(--col-primary, #1a5c45);
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: all 0.15s;
}
.action-btn-manage:hover {
    background-color: var(--col-primary, #1a5c45);
    color: #ffffff;
    transform: translateY(-1px);
}

/* Form Styles */
.form-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.35rem;
}
.form-input {
    width: 100%;
    padding: 0.55rem;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-size: 0.88rem;
    box-sizing: border-box;
    transition: border-color 0.15s;
    background-color: #fff;
}
.form-input:focus {
    border-color: var(--col-primary, #1a5c45);
    outline: none;
}

.action-btn-submit {
    background-color: var(--col-primary, #1a5c45);
    color: #fff;
    border: none;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.88rem;
    font-weight: 600;
}
.action-btn-submit:hover {
    background-color: #124030;
}
.action-btn-back {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.88rem;
    font-weight: 500;
}
</style>
@endpush

<script>
    // Autofill label based on code value change
    document.getElementById('code').addEventListener('input', function(e) {
        const value = e.target.value;
        const regex = /^(\d{4})-(\d{2})$/;
        const match = value.match(regex);
        if (match) {
            const year = match[1];
            const monthNum = match[2];
            const months = {
                '01': 'Janvier', '02': 'Février', '03': 'Mars', '04': 'Avril',
                '05': 'Mai', '06': 'Juin', '07': 'Juillet', '08': 'Août',
                '09': 'Septembre', '10': 'Octobre', '11': 'Novembre', '12': 'Décembre'
            };
            const monthName = months[monthNum];
            if (monthName) {
                document.getElementById('label').value = monthName + ' ' + year;
            }
        }
    });
</script>
@endsection
