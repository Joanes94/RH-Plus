<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCentreAccess
{
    /**
     * Vérifie que l'utilisateur a accès au centre de la ressource consultée.
     * Les rôles globaux (CRH, DDIS, DDRH) ont accès à tous les centres.
     * Les rôles locaux ne voient que les données de leur propre centre.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Les rôles globaux ont accès à tous les centres
        if ($user->isGlobal()) {
            return $next($request);
        }

        // Vérifier si la ressource a un centre_id
        $personnel = $request->route('personnel');
        if ($personnel && $personnel->centre_id && !$user->canManageCentre($personnel->centre_id)) {
            abort(403, 'Vous n\'avez pas accès aux données de ce centre.');
        }

        return $next($request);
    }
}
