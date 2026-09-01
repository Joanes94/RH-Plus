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
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
