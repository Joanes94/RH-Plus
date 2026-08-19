<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mise à jour des données de branding officielles pour chaque centre,
     * extraites des modèles Word fournis dans le dossier RH-Plus.
     *
     * Centres mis à jour :
     *  - DDIS
     *  - CAFSC
     *  - MARIA_GLETA (CSVH St Jean de Maria Gleta)
     *  - SEYON (Centre Sêyon)
     *  - SO_TCHANHOUE (CSVH St Joseph de Sô-Tchanhoué)
     *  - ST_JEAN (CSVH St Jean Cotonou)
     *
     * ST_LUC est intentionnellement laissé intact.
     * GLO (Padre Pio) n'a pas de modèle fourni, laissé intact.
     */
    public function up(): void
    {
        // ── 1. DDIS ──────────────────────────────────────────────────────────────
        DB::table('centres')->where('code', 'DDIS')->update([
            'nom'              => 'DIRECTION DIOCESAINE DE LA SANTE (DDIS)',
            'adresse'          => 'Cotonou, République du Bénin',
            'telephone'        => '',
            'email'            => 'contact@ddiscotonou.org',
            'logo_path'        => 'letterhead/logo_ddis.jpeg',
            'entete_texte'     => implode("\n", [
                'REPUBLIQUE DU BENIN',
                'ARCHIDIOCESE DE COTONOU',
                'DIRECTION DIOCESAINE DE LA SANTE (DDIS)',
                'www.ddiscotonou.org — contact@ddiscotonou.org',
            ]),
            'pied_page_texte'  => implode(' — ', [
                'DIRECTION DIOCESAINE DE LA SANTE (DDIS)',
                'Archidiocèse de Cotonou',
                'www.ddiscotonou.org',
                'contact@ddiscotonou.org',
                'République du Bénin',
            ]),
            'reference_suffix' => 'AC/DDIS/DIR',
            'updated_at'       => now(),
        ]);

        // ── 2. CAFSC ─────────────────────────────────────────────────────────────
        DB::table('centres')->where('code', 'CAFSC')->update([
            'nom'              => 'CENTRALE D\'APPROVISIONNEMENT DES FORMATIONS SANITAIRES CATHOLIQUES (CAFSC)',
            'adresse'          => 'Tokpa-hoho (Ganhi), Cotonou',
            'telephone'        => '21 31 85 26',
            'email'            => 'phciediocesaine@gmail.com',
            'logo_path'        => 'letterhead/logo_archidiocese.jpeg',
            'entete_texte'     => implode("\n", [
                'ARCHIDIOCESE DE COTONOU',
                '01 BP 491 Cotonou (RB)',
                'DIRECTION DIOCESAINE DE LA SANTE (DDIS)',
                'CENTRALE D\'APPROVISIONNEMENT DES FORMATIONS SANITAIRES CATHOLIQUES (CAFSC)',
                '02 BP : 1306 COTONOU — Tél : 21 31 85 26 — Email : phciediocesaine@gmail.com',
                'Tokpa-hoho (Ganhi) — COTONOU — République du Bénin',
            ]),
            'pied_page_texte'  => implode(' — ', [
                'CAFSC',
                '02 BP 1306 Cotonou',
                'Tél : 21 31 85 26',
                'phciediocesaine@gmail.com',
                'Tokpa-hoho (Ganhi)',
                'République du Bénin',
            ]),
            'reference_suffix' => 'AC/DDIS/CAFSC/DIR',
            'updated_at'       => now(),
        ]);

        // ── 3. MARIA_GLETA ───────────────────────────────────────────────────────
        DB::table('centres')->where('code', 'MARIA_GLETA')->update([
            'nom'              => 'CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT JEAN MARIA GLETA',
            'adresse'          => 'Abomey-Calavi, République du Bénin',
            'telephone'        => '69 27 91 10',
            'email'            => 'cmmariagleta@gmail.com',
            'logo_path'        => 'letterhead/logo_archidiocese.jpeg',
            'entete_texte'     => implode("\n", [
                'ARCHIDIOCESE DE COTONOU',
                '02 B.P 1306',
                'DIRECTION DIOCESAINE DE LA SANTE',
                'TEL. 69 27 91 10',
                'CENTRE DE SANTE A VOCATION HUMANITAIRE',
                'SAINT JEAN MARIA GLETA',
                'Abomey-Calavi — E-mail : cmmariagleta@gmail.com — République du Bénin',
            ]),
            'pied_page_texte'  => implode(' — ', [
                'CSVH Saint Jean Maria Gleta',
                '02 BP 1306',
                'Abomey-Calavi',
                'Tél : 69 27 91 10',
                'cmmariagleta@gmail.com',
                'République du Bénin',
            ]),
            'reference_suffix' => 'AC/DDIS/CSVHMG/DIR/DRH/ARH',
            'updated_at'       => now(),
        ]);

        // ── 4. SEYON ─────────────────────────────────────────────────────────────
        DB::table('centres')->where('code', 'SEYON')->update([
            'nom'              => 'CENTRE DE SANTE A VOCATION HUMANITAIRE SEYON',
            'adresse'          => 'République du Bénin',
            'telephone'        => '',
            'email'            => 'centreseyon@yahoo.com',
            'logo_path'        => 'letterhead/logo_seyon.png',
            'entete_texte'     => implode("\n", [
                'REPUBLIQUE DU BENIN',
                'ARCHIDIOCESE DE COTONOU',
                'DIRECTION DIOCESAINE DE LA SANTE',
                'CENTRE DE SANTE A VOCATION HUMANITAIRE « SEYON »',
                'Centre de Soins, de Recherches en Médecines Naturelles,',
                "d'Accompagnement Spirituel et de Clinique Psychologique.",
                'centreseyon@yahoo.com',
            ]),
            'pied_page_texte'  => implode(' — ', [
                'CSVH Sêyon',
                'Archidiocèse de Cotonou',
                'centreseyon@yahoo.com',
                'République du Bénin',
            ]),
            'reference_suffix' => 'AC/DDIS/CSVHSY/DIR/DRH/ARH',
            'updated_at'       => now(),
        ]);

        // ── 5. SO_TCHANHOUE ──────────────────────────────────────────────────────
        DB::table('centres')->where('code', 'SO_TCHANHOUE')->update([
            'nom'              => 'CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT JOSEPH DE SO-TCHANHOUE',
            'adresse'          => 'Sô-Tchanhoué, République du Bénin',
            'telephone'        => '66 62 25 62',
            'email'            => 'csvhsotchanhoue@gmail.com',
            'logo_path'        => 'letterhead/logo_archidiocese.jpeg',
            'entete_texte'     => implode("\n", [
                'Archidiocèse de Cotonou',
                'Direction Diocésaine de la Santé',
                'Centre de Santé à Vocation Humanitaire',
                'Saint Joseph de Sô-Tchanhoué',
                'Email: csvhsotchanhoue@gmail.com — Tél : 66 62 25 62',
            ]),
            'pied_page_texte'  => implode(' — ', [
                'CSVH Saint Joseph de Sô-Tchanhoué',
                'Archidiocèse de Cotonou',
                'Tél : 66 62 25 62',
                'csvhsotchanhoue@gmail.com',
                'République du Bénin',
            ]),
            'reference_suffix' => 'AC/DDIS/CSVHSJST/DIR/DRH/ARH',
            'updated_at'       => now(),
        ]);

        // ── 6. ST_JEAN (Cotonou) ─────────────────────────────────────────────────
        DB::table('centres')->where('code', 'ST_JEAN')->update([
            'nom'              => 'CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT JEAN',
            'adresse'          => 'Gbégamey, Cotonou',
            'telephone'        => '21 30 36 22 / 53 30 69 23',
            'email'            => 'cm_stjean@yahoo.fr',
            'logo_path'        => 'letterhead/logo_archidiocese.jpeg',
            'entete_texte'     => implode("\n", [
                'ARCHIDIOCESE DE COTONOU',
                'DIRECTION DIOCESAINE DE LA SANTE',
                'CENTRE DE SANTE A VOCATION HUMANITAIRE',
                'SAINT JEAN',
                'Gbégamey, Cotonou — Tél : 21 30 36 22 / 53 30 69 23',
                'Email : cm_stjean@yahoo.fr',
            ]),
            'pied_page_texte'  => implode(' — ', [
                'CSVH Saint Jean',
                'Gbégamey, Cotonou',
                'Tél : 21 30 36 22 / 53 30 69 23',
                'cm_stjean@yahoo.fr',
                'République du Bénin',
            ]),
            'reference_suffix' => 'AC/DDIS/CSVHSJ/DIR/DRH/ARH',
            'updated_at'       => now(),
        ]);
    }

    public function down(): void
    {
        $codes = ['DDIS', 'CAFSC', 'MARIA_GLETA', 'SEYON', 'SO_TCHANHOUE', 'ST_JEAN'];
        DB::table('centres')->whereIn('code', $codes)->update([
            'entete_texte'     => null,
            'pied_page_texte'  => null,
            'reference_suffix' => null,
            'logo_path'        => null,
            'updated_at'       => now(),
        ]);
    }
};
