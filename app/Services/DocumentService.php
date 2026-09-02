<?php

namespace App\Services;

use App\Models\Conge;
use App\Models\Absence;
use App\Models\Demande;
use App\Models\Centre;
use App\Models\ConfigRh;
use App\Models\Evaluation;
use App\Models\Personnel;
use App\Models\StagiaireDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    private const DEFAULT_REFERENCE_SUFFIX = 'AC/DDIS/CSVHHSL/DIR/DRH/ARH';

    /**
     * Convertit un chemin d'image en data URI base64.
     */
    public function imageToBase64(?string $storagePath): ?string
    {
        if (!$storagePath) return null;

        // Chemin absolu sur le disque
        $fullPath = Storage::disk('public')->path($storagePath);

        if (!file_exists($fullPath)) return null;

        $mime    = mime_content_type($fullPath) ?: 'image/png';
        $encoded = base64_encode(file_get_contents($fullPath));

        return "data:{$mime};base64,{$encoded}";
    }

    private function signatureToBase64(?string $storagePath): ?string
    {
        return $this->imageToBase64($storagePath);
    }

    /**
     * Résout le chemin de signature : priorité au chemin du document,
     * sinon utilise la signature globale du DRH.
     */
    private function resolveSignature(?string $docPath, ?\App\Models\User $approver = null): ?string
    {
        $path = $docPath ?: ConfigRh::get('drh_signature_path', null, $approver);
        return $this->signatureToBase64($path);
    }

    /**
     * Récupère une configuration RH spécifique à un centre sans dépendre de l'utilisateur connecté.
     */
    private function getCentreConfigValue(int $centreId, string $cle): ?string
    {
        return ConfigRh::where('cle', $cle)
            ->where('centre_id', $centreId)
            ->whereNull('user_id')
            ->whereNotNull('valeur')
            ->where('valeur', '!=', '')
            ->value('valeur');
    }

    /**
     * Construit le bloc signataire pour un utilisateur du centre.
     */
    private function buildTriptychFromUser(User $user, ?Centre $centre = null): array
    {
        $centre ??= $user->centre;
        $titleFallback = $centre?->a_drh_dedie ? 'Directeur des Ressources Humaines' : 'Directeur';

        return [
            'nom'           => ConfigRh::get('drh_nom', null, $user) ?: $user->nom_complet,
            'titre'         => ConfigRh::get('drh_titre', null, $user) ?: $titleFallback,
            'signature_url' => $this->signatureToBase64(ConfigRh::get('drh_signature_path', null, $user) ?: $user->signature_path)
                ?: $user->signature_base64,
            'user'          => $user,
        ];
    }

    /**
     * Génère une référence mensuelle de type O1/07-26/AC/DDIS/CSVHHSL/DIR/DRH/ARH.
     */
    public function generateMonthlyReference(?Carbon $date = null, ?string $suffix = null, ?int $centreId = null): string
    {
        $date ??= now();
        $suffix ??= self::DEFAULT_REFERENCE_SUFFIX;

        $period = $date->format('m-y');
        $pattern = 'O%/' . $period . '/%';
        $count = $this->countReferencesForCentre($pattern, $centreId);
        $sequence = str_pad((string) ($count + 1), 3, '0', STR_PAD_LEFT);

        return 'O' . $sequence . '/' . $period . '/' . $suffix;
    }

    /**
     * Retourne la référence fournie ou génère automatiquement la suivante du mois.
     */
    public function resolveReference(?string $manualReference = null, ?Carbon $date = null, ?string $suffix = null, ?int $centreId = null): string
    {
        if ($manualReference !== null && trim($manualReference) !== '') {
            return trim($manualReference);
        }

        return $this->generateMonthlyReference($date, $suffix, $centreId);
    }

    /**
     * Compte les références du mois pour un centre donné, tous documents officiels confondus.
     */
    private function countReferencesForCentre(string $pattern, ?int $centreId = null): int
    {
        $demandeCount = Demande::whereNotNull('reference')
            ->where('reference', 'like', $pattern)
            ->when($centreId, fn ($q) => $q->whereHas('personnel', fn ($qq) => $qq->where('centre_id', $centreId)))
            ->count();

        $congeCount = Conge::whereNotNull('reference')
            ->where('reference', 'like', $pattern)
            ->when($centreId, fn ($q) => $q->whereHas('personnel', fn ($qq) => $qq->where('centre_id', $centreId)))
            ->count();

        $absenceCount = Absence::whereNotNull('reference')
            ->where('reference', 'like', $pattern)
            ->when($centreId, fn ($q) => $q->whereHas('personnel', fn ($qq) => $qq->where('centre_id', $centreId)))
            ->count();

        $evaluationCount = Evaluation::whereNotNull('reference')
            ->where('reference', 'like', $pattern)
            ->when($centreId, fn ($q) => $q->whereHas('stagiaire', fn ($qq) => $qq->where('centre_id', $centreId)))
            ->count();

        $stagiaireDocCount = StagiaireDocument::whereNotNull('reference')
            ->where('reference', 'like', $pattern)
            ->when($centreId, fn ($q) => $q->whereHas('stagiaire', fn ($qq) => $qq->where('centre_id', $centreId)))
            ->count();

        return $demandeCount + $congeCount + $absenceCount + $evaluationCount + $stagiaireDocCount;
    }

    public function resolveDrhCentre(?Personnel $personnel, ?User $approuvePar = null): array
    {
        $centre   = $personnel?->centre;
        $centreId = $centre?->id;

        if ($approuvePar && !$approuvePar->isCRH()) {
            $nom     = ConfigRh::get('drh_nom', null, $approuvePar) ?: ($approuvePar->nom_complet ?: 'Nom du DRH');
            $titre   = ConfigRh::get('drh_titre', null, $approuvePar) ?: ($approuvePar->titre_effectif ?: 'Directeur des Ressources Humaines');
            $sigPath = ConfigRh::get('drh_signature_path', null, $approuvePar) ?: $approuvePar->signature_path;
            return [
                'nom'           => $nom,
                'titre'         => $titre,
                'signature_url' => $this->signatureToBase64($sigPath) ?: $approuvePar->signature_base64,
                'user'          => $approuvePar,
            ];
        }

        if ($centreId) {
            $drhCentreUser = $centre?->drhOuDirecteur
                ?? User::where('centre_id', $centreId)
                    ->where('role', 'assistant_rh')
                    ->first();

            if ($drhCentreUser) {
                return $this->buildTriptychFromUser($drhCentreUser, $centre);
            }

            $cfgNom   = $this->getCentreConfigValue($centreId, 'drh_nom');
            $cfgTitre = $this->getCentreConfigValue($centreId, 'drh_titre');
            $cfgSig   = $this->getCentreConfigValue($centreId, 'drh_signature_path');

            if ($cfgNom || $cfgTitre || $cfgSig) {
                return [
                    'nom'           => $cfgNom ?: ($centre?->nom ?? 'Nom du signataire'),
                    'titre'         => $cfgTitre ?: ($centre?->a_drh_dedie ? 'Directeur des Ressources Humaines' : 'Directeur'),
                    'signature_url' => $this->signatureToBase64($cfgSig),
                    'user'          => null,
                ];
            }

            return [
                'nom'           => $centre?->nom ?? 'Nom du signataire',
                'titre'         => $centre?->a_drh_dedie ? 'Directeur des Ressources Humaines' : 'Directeur',
                'signature_url' => null,
                'user'          => null,
            ];
        }

        $cfgNom   = ConfigRh::get('drh_nom', 'Le Directeur du Centre');
        $cfgTitre = ConfigRh::get('drh_titre', 'Directeur des Ressources Humaines');
        $cfgSig   = ConfigRh::get('drh_signature_path');

        return [
            'nom'           => $cfgNom,
            'titre'         => $cfgTitre,
            'signature_url' => $this->signatureToBase64($cfgSig),
            'user'          => null,
        ];
    }

    public function buildCongeData(Conge $conge): array
    {
        $p = $conge->personnel;
        $c = $p?->centre;
        $currentUser = auth()->user();
        $approuvePar = $conge->approuvePar ?? $currentUser;
        $redigePar   = $conge->creator ?? $currentUser;
        $drhInfo     = $this->resolveDrhCentre($p, $approuvePar);

        $approuveParCRH = $approuvePar && $approuvePar->isCRH();
        $creeParCRH     = ($redigePar && $redigePar->isCRH()) || ($conge->cree_par && User::find($conge->cree_par)?->isCRH());
        $isCrhAutonome  = $approuveParCRH && $creeParCRH;

        return [
            'type'            => 'conge',
            'document'        => $conge,
            'personnel'       => $p,
            'centre'          => $c,
            'redige_par_nom'  => $redigePar?->nom_complet,
            'drh_nom'         => $drhInfo['nom'],
            'drh_titre'       => $drhInfo['titre'],
            'organisation'    => $c?->nom ?: ConfigRh::get('organisation', 'Institutions Sanitaires Diocésaines'),
            'ville'           => ConfigRh::get('ville', 'Cotonou'),
            'signature_url'   => $approuvePar?->signature_base64 ?: $this->resolveSignature($conge->signature_path, $approuvePar),
            'approuvePar'     => $approuvePar,
            'creePar'         => $redigePar,
            'is_crh_autonome' => $isCrhAutonome,
            'centre_logo'     => $this->imageToBase64($c?->logo_path),
            'entete_image_url'=> $this->imageToBase64($c?->entete_image_path),
            'entete_texte'    => $c?->entete_texte,
            'pied_page_texte' => $c?->pied_page_texte,
            'date_doc'        => $conge->approuve_le
                ? $conge->approuve_le->isoFormat('D MMMM YYYY')
                : now()->isoFormat('D MMMM YYYY'),
        ];
    }

    public function buildAbsenceData(Absence $absence): array
    {
        $p = $absence->personnel;
        $c = $p?->centre;
        $currentUser = auth()->user();
        $approuvePar = $absence->approuvePar ?? $currentUser;
        $redigePar   = $absence->creator ?? $currentUser;
        $drhInfo     = $this->resolveDrhCentre($p, $approuvePar);

        $approuveParCRH = $approuvePar && $approuvePar->isCRH();
        $creeParCRH     = ($redigePar && $redigePar->isCRH()) || ($absence->cree_par && User::find($absence->cree_par)?->isCRH());
        $isCrhAutonome  = $approuveParCRH && $creeParCRH;

        return [
            'type'            => 'absence',
            'document'        => $absence,
            'personnel'       => $p,
            'centre'          => $c,
            'redige_par_nom'  => $redigePar?->nom_complet,
            'drh_nom'         => $drhInfo['nom'],
            'drh_titre'       => $drhInfo['titre'],
            'organisation'    => $c?->nom ?: ConfigRh::get('organisation', 'Institutions Sanitaires Diocésaines'),
            'ville'           => ConfigRh::get('ville', 'Cotonou'),
            'signature_url'   => $approuvePar?->signature_base64 ?: $this->resolveSignature($absence->signature_path, $approuvePar),
            'approuvePar'     => $approuvePar,
            'creePar'         => $redigePar,
            'is_crh_autonome' => $isCrhAutonome,
            'centre_logo'     => $this->imageToBase64($c?->logo_path),
            'entete_image_url'=> $this->imageToBase64($c?->entete_image_path),
            'entete_texte'    => $c?->entete_texte,
            'pied_page_texte' => $c?->pied_page_texte,
            'date_doc'        => $absence->approuve_le
                ? $absence->approuve_le->isoFormat('D MMMM YYYY')
                : now()->isoFormat('D MMMM YYYY'),
        ];
    }


    /**
     * Sauvegarde la signature uploadée par le DRH.
     */
    public function sauvegarderSignature(\Illuminate\Http\UploadedFile $file): string
    {
        return $file->store('signatures', 'public');
    }
}
