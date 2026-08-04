<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Alias de rôles pour regrouper des rôles similaires.
     */
    protected array $aliases = [
        'drh'      => ['drh', 'drh_centre', 'crh'],
        'approver' => ['crh', 'drh_centre', 'drh', 'directeur_centre'],
        'global'   => ['crh', 'ddis', 'ddrh'],
    ];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
            return redirect()->route('login');
        }

        // Résoudre les alias
        $allowedRoles = [];
        foreach ($roles as $role) {
            if (isset($this->aliases[$role])) {
                $allowedRoles = array_merge($allowedRoles, $this->aliases[$role]);
            } else {
                $allowedRoles[] = $role;
            }
        }

        if (!in_array($request->user()->role, $allowedRoles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }
            abort(403, 'Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
        }

        return $next($request);
    }
}
