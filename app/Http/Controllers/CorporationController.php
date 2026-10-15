<?php

namespace App\Http\Controllers;

use App\Models\Corporation;
use App\Models\Centre;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CorporationController extends Controller
{
    /**
     * Liste des corporations accessibles pour le centre de l'utilisateur.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isGlobal = $user->isGlobal();

        // Déterminer le centre actif
        if ($isGlobal) {
            $centreId = $request->filled('centre_id') ? (int) $request->centre_id : null;
        } else {
            $centreId = $user->centre_id;
        }

        $query = Corporation::with(['centre', 'creator']);

        if ($centreId) {
            // Un utilisateur de centre voit les corporations de son centre ET les corporations communes
            $query->where(function ($q) use ($centreId) {
                $q->where('centre_id', $centreId)
                  ->orWhereNull('centre_id');
            });
        } elseif (!$isGlobal) {
            $query->whereNull('centre_id');
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where('nom', 'like', "%{$s}%");
        }

        $corporations = $query->orderByRaw('CASE WHEN centre_id IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('nom')
            ->paginate(30)
            ->withQueryString();

        $centres = $isGlobal ? Centre::actifs()->orderBy('nom')->get() : collect();
        $centreActuel = $centreId ? Centre::find($centreId) : null;

        return view('corporations.index', compact('corporations', 'centres', 'centreActuel', 'isGlobal', 'centreId'));
    }

    /**
     * Enregistre une nouvelle corporation.
     * Pour un DRH ou un Assistant RH de centre, la corporation est obligatoirement liée à son centre.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:191',
            'centre_id' => 'nullable|exists:centres,id',
        ]);

        $user = Auth::user();
        $nomNormalise = Personnel::normaliserCorporation($request->nom);

        if (!$nomNormalise) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Le nom de la corporation est invalide.'], 422);
            }
            return back()->with('error', 'Le nom de la corporation est invalide.');
        }

        // Déterminer le centre : si non global, TOUJOURS le centre de l'utilisateur
        if (!$user->isGlobal()) {
            $centreId = $user->centre_id;
        } else {
            $centreId = $request->filled('centre_id') ? (int) $request->centre_id : null;
        }

        // Vérifier si elle existe déjà dans ce centre ou au niveau global
        $existant = Corporation::where('nom', $nomNormalise)
            ->where(function ($q) use ($centreId) {
                if ($centreId) {
                    $q->where('centre_id', $centreId)->orWhereNull('centre_id');
                } else {
                    $q->whereNull('centre_id');
                }
            })
            ->first();

        if ($existant) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'     => true,
                    'corporation' => $existant,
                    'message'     => 'Cette corporation existe déjà et est disponible pour votre centre.',
                ]);
            }
            return back()->with('warning', "La corporation '{$nomNormalise}' existe déjà.");
        }

        $corp = Corporation::create([
            'nom'        => $nomNormalise,
            'centre_id'  => $centreId,
            'created_by' => $user->id,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'corporation' => $corp,
                'message'     => 'Corporation ajoutée avec succès.',
            ]);
        }

        return redirect()->route('corporations.index')->with('success', "Corporation '{$corp->nom}' créée avec succès pour votre centre.");
    }

    /**
     * Supprime une corporation spécifique à un centre.
     */
    public function destroy(Corporation $corporation)
    {
        $user = Auth::user();

        // Impossible de supprimer les corporations de base communes sauf pour le CRH
        if (is_null($corporation->centre_id) && !$user->isCRH()) {
            abort(403, 'Seul le CRH peut modifier les corporations communes.');
        }

        // Un utilisateur local ne peut supprimer que les corporations de son centre
        if (!is_null($corporation->centre_id) && !$user->isGlobal() && $corporation->centre_id !== $user->centre_id) {
            abort(403, 'Vous ne pouvez supprimer que les corporations de votre propre centre.');
        }

        $nom = $corporation->nom;
        $corporation->delete();

        return redirect()->route('corporations.index')->with('success', "Corporation '{$nom}' supprimée avec succès.");
    }
}
