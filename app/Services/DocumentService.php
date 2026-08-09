<?php

namespace App\Services;

use App\Models\Conge;
use App\Models\Absence;
use App\Models\Demande;
use App\Models\ConfigRh;
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
     * Génère une référence mensuelle de type O1/07-26/AC/DDIS/CSVHHSL/DIR/DRH/ARH.
     */
    public function generateMonthlyReference(?Carbon $date = null, ?string $suffix = null): string
    {
        $date ??= now();
        $suffix ??= self::DEFAULT_REFERENCE_SUFFIX;

        $period = $date->format('m-y');
        $pattern = 'O%/' . $period . '/%';

        $count = Demande::whereNotNull('reference')->where('reference', 'like', $pattern)->count()
            + Conge::whereNotNull('reference')->where('reference', 'like', $pattern)->count()
            + Absence::whereNotNull('reference')->where('reference', 'like', $pattern)->count();

        return 'O' . ($count + 1) . '/' . $period . '/' . $suffix;
    }

    /**
     * Retourne la référence fournie ou génère automatiquement la suivante du mois.
     */
    public function resolveReference(?string $manualReference = null, ?Carbon $date = null, ?string $suffix = null): string
    {
        if ($manualReference !== null && trim($manualReference) !== '') {
            return trim($manualReference);
        }

        return $this->generateMonthlyReference($date, $suffix);
    }

    public function buildCongeData(Conge $conge): array
    {
        $p = $conge->personnel;
        $c = $p?->centre;
        $currentUser = auth()->user();
        $approuvePar = $conge->approuvePar ?? $currentUser;
        $redigePar   = $conge->creator ?? $currentUser;

        return [
            'type'            => 'conge',
            'document'        => $conge,
            'personnel'       => $p,
            'centre'          => $c,
            'redige_par_nom'  => $redigePar?->nom_complet,
            'drh_nom'         => $approuvePar?->nom_complet ?: ConfigRh::get('drh_nom', 'Nom du DRH'),
            'drh_titre'       => $approuvePar?->titre_effectif ?: ConfigRh::get('drh_titre', 'Directeur des Ressources Humaines'),
            'organisation'    => $c?->nom ?: ConfigRh::get('organisation', 'Institutions Sanitaires Diocésaines'),
            'ville'           => ConfigRh::get('ville', 'Cotonou'),
            'signature_url'   => $approuvePar?->signature_base64 ?: $this->resolveSignature($conge->signature_path, $approuvePar),
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

        return [
            'type'            => 'absence',
            'document'        => $absence,
            'personnel'       => $p,
            'centre'          => $c,
            'redige_par_nom'  => $redigePar?->nom_complet,
            'drh_nom'         => $approuvePar?->nom_complet ?: ConfigRh::get('drh_nom', 'Nom du DRH'),
            'drh_titre'       => $approuvePar?->titre_effectif ?: ConfigRh::get('drh_titre', 'Directeur des Ressources Humaines'),
            'organisation'    => $c?->nom ?: ConfigRh::get('organisation', 'Institutions Sanitaires Diocésaines'),
            'ville'           => ConfigRh::get('ville', 'Cotonou'),
            'signature_url'   => $approuvePar?->signature_base64 ?: $this->resolveSignature($absence->signature_path, $approuvePar),
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
