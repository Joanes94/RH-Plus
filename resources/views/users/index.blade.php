@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-heading">Utilisateurs</h1>
        <p class="page-subtitle">Comptes d'accès au système RH Plus</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvel utilisateur
    </a>
</div>

{{-- Filtres --}}
<div class="filters-bar">
    <form method="GET" action="{{ route('users.index') }}" class="filters-form">
        <div class="filter-group">
            <select name="centre_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous les centres</option>
                @foreach($centres as $centre)
                <option value="{{ $centre->id }}" {{ request('centre_id') == $centre->id ? 'selected' : '' }}>{{ $centre->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <select name="role" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous les rôles</option>
                <option value="crh" {{ request('role') == 'crh' ? 'selected' : '' }}>Conseiller RH</option>
                <option value="ddis" {{ request('role') == 'ddis' ? 'selected' : '' }}>Directeur DDIS</option>
                <option value="ddrh" {{ request('role') == 'ddrh' ? 'selected' : '' }}>Directeur DRH</option>
                <option value="drh_centre" {{ request('role') == 'drh_centre' ? 'selected' : '' }}>DRH Centre</option>
                <option value="assistant_rh" {{ request('role') == 'assistant_rh' ? 'selected' : '' }}>Assistant RH</option>
                <option value="directeur_centre" {{ request('role') == 'directeur_centre' ? 'selected' : '' }}>Directeur Centre</option>
            </select>
        </div>
    </form>
</div>

<div class="card card-table">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Centre</th>
                        <th>Créé le</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar-sm">{{ strtoupper(substr($user->prenoms, 0, 1)) }}{{ strtoupper(substr($user->nom, 0, 1)) }}</div>
                                <div>
                                    <span class="user-name-sm">{{ $user->prenoms }} {{ strtoupper($user->nom) }}</span>
                                    <span class="user-sexe">{{ $user->sexe === 'M' ? 'Homme' : 'Femme' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            <span class="role-badge role-{{ $user->role }}">{{ $user->role_label }}</span>
                        </td>
                        <td>
                            @if($user->centre)
                                <span class="centre-tag">{{ $user->centre->nom }}</span>
                            @else
                                <span class="text-muted">— Global —</span>
                            @endif
                        </td>
                        <td class="text-muted text-sm">{{ $user->created_at?->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <div class="action-btns">
                                @if(!$user->isGlobal())
                                <a href="{{ route('users.transferer', $user) }}" class="btn-icon btn-icon-accent" title="Passation de service / Mutation">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                </a>
                                @endif
                                <a href="{{ route('users.edit', $user) }}" class="btn-icon" title="Modifier">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger" title="Supprimer">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding: 2rem;">Aucun utilisateur trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 1.25rem;">
            {{ $users->withQueryString()->links('vendor.pagination.simple') }}
        </div>
    </div>
</div>

@push('styles')
<style>
.filters-bar { margin-bottom: 1rem; }
.filters-form { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.filter-group select { min-width: 200px; }

.user-cell { display: flex; align-items: center; gap: 0.75rem; }
.user-avatar-sm { width: 34px; height: 34px; border-radius: 10px; background: var(--col-primary, #1a5c45); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; flex-shrink: 0; }
.user-name-sm { display: block; font-weight: 500; font-size: 0.85rem; color: #111827; }
.user-sexe { font-size: 0.72rem; color: #9ca3af; }

.role-badge { padding: 3px 10px; border-radius: 99px; font-size: 0.72rem; font-weight: 600; }
.role-crh { background: rgba(16,185,129,0.1); color: #047857; }
.role-ddis { background: rgba(139,92,246,0.1); color: #6d28d9; }
.role-ddrh { background: rgba(59,130,246,0.1); color: #1d4ed8; }
.role-drh_centre { background: rgba(245,158,11,0.1); color: #b45309; }
.role-assistant_rh { background: rgba(107,114,128,0.1); color: #374151; }
.role-directeur_centre { background: rgba(236,72,153,0.1); color: #be185d; }
.role-drh { background: rgba(245,158,11,0.1); color: #b45309; }

.centre-tag { font-size: 0.78rem; background: #f3f4f6; padding: 2px 8px; border-radius: 6px; color: #374151; }

.action-btns { display: flex; gap: 0.4rem; justify-content: center; }
.btn-icon-danger { color: #ef4444 !important; }
.btn-icon-danger:hover { background: rgba(239,68,68,0.1) !important; }
</style>
@endpush
@endsection
