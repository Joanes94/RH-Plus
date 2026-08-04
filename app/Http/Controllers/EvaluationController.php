<?php
// app/Http/Controllers/EvaluationController.php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Stagiaire;
use App\Models\ConfigRh;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class EvaluationController extends Controller
{
    /**
     * Génère une référence mensuelle séquentielle de type O1/07-26/AC/DDIS/CSVHHSL/DIR/DRH/ARH.
     */
    private function generateMonthlyReference(?Carbon $date = null, ?string $suffix = null): string
    {
        $date ??= now();
        $suffix ??= 'AC/DDIS/CSVHHSL/DIR/DRH/ARH';

        $period = $date->format('m-y');
        $key    = 'evaluation_ref_counter:' . $period;
        $ttl    = $date->copy()->endOfMonth()->endOfDay();

        if (!Cache::has($key)) {
            Cache::put($key, 0, $ttl);
        }

        $count = Cache::increment($key);

        return 'O' . $count . '/' . $period . '/' . $suffix;
    }

    // ── Liste ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Evaluation::with('stagiaire', 'creePar', 'approuvePar')->latest();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('stagiaire_id')) {
            $query->where('stagiaire_id', $request->stagiaire_id);
        }

        $evaluations = $query->paginate(20)->withQueryString();

        return view('evaluations.index', compact('evaluations'));
    }

    // ── Création ──────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $stagiaireId = $request->get('stagiaire_id');
        $stagiaire = $stagiaireId ? Stagiaire::find($stagiaireId) : null;

        return view('evaluations.create', compact('stagiaire'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'stagiaire_id' => 'required|exists:stagiaires,id',
            'qualites' => 'nullable|string',
            'defauts' => 'nullable|string',
            'maitrise_pratique' => 'nullable|string',
            'appreciation_personnelle' => 'nullable|string',
            'action' => 'required|in:brouillon,soumettre',
        ]);

        $evaluation = Evaluation::create([
            'stagiaire_id' => $data['stagiaire_id'],
            'qualites' => $data['qualites'] ?? null,
            'defauts' => $data['defauts'] ?? null,
            'maitrise_pratique' => $data['maitrise_pratique'] ?? null,
            'appreciation_personnelle' => $data['appreciation_personnelle'] ?? null,
            'statut' => $data['action'] === 'soumettre' ? 'soumis' : 'brouillon',
            'cree_par' => Auth::id(),
        ]);

        $msg = $data['action'] === 'soumettre'
            ? 'Évaluation soumise au DRH pour validation.'
            : 'Brouillon enregistré.';

        return redirect()->route('evaluations.show', $evaluation)->with('success', $msg);
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    public function show(Evaluation $evaluation)
    {
        $evaluation->load('stagiaire', 'creePar', 'approuvePar');
        return view('evaluations.show', compact('evaluation'));
    }

    // ── Modification ──────────────────────────────────────────────────────────
    public function edit(Evaluation $evaluation)
    {
        abort_if(!$evaluation->isEditable(), 403, 'Cette évaluation ne peut plus être modifiée.');

        return view('evaluations.edit', compact('evaluation'));
    }

    public function update(Request $request, Evaluation $evaluation)
    {
        abort_if(!$evaluation->isEditable(), 403);

        $data = $request->validate([
            'qualites' => 'nullable|string',
            'defauts' => 'nullable|string',
            'maitrise_pratique' => 'nullable|string',
            'appreciation_personnelle' => 'nullable|string',
            'action' => 'required|in:brouillon,soumettre',
        ]);

        $evaluation->update([
            'qualites' => $data['qualites'] ?? null,
            'defauts' => $data['defauts'] ?? null,
            'maitrise_pratique' => $data['maitrise_pratique'] ?? null,
            'appreciation_personnelle' => $data['appreciation_personnelle'] ?? null,
            'statut' => $data['action'] === 'soumettre' ? 'soumis' : 'brouillon',
        ]);

        $msg = $data['action'] === 'soumettre'
            ? 'Évaluation soumise au DRH pour validation.'
            : 'Brouillon mis à jour.';

        return redirect()->route('evaluations.show', $evaluation)->with('success', $msg);
    }

    public function destroy(Evaluation $evaluation)
    {
        abort_if(!$evaluation->isEditable(), 403);
        $evaluation->delete();

        return redirect()->route('evaluations.index')->with('success', 'Évaluation supprimée.');
    }

    // ── Approbation / Rejet (DRH uniquement) ─────────────────────────────────
    public function approuver(Request $request, Evaluation $evaluation)
    {
        abort_if(!Auth::user()->isDRH(), 403, 'Seul le DRH peut approuver cette évaluation.');
        abort_if($evaluation->statut !== 'soumis', 422, 'Cette évaluation n\'est pas en attente.');

        $signPath = null;
        if ($request->hasFile('signature')) {
            $signPath = $request->file('signature')->store('signatures/evaluations', 'public');
        } else {
            $signPath = ConfigRh::get('drh_signature_path');
        }

        $evaluation->update([
            'statut' => 'approuve',
            'approuve_par' => Auth::id(),
            'approuve_le' => now(),
            'signature_path' => $signPath,
            'reference' => $request->filled('reference') ? $request->reference : $this->generateMonthlyReference(),
        ]);

        return redirect()->route('evaluations.show', $evaluation)
            ->with('success', 'Évaluation approuvée avec succès.');
    }

    public function rejeter(Request $request, Evaluation $evaluation)
    {
        abort_if(!Auth::user()->isDRH(), 403, 'Seul le DRH peut rejeter cette évaluation.');

        $request->validate(['motif_rejet' => 'required|string|max:500']);

        $evaluation->update([
            'statut' => 'rejete',
            'motif_rejet' => $request->motif_rejet,
            'approuve_par' => Auth::id(),
            'approuve_le' => now(),
        ]);

        return redirect()->route('evaluations.show', $evaluation)
            ->with('success', 'Évaluation rejetée.');
    }

    // ── Document PDF ──────────────────────────────────────────────────────────
    public function document(Evaluation $evaluation)
    {
        abort_if($evaluation->statut !== 'approuve', 403, 'L\'évaluation doit être approuvée pour générer le document.');

        $evaluation->load('stagiaire');

        $logoPath = public_path('images/letterhead/logo_archidiocese.jpeg');
        $logoB64 = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : null;

        $evequePath = public_path('images/letterhead/photo_eveque.jpeg');
        $evequeB64 = file_exists($evequePath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($evequePath)) : null;

        $signaturePath = $evaluation->signature_path;
        $signatureUrl = null;
        if ($signaturePath) {
            if (Storage::disk('public')->exists($signaturePath)) {
                $signatureUrl = Storage::url($signaturePath);
            } elseif (filter_var($signaturePath, FILTER_VALIDATE_URL)) {
                $signatureUrl = $signaturePath;
            }
        }

        return view('evaluations.document', [
            'evaluation' => $evaluation,
            'stagiaire' => $evaluation->stagiaire,
            'signature_url' => $signatureUrl,
            'logo_b64' => $logoB64,
            'eveque_b64' => $evequeB64,
            'date_doc' => now()->isoFormat('DD MMMM YYYY'),
            'ville' => ConfigRh::get('ville', 'Cotonou'),
            'drh_nom' => ConfigRh::get('drh_nom', ''),
            'drh_titre' => ConfigRh::get('drh_titre', 'Directeur des Ressources Humaines'),
        ]);
    }
}