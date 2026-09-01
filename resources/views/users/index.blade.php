@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')

{{-- En-tête --}}
<div class="ug-header">
    <div>
        <h1 class="ug-title">Utilisateurs</h1>
        <p class="ug-subtitle">Comptes d'accès au système RH Plus</p>
    </div>
    <a href="{{ route('users.create') }}" class="ug-btn-new">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvel utilisateur
    </a>
</div>

{{-- Filtres --}}
<div class="ug-filters">
    <form method="GET" action="{{ route('users.index') }}" class="ug-filters-form">
        <div class="ug-filter-field">
            <label class="ug-filter-label">Centre</label>
            <select name="centre_id" class="ug-select" onchange="this.form.submit()">
                <option value="">Tous les centres</option>
                @foreach($centres as $centre)
                <option value="{{ $centre->id }}" {{ request('centre_id') == $centre->id ? 'selected' : '' }}>{{ $centre->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="ug-filter-field">
            <label class="ug-filter-label">Rôle</label>
            <select name="role" class="ug-select" onchange="this.form.submit()">
                <option value="">Tous les rôles</option>
                <option value="crh" {{ request('role') == 'crh' ? 'selected' : '' }}>Conseiller RH</option>
                <option value="ddis" {{ request('role') == 'ddis' ? 'selected' : '' }}>Directeur DDIS</option>
                <option value="ddrh" {{ request('role') == 'ddrh' ? 'selected' : '' }}>Directeur DRH</option>
                <option value="drh_centre" {{ request('role') == 'drh_centre' ? 'selected' : '' }}>DRH Centre</option>
                <option value="assistant_rh" {{ request('role') == 'assistant_rh' ? 'selected' : '' }}>Assistant RH</option>
                <option value="directeur_centre" {{ request('role') == 'directeur_centre' ? 'selected' : '' }}>Directeur Centre</option>
            </select>
        </div>
        @if(request()->hasAny(['centre_id','role']))
        <a href="{{ route('users.index') }}" class="ug-btn-reset" style="align-self:flex-end;">✕ Effacer</a>
        @endif
    </form>
</div>

{{-- Flash messages --}}
@if(session('success'))
<div class="ug-alert ug-alert-success">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="ug-alert ug-alert-error">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- Table card (desktop) --}}
<div class="ug-table-card">
    <div class="ug-table-wrap">
        <table class="ug-table">
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
                        <div class="ug-user-cell">
                            @if($user->photo_url)
                                <img src="{{ $user->photo_url }}" alt="{{ $user->nom_complet }}"
                                     onclick="zoomUserPhoto('{{ $user->photo_url }}', '{{ addslashes($user->nom_complet) }}', '{{ addslashes($user->role_label) }}', '{{ addslashes($user->centre?->nom ?? 'Direction Générale') }}', '')"
                                     style="width:38px; height:38px; border-radius:50%; object-fit:cover; flex-shrink:0; cursor:pointer; border:2px solid #e5e7eb; transition:transform 0.15s ease;"
                                     onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"
                                     title="Cliquer pour zoomer">
                            @else
                                <div class="ug-avatar ug-avatar-{{ $user->role }}"
                                     onclick="zoomUserPhoto('', '{{ addslashes($user->nom_complet) }}', '{{ addslashes($user->role_label) }}', '{{ addslashes($user->centre?->nom ?? 'Direction Générale') }}', '{{ strtoupper(substr($user->prenoms, 0, 1)) }}{{ strtoupper(substr($user->nom, 0, 1)) }}')"
                                     style="cursor:pointer; transition:transform 0.15s ease;"
                                     onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'"
                                     title="Cliquer pour zoomer">
                                    {{ strtoupper(substr($user->prenoms, 0, 1)) }}{{ strtoupper(substr($user->nom, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <span class="ug-user-name">{{ $user->prenoms }} {{ strtoupper($user->nom) }}</span>
                                <span class="ug-user-sub">{{ $user->sexe === 'M' ? '♂ Homme' : '♀ Femme' }}</span>
                            </div>
                        </div>

                    </td>
                    <td class="ug-email">{{ $user->email }}</td>
                    <td><span class="ug-role-badge ug-role-{{ $user->role }}">{{ $user->role_label }}</span></td>
                    <td>
                        @if($user->centre)
                            <span class="ug-centre-tag">{{ $user->centre->nom }}</span>
                        @else
                            <span class="ug-global-tag">— Global —</span>
                        @endif
                    </td>
                    <td class="ug-date">{{ $user->created_at?->format('d/m/Y') }}</td>
                    <td>
                        <div class="ug-actions">
                            @if(!$user->isGlobal())
                            <a href="{{ route('users.transferer', $user) }}" class="ug-action-btn ug-btn-accent" title="Passation / Mutation">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                            </a>
                            @endif
                            <a href="{{ route('users.edit', $user) }}" class="ug-action-btn ug-btn-edit" title="Modifier">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="ug-action-btn ug-btn-delete" title="Supprimer">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="ug-empty">
                        <div class="ug-empty-icon">👤</div>
                        <div>Aucun utilisateur trouvé</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cards mobile --}}
    <div class="ug-cards-mobile">
        @forelse($users as $user)
        <div class="ug-card-mobile">
            <div class="ug-card-mobile-top">
                <div class="ug-user-cell">
                    <div class="ug-avatar ug-avatar-{{ $user->role }}">
                        {{ strtoupper(substr($user->prenoms, 0, 1)) }}{{ strtoupper(substr($user->nom, 0, 1)) }}
                    </div>
                    <div>
                        <span class="ug-user-name">{{ $user->prenoms }} {{ strtoupper($user->nom) }}</span>
                        <span class="ug-user-sub">{{ $user->email }}</span>
                    </div>
                </div>
                <div class="ug-actions">
                    @if(!$user->isGlobal())
                    <a href="{{ route('users.transferer', $user) }}" class="ug-action-btn ug-btn-accent" title="Passation">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    </a>
                    @endif
                    <a href="{{ route('users.edit', $user) }}" class="ug-action-btn ug-btn-edit" title="Modifier">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="ug-action-btn ug-btn-delete">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="ug-card-mobile-footer">
                <span class="ug-role-badge ug-role-{{ $user->role }}">{{ $user->role_label }}</span>
                @if($user->centre)
                    <span class="ug-centre-tag">{{ $user->centre->nom }}</span>
                @else
                    <span class="ug-global-tag">Global</span>
                @endif
                <span class="ug-date">{{ $user->created_at?->format('d/m/Y') }}</span>
            </div>
        </div>
        @empty
        <div class="ug-empty"><div class="ug-empty-icon">👤</div><div>Aucun utilisateur trouvé</div></div>
        @endforelse
    </div>

    <div class="ug-pagination">
        {{ $users->withQueryString()->links('vendor.pagination.simple') }}
    </div>
</div>

@push('styles')
<style>
.ug-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem}
.ug-title{font-size:1.6rem;font-weight:700;color:#111827;margin:0 0 .2rem}
.ug-subtitle{font-size:.87rem;color:#6b7280;margin:0}
.ug-btn-new{display:inline-flex;align-items:center;gap:.4rem;background:linear-gradient(135deg,#1a5c45,#227055);color:#fff;font-weight:600;font-size:.88rem;padding:.6rem 1.2rem;border-radius:10px;text-decoration:none;box-shadow:0 4px 12px rgba(26,92,69,.2);white-space:nowrap;transition:transform .15s,box-shadow .15s}
.ug-btn-new:hover{transform:translateY(-1px);box-shadow:0 6px 16px rgba(26,92,69,.3)}
.ug-filters{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1rem;box-shadow:0 1px 4px rgba(0,0,0,.04)}
.ug-filters-form{display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end}
.ug-filter-field{display:flex;flex-direction:column;gap:.3rem;flex:1;min-width:160px}
.ug-filter-label{font-size:.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.4px}
.ug-select{padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:8px;font-size:.87rem;color:#374151;background:#fff;transition:border-color .15s;width:100%;box-sizing:border-box}
.ug-select:focus{outline:none;border-color:#1a5c45;box-shadow:0 0 0 3px rgba(26,92,69,.08)}
.ug-btn-reset{padding:.5rem .9rem;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;font-size:.82rem;font-weight:600;text-decoration:none;white-space:nowrap}
.ug-alert{display:flex;align-items:center;gap:.5rem;padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.87rem;font-weight:500}
.ug-alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.ug-alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.ug-table-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.04)}
.ug-table-wrap{overflow-x:auto}
.ug-table{width:100%;border-collapse:collapse;min-width:720px}
.ug-table thead tr{background:#f9fafb}
.ug-table th{padding:.85rem 1rem;text-align:left;font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;border-bottom:1.5px solid #e5e7eb;white-space:nowrap}
.ug-table td{padding:.9rem 1rem;border-bottom:1px solid #f3f4f6;vertical-align:middle}
.ug-table tbody tr:last-child td{border-bottom:none}
.ug-table tbody tr:hover td{background:#fafafa}
.ug-user-cell{display:flex;align-items:center;gap:.65rem}
.ug-avatar{width:36px;height:36px;border-radius:10px;flex-shrink:0;color:#fff;font-size:.72rem;font-weight:700;display:flex;align-items:center;justify-content:center}
.ug-avatar-crh{background:#1a5c45}
.ug-avatar-ddis{background:#6d28d9}
.ug-avatar-ddrh{background:#1d4ed8}
.ug-avatar-drh_centre,.ug-avatar-drh{background:#b45309}
.ug-avatar-assistant_rh{background:#374151}
.ug-avatar-directeur_centre{background:#be185d}
.ug-user-name{display:block;font-weight:600;font-size:.88rem;color:#111827}
.ug-user-sub{font-size:.72rem;color:#9ca3af}
.ug-email{font-size:.83rem;color:#6b7280}
.ug-date{font-size:.8rem;color:#9ca3af;white-space:nowrap}
.ug-role-badge{padding:3px 10px;border-radius:99px;font-size:.71rem;font-weight:700;white-space:nowrap}
.ug-role-crh{background:rgba(16,185,129,.12);color:#065f46}
.ug-role-ddis{background:rgba(139,92,246,.12);color:#5b21b6}
.ug-role-ddrh{background:rgba(59,130,246,.12);color:#1d4ed8}
.ug-role-drh_centre,.ug-role-drh{background:rgba(245,158,11,.12);color:#92400e}
.ug-role-assistant_rh{background:rgba(107,114,128,.12);color:#374151}
.ug-role-directeur_centre{background:rgba(236,72,153,.12);color:#9d174d}
.ug-centre-tag{font-size:.78rem;background:#f3f4f6;padding:3px 9px;border-radius:6px;color:#374151}
.ug-global-tag{font-size:.78rem;color:#9ca3af;font-style:italic}
.ug-actions{display:flex;align-items:center;gap:.4rem;justify-content:center}
.ug-action-btn{width:30px;height:30px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;border:1px solid transparent;cursor:pointer;text-decoration:none;transition:all .15s;background:none;padding:0}
.ug-btn-edit{color:#1d4ed8;border-color:#bfdbfe;background:#eff6ff}
.ug-btn-edit:hover{background:#dbeafe}
.ug-btn-accent{color:#1a5c45;border-color:#a7f3d0;background:#ecfdf5}
.ug-btn-accent:hover{background:#d1fae5}
.ug-btn-delete{color:#dc2626;border-color:#fecaca;background:#fef2f2}
.ug-btn-delete:hover{background:#fee2e2}
.ug-empty{text-align:center;padding:3rem;color:#9ca3af;font-size:.9rem}
.ug-empty-icon{font-size:2.5rem;margin-bottom:.5rem}
.ug-pagination{padding:1rem 1.25rem;border-top:1px solid #f3f4f6}
.ug-cards-mobile{display:none}
@media(max-width:768px){
    .ug-title{font-size:1.25rem}
    .ug-table-wrap{display:none}
    .ug-cards-mobile{display:block;padding:.75rem}
    .ug-card-mobile{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:1rem;margin-bottom:.75rem;box-shadow:0 1px 4px rgba(0,0,0,.04)}
    .ug-card-mobile-top{display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;margin-bottom:.75rem}
    .ug-card-mobile-footer{display:flex;flex-wrap:wrap;align-items:center;gap:.4rem;padding-top:.6rem;border-top:1px solid #f3f4f6}
    .ug-filters-form{flex-direction:column}
    .ug-filter-field{min-width:100%}
    .ug-header{flex-direction:column;align-items:stretch}
    .ug-btn-new{justify-content:center}
}
</style>
@endpush

{{-- Modale Lightbox Photo Utilisateur --}}
<dialog id="modalUserPhoto" style="border: none; border-radius: 16px; padding: 0; max-width: 520px; width: 90vw; background: rgba(17, 24, 39, 0.96); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px);">
    <div style="position: relative; padding: 1.5rem; text-align: center;">
        <button onclick="document.getElementById('modalUserPhoto').close()" style="position: absolute; top: 12px; right: 16px; background: rgba(255,255,255,0.2); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">&times;</button>
        <div id="uModalTitle" style="font-weight: 700; color: #fff; font-size: 1.15rem; margin-bottom: 1rem;">Photo de profil</div>
        <div id="uModalImgContainer">
            <img id="uModalImg" src="" alt="Photo" style="max-width: 100%; max-height: 65vh; border-radius: 12px; border: 3px solid rgba(255,255,255,0.3); box-shadow: 0 10px 30px rgba(0,0,0,0.5); object-fit: contain;">
            <div id="uModalAvatar" style="width: 140px; height: 140px; border-radius: 50%; background: #1a5c45; color: white; display: none; align-items: center; justify-content: center; font-size: 2.8rem; font-weight: 700; margin: 1.5rem auto; border: 4px solid rgba(255,255,255,0.3);"></div>
        </div>
        <div id="uModalMeta" style="margin-top: 1rem; font-size: 0.88rem; color: #9ca3af; font-weight: 600;"></div>
    </div>
</dialog>

@push('scripts')
<script>
function zoomUserPhoto(url, name, role, centre, initials) {
    document.getElementById('uModalTitle').textContent = 'Profil — ' + name;
    document.getElementById('uModalMeta').textContent = role + (centre ? ' — ' + centre : '');
    const img = document.getElementById('uModalImg');
    const av = document.getElementById('uModalAvatar');
    if (url && url.length > 0) {
        img.src = url;
        img.style.display = 'inline-block';
        av.style.display = 'none';
    } else {
        img.style.display = 'none';
        av.textContent = initials || 'RH';
        av.style.display = 'flex';
    }
    document.getElementById('modalUserPhoto').showModal();
}
</script>
@endpush
@endsection



