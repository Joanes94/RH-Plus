<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Absence;
use App\Models\Demande;
use App\Models\Personnel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DrhController extends Controller
{
    public function index()
    {
        $base = Personnel::personnelPrincipal();
        $user = Auth::user();
        $isGlobal = $user?->isGlobal() || $user?->isCRH();

        $query = $isGlobal ? $base : $base->where('centre_id', $user->centre_id);

        $stats = [
            'personnel_actif'  => (clone $query)->where('statut', 'actif')->count(),
            'anciens_travailleurs' => (clone $query)->anciensTravailleurs()->count(),
            'en_attente'       => Conge::where('statut','soumis')
                                ->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))
                                ->count()
                                + Absence::where('statut','soumis')
                                ->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))
                                ->count()
                                + Demande::where('statut','soumis')
                                ->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))
                                ->count(),
            'conges_en_cours'  => Conge::where('statut','approuve')
                                    ->whereDate('date_debut','<=',now())
                                    ->whereDate('date_fin','>=',now())
                                    ->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))
                                    ->distinct('personnel_id')
                                    ->count('personnel_id'),
            'traites_ce_mois'  => Conge::where('statut','approuve')
                                    ->whereMonth('approuve_le', now()->month)
                                    ->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))
                                    ->count()
                                + Absence::where('statut','approuve')
                                    ->whereMonth('approuve_le', now()->month)
                                    ->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))
                                    ->count()
                                + Demande::where('statut','approuve')
                                    ->whereMonth('approuve_le', now()->month)
                                    ->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))
                                    ->count(),
        ];

        $anciensRecents = (clone $query)->anciensTravailleurs()->orderByDesc('date_depart')->take(5)->get();

        $congesEnAttente   = Conge::with('personnel')->where('statut','soumis')->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))->latest()->take(6)->get();
        $absencesEnAttente = Absence::with('personnel')->where('statut','soumis')->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))->latest()->take(5)->get();
        $demandesEnAttente = Demande::with('personnel')->where('statut','soumis')->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))->latest()->take(5)->get();

        return view('drh.dashboard', compact(
            'stats', 'congesEnAttente', 'absencesEnAttente', 'demandesEnAttente', 'anciensRecents'
        ));
    }

    public function historique()
    {
        $user = Auth::user();
        $userId = Auth::id();
        $historique = [];
        $isGlobal = $user?->isGlobal() || $user?->isCRH();

        // Congés
        foreach (Conge::with('personnel')->where('approuve_par', $userId)
            ->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))
            ->when(request('statut'), fn($q) => $q->where('statut', request('statut')))
            ->when(request('mois'), fn($q) => $q->whereRaw("DATE_FORMAT(approuve_le,'%Y-%m') = ?", [request('mois')]))
            ->latest('approuve_le')->get() as $c) {
            $historique[] = [
                'type_doc'     => 'Congé',
                'type_sub'     => $c->getTypeCongeLabel() ?? 'Congé',
                'agent'        => $c->personnel->nom_complet,
                'service'      => $c->personnel->service,
                'photo_url'    => $c->personnel->photo_url,
                'avatar_url'   => $c->personnel->avatar_url,
                'initiales'    => $c->personnel->initiales,
                'statut_color' => $c->getStatutColor(),
                'statut_label' => $c->getStatutLabel(),
                'date'         => $c->approuve_le,
                'route'        => route('conges.show', $c),
                'doc_route'    => $c->statut === 'approuve' ? route('conges.document', $c) : null,
            ];
        }

        // Absences
        foreach (Absence::with('personnel')->where('approuve_par', $userId)
            ->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))
            ->when(request('statut'), fn($q) => $q->where('statut', request('statut')))
            ->when(request('mois'), fn($q) => $q->whereRaw("DATE_FORMAT(approuve_le,'%Y-%m') = ?", [request('mois')]))
            ->latest('approuve_le')->get() as $a) {
            $historique[] = [
                'type_doc'     => 'Absence',
                'type_sub'     => $a->getTypeLabel(),
                'agent'        => $a->personnel->nom_complet,
                'service'      => $a->personnel->service,
                'photo_url'    => $a->personnel->photo_url,
                'avatar_url'   => $a->personnel->avatar_url,
                'initiales'    => $a->personnel->initiales,
                'statut_color' => $a->getStatutColor(),
                'statut_label' => $a->getStatutLabel(),
                'date'         => $a->approuve_le,
                'route'        => route('absences.show', $a),
                'doc_route'    => $a->statut === 'approuve' ? route('absences.document', $a) : null,
            ];
        }

        // Demandes
        foreach (Demande::with('personnel')->where('approuve_par', $userId)
            ->when(!$isGlobal && $user->centre_id, fn($q) => $q->whereHas('personnel', fn($q2) => $q2->where('centre_id', $user->centre_id)))
            ->when(request('statut'), fn($q) => $q->where('statut', request('statut')))
            ->when(request('mois'), fn($q) => $q->whereRaw("DATE_FORMAT(approuve_le,'%Y-%m') = ?", [request('mois')]))
            ->latest('approuve_le')->get() as $d) {
            $historique[] = [
                'type_doc'     => 'Demande',
                'type_doc_slug'=> strtolower(str_replace(' ', '_', $d->type_label)),
                'type_sub'     => $d->type_label,
                'agent'        => $d->personnel->nom_complet,
                'service'      => $d->personnel->service,
                'photo_url'    => $d->personnel->photo_url,
                'avatar_url'   => $d->personnel->avatar_url,
                'initiales'    => $d->personnel->initiales,
                'statut_color' => $d->statut_color,
                'statut_label' => $d->statut_label,
                'date'         => $d->approuve_le,
                'route'        => route('demandes.show', $d),
                'doc_route'    => $d->statut === 'approuve' ? route('demandes.document', $d) : null,
            ];
        }

        // Tri par date décroissante
        usort($historique, fn($a, $b) =>
            ($b['date'] ?? '') <=> ($a['date'] ?? '')
        );

        return view('drh.historique', compact('historique'));
    }
}