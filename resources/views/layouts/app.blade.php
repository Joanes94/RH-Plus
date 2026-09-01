<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RH Plus') — RH Plus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Fraunces:ital,wght@0,300;0,600;0,700;1,300;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand" style="display: flex; align-items: center; gap: 10px;">
                <img src="{{ asset('images/logo_tekton.jpg') }}" alt="TEKTON SIRH Logo" style="height: 38px; width: auto; border-radius: 6px; object-fit: contain; background: #fff; padding: 2px;">
                <span class="brand-name" style="font-size: 1.15rem; font-weight: 700;">TEKTON <em style="color: #d97706; font-style: normal; font-weight: 600;">SIRH</em></span>
            </div>
            <button class="sidebar-close" id="sidebarClose">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="sidebar-user" style="cursor: pointer;" onclick="openSidebarPhoto()" title="Cliquer pour agrandir la photo">
            @if(auth()->user()->photo_url)
                <img src="{{ auth()->user()->photo_url }}" alt="{{ auth()->user()->nom_complet }}" class="user-avatar-img" style="width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.4); flex-shrink: 0; transition: transform 0.2s ease;">
            @else
                <div class="user-avatar" style="transition: transform 0.2s ease;">{{ strtoupper(substr(auth()->user()->prenoms, 0, 1)) }}{{ strtoupper(substr(auth()->user()->nom, 0, 1)) }}</div>
            @endif
            <div class="user-info">
                <span class="user-name">{{ auth()->user()->prenoms }} {{ strtoupper(auth()->user()->nom) }}</span>
                <span class="user-role">{{ auth()->user()->role_label }}</span>
                @if(auth()->user()->centre)
                <span class="user-centre">{{ auth()->user()->centre->nom }}</span>
                @endif
            </div>
        </div>



        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-label">Principal</span>

                @if(auth()->user()->isGlobal())
                {{-- Rôles globaux : CRH, DDIS, DDRH --}}
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.centre') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Tableau de bord général
                </a>
                <a href="{{ route('dashboard.centre') }}" class="nav-item {{ request()->routeIs('dashboard.centre') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Tableau de bord par centre
                </a>
                <a href="{{ route('avancements.index') }}" class="nav-item {{ request()->routeIs('avancements.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
                    Validation Avancements
                    @php
                        $nbAvancementsSoumis = \App\Models\Avancement::where('statut', 'soumis')->count();
                    @endphp
                    @if($nbAvancementsSoumis > 0)
                        <span class="nav-badge">{{ $nbAvancementsSoumis }}</span>
                    @endif
                </a>
                @elseif(auth()->user()->isDRH() || (auth()->user()->isDirecteurCentre() && !auth()->user()->centre?->a_drh_dedie))
                {{-- DRH centre ou Directeur faisant office de DRH --}}
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Tableau de bord
                </a>
                @else
                {{-- Assistant RH ou Directeur en lecture seule --}}
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Tableau de bord
                </a>
                @endif
            </div>

            <div class="nav-section">
                <span class="nav-label">RH</span>

                <a href="{{ route('personnel.index') }}" class="nav-item {{ request()->routeIs('personnel.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Personnel
                </a>

                <a href="{{ route('stagiaires.index') }}" class="nav-item {{ request()->routeIs('stagiaires.index') || request()->routeIs('stagiaires.create') || request()->routeIs('stagiaires.show') || request()->routeIs('stagiaires.edit') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    Stagiaires
                </a>

                @php
                    $userCentreId = auth()->user()->centre_id;
                    $isGlobalUser = auth()->user()->isGlobal();
                    $nbCongesSoumis        = \App\Models\Conge::where('statut','soumis')
                        ->when(!$isGlobalUser && $userCentreId, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $userCentreId)))
                        ->count();
                    $nbAbsencesSoumis      = \App\Models\Absence::where('statut','soumis')
                        ->when(!$isGlobalUser && $userCentreId, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $userCentreId)))
                        ->count();
                    $nbDemandesSoumis      = \App\Models\Demande::where('statut','soumis')
                        ->when(!$isGlobalUser && $userCentreId, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $userCentreId)))
                        ->count();
                    $nbDemandesStagSoumis  = \App\Models\StagiaireDocument::where('statut','soumis')->count()
                                            + \App\Models\Evaluation::where('statut','soumis')->count();
                @endphp

                <a href="{{ route('stagiaires.demandes.index') }}" class="nav-item {{ request()->routeIs('stagiaires.demandes.*') || request()->routeIs('evaluations.*') || request()->routeIs('stagiaires.documents.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Demandes stagiaires
                    @if($nbDemandesStagSoumis > 0 && auth()->user()->canApprove())
                        <span class="nav-badge">{{ $nbDemandesStagSoumis }}</span>
                    @endif
                </a>

                <a href="{{ route('conges.index') }}" class="nav-item {{ request()->routeIs('conges.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Congés
                    @if($nbCongesSoumis > 0 && auth()->user()->canApprove())
                        <span class="nav-badge">{{ $nbCongesSoumis }}</span>
                    @endif
                </a>

                <a href="{{ route('absences.index') }}" class="nav-item {{ request()->routeIs('absences.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Absences
                    @if($nbAbsencesSoumis > 0 && auth()->user()->canApprove())
                        <span class="nav-badge">{{ $nbAbsencesSoumis }}</span>
                    @endif
                </a>

                <a href="{{ route('demandes.index') }}" class="nav-item {{ request()->routeIs('demandes.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Demandes
                    @if($nbDemandesSoumis > 0 && auth()->user()->canApprove())
                        <span class="nav-badge">{{ $nbDemandesSoumis }}</span>
                    @endif
                </a>

                <a href="{{ route('rapports.personnel') }}" class="nav-item {{ request()->routeIs('rapports.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    Rapports
                </a>
            </div>

            @if(auth()->user()->canApprove() || auth()->user()->isDDIS())
            <div class="nav-section">
                <span class="nav-label">{{ auth()->user()->isGlobal() ? 'Administration' : 'Direction' }}</span>
                <a href="{{ route('drh.historique') }}" class="nav-item {{ request()->routeIs('drh.historique') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                    Mon historique
                </a>

                @if(auth()->user()->isDDIS())
                <a href="{{ route('config-ddis.index') }}" class="nav-item {{ request()->routeIs('config-ddis.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                    Configuration DDIS
                </a>
                @else
                <a href="{{ route('config-rh.index') }}" class="nav-item {{ request()->routeIs('config-rh.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                    Configuration RH
                </a>
                @endif
            </div>
            @endif

            <div class="nav-section">
                <span class="nav-label">Paie & Rémunérations</span>
                <a href="{{ route('pay-periods.index') }}" class="nav-item {{ request()->routeIs('pay-periods.*') || request()->routeIs('pay-slips.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="12" y1="4" x2="12" y2="20"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
                    Calcul de la Paie
                </a>
                <a href="{{ route('pay-adjustments.index') }}" class="nav-item {{ request()->routeIs('pay-adjustments.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Avances & Ajustements
                </a>
            </div>

            @if(auth()->user()->isCRH())
            <div class="nav-section">
                <span class="nav-label">Gestion CRH</span>
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                    Gestion utilisateurs
                </a>
            </div>

            <div class="nav-section nav-section-centres">
                <div class="nav-section-header">
                    <span class="nav-label">Centres de Santé</span>
                    @if(isset($sidebarCentres) && $sidebarCentres->count() > 0)
                        <span class="nav-section-badge">{{ $sidebarCentres->count() }}</span>
                    @endif
                </div>

                <a href="{{ route('centres.index') }}" class="nav-item nav-item-main {{ request()->routeIs('centres.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>Gérer les centres</span>
                </a>

                @if(isset($sidebarCentres) && $sidebarCentres->count() > 0)
                <div class="sidebar-sublist">
                    @foreach($sidebarCentres as $sc)
                        @php
                            $isCurrentCentre = request()->routeIs('dashboard.centre') && request()->get('centre_id') == $sc->id;
                        @endphp
                        <a href="{{ route('dashboard.centre', ['centre_id' => $sc->id]) }}" 
                           class="sidebar-subitem {{ $isCurrentCentre ? 'active' : '' }} {{ !$sc->actif ? 'subitem-inactif' : '' }}"
                           title="{{ $sc->nom }} ({{ $sc->effectif_actif }} agents)">
                            <span class="subitem-dot" style="background: {{ $sc->actif ? ['#10b981','#3b82f6','#8b5cf6','#f59e0b','#ec4899','#06b6d4'][$loop->index % 6] : '#9ca3af' }}"></span>
                            <span class="subitem-name">{{ $sc->nom }}</span>
                            <span class="subitem-count">{{ $sc->effectif_actif }}</span>
                        </a>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <div class="nav-section">
                <span class="nav-label">Mon compte</span>
                <a href="{{ route('profile.show') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Mon profil
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="topbar-title">@yield('page-title', 'Tableau de bord')</div>
            <div class="topbar-right">
                @auth
                    @include('partials.notifications_bell')
                @endauth
                <div class="topbar-badge">{{ auth()->user()->role_label }}</div>
            </div>
        </header>

        <div class="page-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <dialog id="modalSidebarPhoto" style="border: none; border-radius: 16px; padding: 0; max-width: 520px; width: 90vw; background: rgba(17, 24, 39, 0.96); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6); backdrop-filter: blur(8px);">
        <div style="position: relative; padding: 1.5rem; text-align: center;">
            <button onclick="document.getElementById('modalSidebarPhoto').close()" style="position: absolute; top: 12px; right: 16px; background: rgba(255,255,255,0.2); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;">&times;</button>
            <div style="font-weight: 700; color: #fff; font-size: 1.15rem; margin-bottom: 1rem;">Photo de profil — {{ auth()->user()->nom_complet }}</div>
            @if(auth()->user()->photo_url)
                <img src="{{ auth()->user()->photo_url }}" alt="Photo" style="max-width: 100%; max-height: 65vh; border-radius: 12px; border: 3px solid rgba(255,255,255,0.3); box-shadow: 0 10px 30px rgba(0,0,0,0.5); object-fit: contain;">
            @else
                <div style="width: 150px; height: 150px; border-radius: 50%; background: #1a5c45; color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700; margin: 1.5rem auto; border: 4px solid rgba(255,255,255,0.3);">
                    {{ strtoupper(substr(auth()->user()->prenoms, 0, 1)) }}{{ strtoupper(substr(auth()->user()->nom, 0, 1)) }}
                </div>
            @endif
            <div style="margin-top: 1rem; font-size: 0.88rem; color: #9ca3af; font-weight: 600;">{{ auth()->user()->role_label }} — {{ auth()->user()->centre?->nom ?? 'Direction Générale' }}</div>
        </div>
    </dialog>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
    function openSidebarPhoto() {
        const m = document.getElementById('modalSidebarPhoto');
        if (m) m.showModal();
    }
    </script>
    @stack('scripts')
</body>
</html>