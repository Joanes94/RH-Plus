<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Connexion') — RH Plus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Fraunces:ital,wght@0,300;0,600;0,700;1,300;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .auth-split {
            position: relative;
            background: #0d231b;
        }

        /* Arrière-plan global stéthoscope médical haute définition */
        .auth-split::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url("{{ asset('images/landing/stethoscope_auth.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.18;
            filter: saturate(1.2);
            pointer-events: none;
            z-index: 1;
        }

        /* Panneau gauche avec image stéthoscope en fond et dégradé émeraude/nuit profond */
        .auth-panel-left {
            position: relative;
            background: linear-gradient(145deg, rgba(13, 35, 27, 0.94) 0%, rgba(26, 92, 69, 0.88) 50%, rgba(15, 23, 42, 0.92) 100%),
                        url("{{ asset('images/landing/stethoscope_auth.jpg') }}") center/cover no-repeat;
            border-right: 1px solid rgba(167, 243, 208, 0.15);
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.25);
            z-index: 2;
        }

        .auth-panel-inner {
            background: radial-gradient(circle at 85% 15%, rgba(5, 150, 105, 0.25) 0%, transparent 60%);
        }

        .auth-tagline h2 {
            font-family: 'Fraunces', serif;
            font-size: 1.85rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .auth-tagline p {
            color: #d1fae5;
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .auth-feature {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.2s ease;
        }

        .auth-feature:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(167, 243, 208, 0.35);
            transform: translateX(4px);
        }

        .auth-feature span {
            color: #f0fdf4 !important;
            font-weight: 500;
        }

        .feat-dot {
            background: #34d399 !important;
            box-shadow: 0 0 10px rgba(52, 211, 153, 0.6);
        }

        /* Panneau droit (formulaire) avec effet glassmorphism épuré */
        .auth-panel-right {
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.96) 0%, rgba(241, 245, 249, 0.94) 100%);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .auth-form-container {
            background: #ffffff;
            padding: 38px 36px;
            border-radius: 24px;
            box-shadow: 0 16px 40px rgba(15, 41, 30, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(226, 232, 240, 0.9);
        }

        .auth-form-header h1 {
            font-family: 'Fraunces', serif;
            color: #0f291e;
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .btn-primary.btn-full {
            background: #1a5c45;
            border-color: #1a5c45;
            box-shadow: 0 4px 14px rgba(26, 92, 69, 0.3);
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 700;
            transition: all 0.25s ease;
        }

        .btn-primary.btn-full:hover {
            background: #124332;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 92, 69, 0.4);
        }

        /* Mobile : fond stéthoscope conservé avec belle lisibilité */
        @media (max-width: 900px) {
            .auth-panel-left {
                display: none;
            }
            .auth-panel-right {
                background: linear-gradient(135deg, rgba(13, 35, 27, 0.90) 0%, rgba(26, 92, 69, 0.86) 100%),
                            url("{{ asset('images/landing/stethoscope_auth.jpg') }}") center/cover no-repeat;
                padding: 24px 16px;
            }
            .auth-form-container {
                backdrop-filter: blur(16px);
                background: rgba(255, 255, 255, 0.97);
            }
        }
    </style>
</head>
<body class="auth-body">

    <div class="auth-split">
        {{-- Panneau gauche décoratif --}}
        <div class="auth-panel-left">
            <div class="auth-panel-inner">
                <div class="auth-brand" style="display: flex; align-items: center; gap: 12px;">
                    <img src="{{ asset('images/logo_tekton.jpg') }}" alt="TEKTON SIRH Logo" style="height: 52px; width: auto; border-radius: 8px; background: #fff; padding: 3px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <span style="font-size: 1.5rem; font-weight: 800; color: #fff;">TEKTON <em style="color: #f59e0b; font-style: normal;">SIRH</em></span>
                </div>

                <div class="auth-tagline">
                    <h2>Gérez vos ressources humaines avec précision.</h2>
                    <p>Une plateforme conçue pour les équipes RH modernes — intuitive, sécurisée, efficace.</p>
                </div>
                <div class="auth-features">
                    <div class="auth-feature">
                        <div class="feat-dot"></div>
                        <span>Gestion centralisée du personnel</span>
                    </div>
                    <div class="auth-feature">
                        <div class="feat-dot"></div>
                        <span>Suivi des congés et absences</span>
                    </div>
                    <div class="auth-feature">
                        <div class="feat-dot"></div>
                        <span>Tableaux de bord analytiques</span>
                    </div>
                    <div class="auth-feature">
                        <div class="feat-dot"></div>
                        <span>Accès sécurisé par rôle</span>
                    </div>
                </div>
                <div class="auth-deco-shapes">
                    <div class="deco-circle deco-c1"></div>
                    <div class="deco-circle deco-c2"></div>
                    <div class="deco-rect"></div>
                </div>
            </div>
        </div>

        {{-- Panneau droit formulaire --}}
        <div class="auth-panel-right">
            <div class="auth-form-container">
                <div style="margin-bottom: 1.5rem;">
                    <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #64748b; text-decoration: none; font-weight: 600; transition: color 0.15s ease;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        <span>Retour à l'accueil</span>
                    </a>
                </div>
                @yield('content')
            </div>
        </div>

    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
