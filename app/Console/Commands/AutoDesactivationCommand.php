<?php

namespace App\Console\Commands;

use App\Models\Contrat;
use App\Models\Personnel;
use App\Models\PersonnelHistorique;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoDesactivationCommand extends Command
{
    protected $signature = 'personnel:auto-desactiver {--dry-run : Afficher les actions sans les exécuter}';
    protected $description = 'Désactive automatiquement les CDD dont le contrat est arrivé à terme et les CDI à l\'âge de la retraite (60 ans).';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $today = Carbon::today();
        $count = 0;

        // ── CDD / Prestataire : contrat arrivé à terme ───────────────────────
        $contratsExpires = Contrat::where('statut', 'actif')
            ->whereIn('type_contrat', ['CDD', 'Prestataire'])
            ->whereNotNull('date_fin')
            ->where('date_fin', '<=', $today)
            ->with('personnel')
            ->get();

        foreach ($contratsExpires as $contrat) {
            $personnel = $contrat->personnel;
            if (!$personnel || $personnel->est_ancien_travailleur) continue;

            if ($dryRun) {
                $this->info("[DRY-RUN] Désactivation CDD : {$personnel->nom_complet} — Contrat #{$contrat->id} expiré le {$contrat->date_fin->format('d/m/Y')}");
            } else {
                // Archiver le contrat
                $contrat->update(['statut' => 'termine']);

                // Désactiver le personnel
                $personnel->update([
                    'statut'       => 'ancien',
                    'motif_depart' => 'fin_contrat',
                    'date_depart'  => $today,
                ]);

                // Créer l'historique
                PersonnelHistorique::create([
                    'personnel_id'     => $personnel->id,
                    'contrat_id'       => $contrat->id,
                    'centre_id'        => $personnel->centre_id,
                    'date_debut'       => $contrat->date_debut,
                    'date_fin'         => $today,
                    'type_contrat'     => $contrat->type_contrat,
                    'categorie_echelon'=> $contrat->categorie_echelon,
                    'salaire_base'     => $contrat->salaire_base,
                    'corporation'      => $contrat->fonction ?? $personnel->corporation,
                    'service'          => $contrat->service ?? $personnel->service,
                    'centre_nom'       => $contrat->centre ?? $personnel->centre?->nom,
                    'type_evenement'   => 'fin_contrat',
                    'commentaire'      => 'Désactivation automatique — contrat CDD/Prestataire arrivé à terme.',
                ]);

                $this->info("✓ Désactivé : {$personnel->nom_complet} (CDD expiré le {$contrat->date_fin->format('d/m/Y')})");
            }
            $count++;
        }

        // ── CDI : retraite à 60 ans ──────────────────────────────────────────
        $personnelsRetraite = Personnel::whereHas('contrats', function ($q) {
                $q->where('statut', 'actif')->where('type_contrat', 'CDI');
            })
            ->whereNotIn('statut', ['ancien', 'retraite'])
            ->whereNotNull('date_naissance')
            ->where('date_naissance', '<=', $today->copy()->subYears(60))
            ->with(['contrats' => fn($q) => $q->where('statut', 'actif')->where('type_contrat', 'CDI')])
            ->get();

        foreach ($personnelsRetraite as $personnel) {
            $contrat = $personnel->contrats->first();

            if ($dryRun) {
                $this->info("[DRY-RUN] Retraite CDI : {$personnel->nom_complet} — Né(e) le {$personnel->date_naissance->format('d/m/Y')} (60 ans atteints)");
            } else {
                if ($contrat) {
                    $contrat->update(['statut' => 'termine']);
                }

                $personnel->update([
                    'statut'       => 'retraite',
                    'motif_depart' => 'retraite',
                    'date_depart'  => $today,
                ]);

                PersonnelHistorique::create([
                    'personnel_id'     => $personnel->id,
                    'contrat_id'       => $contrat?->id,
                    'centre_id'        => $personnel->centre_id,
                    'date_debut'       => $contrat?->date_debut,
                    'date_fin'         => $today,
                    'type_contrat'     => 'CDI',
                    'categorie_echelon'=> $contrat?->categorie_echelon ?? $personnel->categorie_echelon,
                    'salaire_base'     => $contrat?->salaire_base,
                    'corporation'      => $contrat?->fonction ?? $personnel->corporation,
                    'service'          => $contrat?->service ?? $personnel->service,
                    'centre_nom'       => $personnel->centre?->nom,
                    'type_evenement'   => 'desactivation',
                    'commentaire'      => 'Départ en retraite automatique — 60 ans atteints.',
                ]);

                $this->info("✓ Retraité : {$personnel->nom_complet} (60 ans le {$personnel->date_naissance->copy()->addYears(60)->format('d/m/Y')})");
            }
            $count++;
        }

        // ── Stagiaires : fin de stage échue ─────────────────────────────────
        $stagiairesFin = \App\Models\Stagiaire::where('statut', 'en_cours')
            ->whereNotNull('date_fin_stage')
            ->where('date_fin_stage', '<=', $today)
            ->get();

        $stagiaireCount = 0;
        foreach ($stagiairesFin as $stagiaire) {
            $dateFinStr = $stagiaire->date_fin_stage ? $stagiaire->date_fin_stage->format('d/m/Y') : $today->format('d/m/Y');
            if ($dryRun) {
                $this->info("[DRY-RUN] Fin de stage : {$stagiaire->nom_complet} — Date de fin {$dateFinStr}");
            } else {
                $stagiaire->update(['statut' => 'termine']);

                if ($stagiaire->centre_id) {
                    \App\Models\Notification::create([
                        'centre_id'         => $stagiaire->centre_id,
                        'type'              => 'fin_stage',
                        'titre'             => 'Fin de stage - ' . $stagiaire->nom_complet,
                        'message'           => "Le stage de {$stagiaire->nom_complet}" . ($stagiaire->titre ? " ({$stagiaire->titre})" : "") . " est arrivé à terme le {$dateFinStr}. Le statut a été marqué comme Terminé.",
                        'date_notification' => $today,
                    ]);
                }
                $this->info("✓ Stage terminé : {$stagiaire->nom_complet} (Fin le {$dateFinStr})");
            }
            $stagiaireCount++;
        }

        $this->info("");
        $this->info($dryRun
            ? "{$count} personnel(s) et {$stagiaireCount} stagiaire(s) serai(en)t traité(s)."
            : "{$count} personnel(s) désactivé(s) et {$stagiaireCount} stagiaire(s) marqué(s) Terminé."
        );

        return Command::SUCCESS;
    }

}
