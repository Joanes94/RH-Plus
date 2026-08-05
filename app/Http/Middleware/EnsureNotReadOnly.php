<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotReadOnly
{
    /**
     * Bloque les modifications pour les utilisateurs en lecture seule.
     * Le DDRH a un accès global mais uniquement en lecture.
     * Le Directeur a un accès local en lecture seule (sauf dans les centres sans DRH).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // DDRH et DDIS sont en mode lecture seule globale (sauf approbation des bonifications Art 88 pour DDIS)
        if ($user->isDDRH() || $user->isDDIS()) {
            if ($user->isDDIS() && ($request->routeIs('avancements.approuver') || $request->routeIs('avancements.rejeter'))) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès en lecture seule pour DDRH / DDIS.'], 403);
            }
            abort(403, 'Votre rôle (DDIS / DDRH) est en mode lecture seule. Vous ne pouvez pas effectuer de modifications.');
        }

        // Le Directeur est en lecture seule SAUF dans les centres sans DRH dédié
        // (où il fait office de DRH)
        if ($user->isDirecteurCentre()) {
            $centre = $user->centre;
            if ($centre && $centre->a_drh_dedie) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Accès en lecture seule.'], 403);
                }
                abort(403, 'Ce centre dispose d\'un DRH dédié. Votre accès est en lecture seule.');
            }
        }

        return $next($request);
    }
}
