<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TEKTON SIRH — Relier les talents. Construire l'avenir.</title>
    
    <!-- Polices Google Fonts Premium -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;0,9..144,800;1,9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1a5c45;
            --primary-hover: #124332;
            --primary-light: #ecfdf5;
            --primary-border: #a7f3d0;
            --gold: #b45309;
            --gold-light: #fef3c7;
            --gold-border: #fde68a;
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --text-title: #0f291e;
            --text-main: #334155;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 10px 30px rgba(26, 92, 69, 0.08);
            --shadow-lg: 0 20px 50px rgba(15, 41, 30, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* ── Fond Lumineux avec Motifs & Halos Doux ─────────────────── */
        .bg-canvas {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            pointer-events: none;
            z-index: -1;
            background: 
                radial-gradient(circle at 12% 18%, rgba(26, 92, 69, 0.07) 0%, transparent 45%),
                radial-gradient(circle at 88% 65%, rgba(217, 119, 6, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 50% 90%, rgba(5, 150, 105, 0.06) 0%, transparent 40%),
                #f8fafc;
        }

        .grid-overlay {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(0, 0, 0, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.02) 1px, transparent 1px);
            pointer-events: none;
            z-index: -1;
        }

        /* ── Barre de Navigation ─────────────────────────────────────── */
        .navbar {
            position: fixed;
            top: 0; left: 0; width: 100%;
            padding: 0.9rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-light);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .brand-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
        }

        .brand-logo-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(26, 92, 69, 0.15);
            border: 1px solid rgba(26, 92, 69, 0.2);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.25s ease;
        }

        .brand-container:hover .brand-logo-wrap {
            transform: scale(1.05);
            border-color: var(--primary);
        }

        .brand-logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand-text h1 {
            font-family: 'Fraunces', serif;
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-title);
            letter-spacing: -0.5px;
            margin: 0;
            line-height: 1.1;
        }

        .brand-text span {
            font-size: 0.72rem;
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2.2rem;
            list-style: none;
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .nav-btn-login {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            background: linear-gradient(135deg, var(--primary) 0%, #23785b 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 0.65rem 1.4rem;
            border-radius: 99px;
            font-weight: 700;
            font-size: 0.92rem;
            box-shadow: 0 4px 14px rgba(26, 92, 69, 0.25);
            transition: all 0.25s ease;
        }

        .nav-btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 92, 69, 0.35);
            background: linear-gradient(135deg, var(--primary-hover) 0%, #1a5c45 100%);
        }

        /* ── Hero Section (Thème Clair & Grand Visuel) ───────────────── */
        .hero {
            padding: 8.5rem 2rem 5rem;
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            align-items: center;
            gap: 3.5rem;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.45rem 1.1rem;
            background: var(--primary-light);
            border: 1px solid var(--primary-border);
            border-radius: 99px;
            color: var(--primary);
            font-size: 0.84rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .hero-tag .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
        }

        .hero-title {
            font-family: 'Fraunces', serif;
            font-size: clamp(2.4rem, 4.2vw, 3.5rem);
            font-weight: 800;
            line-height: 1.15;
            color: var(--text-title);
            margin-bottom: 1.4rem;
            letter-spacing: -1px;
        }

        .hero-title em {
            font-style: normal;
            background: linear-gradient(135deg, var(--primary) 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-description {
            font-size: 1.1rem;
            color: var(--text-main);
            line-height: 1.7;
            margin-bottom: 2.2rem;
            max-width: 560px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            flex-wrap: wrap;
        }

        .btn-cta-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--primary) 0%, #227055 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 0.95rem 2rem;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1.05rem;
            box-shadow: 0 8px 24px rgba(26, 92, 69, 0.28);
            transition: all 0.25s ease;
        }

        .btn-cta-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(26, 92, 69, 0.38);
            background: linear-gradient(135deg, var(--primary-hover) 0%, #1a5c45 100%);
            color: #fff;
        }

        .btn-cta-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #ffffff;
            color: var(--text-title);
            text-decoration: none;
            padding: 0.95rem 1.8rem;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1.02rem;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
            transition: all 0.25s ease;
        }

        .btn-cta-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        /* ── Visuel Hero Immersif ─────────────────────────────────────── */
        .hero-visual-wrapper {
            position: relative;
        }

        .hero-visual-card {
            position: relative;
            background: #ffffff;
            border-radius: 28px;
            padding: 12px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(26, 92, 69, 0.15);
            transition: transform 0.4s ease;
        }

        .hero-visual-card:hover {
            transform: translateY(-4px);
        }

        .hero-visual-img {
            width: 100%;
            height: 440px;
            object-fit: cover;
            border-radius: 20px;
            display: block;
        }

        /* Badges Flottants Clairs */
        .floating-badge {
            position: absolute;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(26, 92, 69, 0.15);
            padding: 0.75rem 1.2rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            z-index: 10;
            animation: floatSlow 4s ease-in-out infinite alternate;
        }

        .floating-badge-1 {
            top: -15px;
            left: -20px;
        }

        .floating-badge-2 {
            bottom: -20px;
            right: -15px;
            animation-delay: -2s;
        }

        .badge-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .badge-info strong {
            display: block;
            font-size: 0.92rem;
            color: var(--text-title);
        }

        .badge-info span {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        @keyframes floatSlow {
            0% { transform: translateY(0px); }
            100% { transform: translateY(-8px); }
        }

        /* ── Section Chiffres Clés ───────────────────────────────────── */
        .stats-strip {
            max-width: 1280px;
            margin: 0 auto 5rem;
            padding: 0 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 24px;
            padding: 2.2rem 2rem;
            box-shadow: var(--shadow-md);
        }

        .stat-item {
            text-align: center;
            padding: 0.5rem 1rem;
            border-right: 1px solid var(--border-light);
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-number {
            font-family: 'Fraunces', serif;
            font-size: 2.3rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.3rem;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* ── Section Modules & Fonctionnalités ───────────────────────── */
        .section-wrap {
            max-width: 1280px;
            margin: 0 auto;
            padding: 3rem 2rem 5rem;
        }

        .section-header {
            text-align: center;
            max-width: 720px;
            margin: 0 auto 3.5rem;
        }

        .section-tag {
            display: inline-block;
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 0.8rem;
            padding: 0.35rem 0.9rem;
            background: var(--primary-light);
            border-radius: 99px;
            border: 1px solid var(--primary-border);
        }

        .section-title {
            font-family: 'Fraunces', serif;
            font-size: clamp(2rem, 3.2vw, 2.6rem);
            font-weight: 800;
            color: var(--text-title);
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .section-desc {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 1.8rem;
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid var(--border-light);
            border-radius: 22px;
            padding: 2.2rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            box-shadow: var(--shadow-sm);
        }

        .feature-card:hover {
            transform: translateY(-6px);
            border-color: var(--primary-border);
            box-shadow: var(--shadow-md);
        }

        .feature-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: var(--primary-light);
            border: 1px solid var(--primary-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.4rem;
            color: var(--primary);
        }

        .feature-card h3 {
            font-family: 'Fraunces', serif;
            font-size: 1.28rem;
            font-weight: 700;
            color: var(--text-title);
            margin-bottom: 0.75rem;
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.65;
        }

        /* ── Section Immersion Médicale & Centres Sanitaires ─────────── */
        .institutions-banner {
            position: relative;
            max-width: 1280px;
            margin: 2rem auto 5rem;
            padding: 0 2rem;
        }

        .institutions-card-wrap {
            position: relative;
            border-radius: 32px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-light);
            background: #ffffff;
        }

        .institutions-bg-img {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
            opacity: 0.18;
            filter: saturate(1.2);
            z-index: 0;
        }

        .institutions-content {
            position: relative;
            z-index: 2;
            padding: 3.5rem 3rem 4.5rem;
            text-align: center;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.92) 0%, rgba(240, 253, 244, 0.95) 100%);
        }

        .institutions-illustration-wrap {
            max-width: 820px;
            margin: 0 auto 2.2rem;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(26, 92, 69, 0.16);
            border: 1px solid rgba(26, 92, 69, 0.18);
            background: #ffffff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .institutions-illustration-wrap:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 45px rgba(26, 92, 69, 0.22);
        }

        .institutions-illustration-img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
        }

        .centers-pills {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.1rem;
            max-width: 950px;
            margin: 2.5rem auto 0;
        }

        .center-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            background: #ffffff;
            border: 1px solid rgba(26, 92, 69, 0.18);
            padding: 0.85rem 1.5rem;
            border-radius: 99px;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-title);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
        }

        .center-pill:hover {
            border-color: var(--primary);
            background: var(--primary-light);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(26, 92, 69, 0.12);
        }



        /* ── CTA Final Lumineux avec Arrière-plan Stéthoscope ────────── */
        .cta-box {
            position: relative;
            border-radius: 28px;
            padding: 5rem 2.5rem;
            text-align: center;
            box-shadow: 0 20px 60px rgba(26, 92, 69, 0.25);
            color: #ffffff;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(15, 41, 30, 0.88) 0%, rgba(26, 92, 69, 0.84) 100%);
        }

        .cta-bg-img {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
            z-index: 0;
            opacity: 0.35;
            filter: contrast(1.1);
        }

        .cta-content {
            position: relative;
            z-index: 2;
            max-width: 720px;
            margin: 0 auto;
        }

        .cta-box h2 {
            font-family: 'Fraunces', serif;
            font-size: clamp(2.1rem, 3.8vw, 2.9rem);
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 1.1rem;
            letter-spacing: -0.5px;
        }

        .cta-box p {
            color: #e6fcf2;
            font-size: 1.15rem;
            max-width: 620px;
            margin: 0 auto 2.4rem;
            line-height: 1.65;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .btn-cta-white {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: #ffffff;
            color: var(--primary);
            text-decoration: none;
            padding: 1.05rem 2.4rem;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1.08rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.20);
            transition: all 0.25s ease;
        }

        .btn-cta-white:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.28);
            background: #f0fdf4;
            color: var(--primary-hover);
        }

        /* ── Footer Sobre & Institutionnel ───────────────────────────── */
        .footer {
            border-top: 1px solid var(--border-light);
            padding: 3.5rem 2rem 2.5rem;
            background: #ffffff;
            text-align: center;
            margin-top: 2rem;
        }

        .footer-logo {
            display: inline-flex;
            align-items: center;
            gap: 0.85rem;
            margin-bottom: 1.2rem;
        }

        .footer-logo img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--border-light);
        }

        .footer-logo span {
            font-family: 'Fraunces', serif;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-title);
        }

        .footer-text {
            color: var(--text-muted);
            font-size: 0.92rem;
            max-width: 540px;
            margin: 0 auto 1.6rem;
            line-height: 1.6;
        }

        .footer-copy {
            color: #94a3b8;
            font-size: 0.84rem;
        }

        /* Responsive */
        @media (max-width: 960px) {
            .hero {
                grid-template-columns: 1fr;
                padding: 7rem 1.5rem 3.5rem;
                text-align: center;
            }
            .hero-description { margin-left: auto; margin-right: auto; }
            .hero-actions { justify-content: center; }
            .hero-visual-card { max-width: 560px; margin: 0 auto; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 2rem; }
            .stat-item { border-right: none; }
            .floating-badge { display: none; }
        }

        @media (max-width: 640px) {
            .navbar { padding: 0.8rem 1.2rem; }
            .nav-links { display: none; }
            .stats-grid { grid-template-columns: 1fr; }
            .centers-pills { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>

    <div class="bg-canvas"></div>
    <div class="grid-overlay"></div>

    <!-- Navigation Header -->
    <nav class="navbar">
        <a href="{{ route('home') }}" class="brand-container">
            <div class="brand-logo-wrap">
                <img src="{{ asset('images/logo_tekton.jpg') }}" alt="Logo TEKTON SIRH">
            </div>
            <div class="brand-text">
                <h1>TEKTON SIRH</h1>
                <span>Plateforme RH Diocésaine</span>
            </div>
        </a>

        <ul class="nav-links">
            <li><a href="#features" class="nav-link">Fonctionnalités</a></li>
            <li><a href="#institutions" class="nav-link">Formations Sanitaires</a></li>
            <li><a href="#securite" class="nav-link">Sécurité & Rôles</a></li>
        </ul>

        <div>
            @auth
                <a href="{{ route('dashboard') }}" class="nav-btn-login">
                    <span>Tableau de bord</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            @else
                <a href="{{ route('login') }}" class="nav-btn-login">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    <span>Espace Connexion</span>
                </a>
            @endauth
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-tag">
                <div class="dot"></div>
                <span>Système d'Information des Ressources Humaines</span>
            </div>
            <h1 class="hero-title">
                Relier les talents.<br>
                <em>Construire l'avenir</em> des soins.
            </h1>
            <p class="hero-description">
                La solution complète de gestion des carrières, paie, contrats et actes administratifs conçue pour la <strong>Direction Diocésaine des Institutions Sanitaires (DDIS)</strong> et l'ensemble de ses centres de santé.
            </p>
            <div class="hero-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-cta-primary">
                        <span>Ouvrir mon Espace RH</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-cta-primary">
                        <span>Se Connecter à TEKTON</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    </a>
                @endauth
                <a href="#features" class="btn-cta-secondary">
                    <span>Explorer les modules</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M7 13l5 5 5-5M7 6l5 5 5-5"/></svg>
                </a>
            </div>
        </div>

        <div class="hero-visual-wrapper">
            <div class="hero-visual-card">
                <img src="{{ asset('images/landing/hero_team.jpg') }}" alt="Équipe RH TEKTON" class="hero-visual-img">
                
                <!-- Badge Flottant 1 -->
                <div class="floating-badge floating-badge-1">
                    <div class="badge-icon">🏛️</div>
                    <div class="badge-info">
                        <strong>Conformité Bénin</strong>
                        <span>Code du travail & Convention</span>
                    </div>
                </div>

                <!-- Badge Flottant 2 -->
                <div class="floating-badge floating-badge-2">
                    <div class="badge-icon">✨</div>
                    <div class="badge-info">
                        <strong>Paie & Carrières</strong>
                        <span>Calculs, ITS & CNSS automatisés</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bandeau Chiffres Clés -->
    <div class="stats-strip">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">6+</div>
                <div class="stat-label">Hôpitaux & Centres Sanitaires</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div class="stat-label">Conformité Légale & Barèmes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">0 Papier</div>
                <div class="stat-label">Processus RH Dématérialisés</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Disponibilité Sécurisée Multi-Rôles</div>
            </div>
        </div>
    </div>

    <!-- Section Modules & Fonctionnalités -->
    <section id="features" class="section-wrap">
        <div class="section-header">
            <span class="section-tag">Piliers & Capacités</span>
            <h2 class="section-title">Une suite RH complète au service des soignants</h2>
            <p class="section-desc">TEKTON centralise chaque étape de la vie professionnelle des agents avec rigueur, transparence et traçabilité.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon-wrap">👥</div>
                <h3>Dossier Collaborateur & Stagiaires</h3>
                <p>Gestion 360° du personnel soignant et administratif, suivi rigoureux des périodes d'essai, des conventions de stage avec notification automatique de fin de cycle.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrap">💳</div>
                <h3>Calcul de Paie & Traitements</h3>
                <p>Moteur de paie automatisé conforme à la législation béninoise (ITS, CNSS, acomptes, cotisations, primes de garde), ouvertures de mois étanches par centre et bulletins officiels.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrap">📈</div>
                <h3>Avancements & Carrières</h3>
                <p>Gestion des échelons, avancements d'ancienneté, bonifications Article 88 et génération automatique des décisions d'avancement signées.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrap">🏥</div>
                <h3>Gestion Locale Autonome & Supervision Centrale </h3>
                <p>Chaque centre gère son personel tout en garantissant à la DDIS et à la DDRH une supervision globale consolidée en temps réel.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrap">✍️</div>
                <h3>Actes & Signatures Numérisés</h3>
                <p>Édition automatisée des attestations de travail, titre de congé, contrats de travail et autres documents administratifs avec signatures adaptées.</p>
            </div>

            <div class="feature-card" id="securite">
                <div class="feature-icon-wrap">🛡️</div>
                <h3>Contrôle d'Accès & Sécurité des Informations</h3>
                <p>Authentification sécurisée, traçabilité des opérations et droits d'accès stricts.</p>
            </div>
        </div>
    </section>



    <!-- Section Immersion Médicale & Centres Rattachés -->
    <section id="institutions" class="institutions-banner">
        <div class="institutions-card-wrap">
            <img src="{{ asset('images/landing/hero_medical.jpg') }}" alt="Équipe Médicale" class="institutions-bg-img">
            
            <div class="institutions-content">
                <div class="institutions-illustration-wrap">
                    <img src="{{ asset('images/landing/centre_sante_catholique.jpg') }}" alt="Hôpital et Centre de Santé Catholique Diocésain" class="institutions-illustration-img">
                </div>
                <span class="section-tag">Réseau Sanitaire Diocésain</span>

                <h2 class="section-title">Les Formations Sanitaires Rattachées</h2>
                <p class="section-desc">
                    TEKTON SIRH unifie les hôpitaux et centres de santé sous la tutelle de la <strong>Direction Diocésaine de la Santé (DDIS)</strong>.
                </p>

                <div class="centers-pills">
                    <div class="center-pill">🏥 CSVH Saint Luc</div>
                    <div class="center-pill">🏥 CSVH Saint Jean (Cotonou)</div>
                    <div class="center-pill">🏥 CSVH Saint Joseph (Sô-Tchanhoué)</div>
                    <div class="center-pill">🏥 CSVH Sêyon</div>
                    <div class="center-pill">🏥 CSVH Padre Pio de Glo </div>
                    <div class="center-pill">🏥 CSVH Saint Jean de Maria-Gléta</div>
                    <div class="center-pill">🏥 CAFSC </div>
                    <div class="center-pill">🏥 DDIS </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section CTA Clôture avec Fond Stéthoscope -->
    <section class="section-wrap" style="padding-top: 1rem;">
        <div class="cta-box">
            <img src="{{ asset('images/landing/stethoscope_cta.jpg') }}" alt="Espace de travail" class="cta-bg-img">
            
            <div class="cta-content">
                <h2>Prêt à accéder à votre espace de travail ?</h2>
                <p>Connectez-vous pour piloter vos effectifs, vos contrats et vos paies en toute simplicité.</p>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-cta-white">
                        <span>Accéder à mon Tableau de Bord</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-cta-white">
                        <span>Se Connecter Maintenant</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Footer Institutionnel Clair -->

    <footer class="footer">
        <div class="footer-logo">
            <img src="{{ asset('images/logo_tekton.jpg') }}" alt="TEKTON SIRH">
            <span>TEKTON SIRH</span>
        </div>
        <p class="footer-text">
            Direction Diocésaine de la Santé (DDIS).<br>
            <strong>Relier les talents. Construire l'avenir.</strong>
        </p>
        <div class="footer-copy">
            &copy; {{ date('Y') }} TEKTON SIRH. Tous droits réservés.
        </div>
    </footer>

</body>
</html>