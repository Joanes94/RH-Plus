<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Génère un export (dump) compressé de la base de données MySQL, stocké dans
 * storage/app/backups (donc INACCESSIBLE depuis le web — ce dossier n'est pas
 * dans public/). Les sauvegardes plus vieilles que $joursConservation jours sont
 * supprimées automatiquement pour ne pas saturer votre quota d'hébergement.
 *
 * Ceci ne remplace pas les sauvegardes automatiques de Hostinger (hPanel) :
 * c'est un filet de sécurité supplémentaire, que vous pouvez ensuite
 * télécharger vous-même (File Manager ou FileZilla) pour l'archiver sur un
 * disque dur externe.
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Génère un dump compressé de la base de données dans storage/app/backups';

    private int $joursConservation = 14;

    public function handle(): int
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            $this->error("Cette commande ne prend en charge que MySQL (connexion actuelle : {$connection}).");
            return self::FAILURE;
        }

        $dossier = storage_path('app/backups');
        if (!File::exists($dossier)) {
            File::makeDirectory($dossier, 0755, true);
        }

        $horodatage = now()->format('Y-m-d_His');
        $nomFichier = "backup_{$config['database']}_{$horodatage}.sql";
        $cheminSql = "{$dossier}/{$nomFichier}";
        $cheminGz = "{$cheminSql}.gz";

        $host = escapeshellarg($config['host']);
        $port = escapeshellarg((string) ($config['port'] ?? 3306));
        $user = escapeshellarg($config['username']);
        $database = escapeshellarg($config['database']);

        // Mot de passe passé via variable d'environnement plutôt qu'en argument
        // de commande, pour ne pas l'exposer dans la liste des process (ps aux).
        $commande = sprintf(
            'mysqldump --single-transaction --quick --host=%s --port=%s --user=%s %s > %s 2>&1',
            $host,
            $port,
            $user,
            $database,
            escapeshellarg($cheminSql)
        );

        putenv('MYSQL_PWD=' . ($config['password'] ?? ''));
        $this->info('Génération du dump en cours…');
        exec($commande, $sortie, $codeRetour);
        putenv('MYSQL_PWD');

        if ($codeRetour !== 0 || !File::exists($cheminSql)) {
            $this->error('Échec du dump : ' . implode("\n", $sortie));
            \Illuminate\Support\Facades\Log::error('Échec de backup:database : ' . implode("\n", $sortie));
            return self::FAILURE;
        }

        // Compression gzip pour économiser l'espace disque
        $donnees = File::get($cheminSql);
        file_put_contents($cheminGz, gzencode($donnees, 9));
        File::delete($cheminSql);

        $tailleMo = round(filesize($cheminGz) / 1024 / 1024, 2);
        $this->info("Sauvegarde créée : storage/app/backups/{$nomFichier}.gz ({$tailleMo} Mo)");

        $this->purgerAnciennesSauvegardes($dossier);

        return self::SUCCESS;
    }

    private function purgerAnciennesSauvegardes(string $dossier): void
    {
        $seuil = now()->subDays($this->joursConservation);
        $supprimees = 0;

        foreach (File::files($dossier) as $fichier) {
            if (now()->timestamp - $fichier->getMTime() > $this->joursConservation * 86400) {
                File::delete($fichier->getPathname());
                $supprimees++;
            }
        }

        if ($supprimees > 0) {
            $this->info("{$supprimees} ancienne(s) sauvegarde(s) de plus de {$this->joursConservation} jours supprimée(s).");
        }
    }
}