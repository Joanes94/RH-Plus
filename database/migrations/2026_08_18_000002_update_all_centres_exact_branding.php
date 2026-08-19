<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mise à jour fidèle des en-têtes, pieds de page et images des 8 centres.
     */
    public function up(): void
    {
        // 1. DDIS
        DB::table('centres')->where('code', 'DDIS')->update([
            'nom'              => 'DIRECTION DIOCESAINE DE LA SANTE (DDIS)',
            'email'            => 'contact@ddiscotonou.org',
            'logo_path'        => 'letterhead/logo_ddis.jpeg',
            'entete_texte'     => "REPUBLIQUE DU BENIN\nARCHIDIOCESE DE COTONOU\nDIRECTION DIOCESAINE DE LA SANTE (DDIS)\nwww.ddiscotonou.org    contact@ddiscotonou.org",
            'pied_page_texte'  => '02 BP : 1306 COTONOU  (BENIN)  Tél : +229 90075025   N° IFU: 6201910786538 Compte BOA n° 006174050000',
            'reference_suffix' => 'AC/DDIS/DIR',
            'updated_at'       => now(),
        ]);

        // 2. CAFSC (Pas de bas de page)
        DB::table('centres')->where('code', 'CAFSC')->update([
            'nom'              => 'CENTRALE D\'APPROVISIONNEMENT DES FORMATIONS SANITAIRES CATHOLIQUES (CAFSC)',
            'adresse'          => 'Tokpa-hoho (Ganhi), Cotonou',
            'telephone'        => '21 31 85 26',
            'email'            => 'phciediocesaine@gmail.com',
            'logo_path'        => null,
            'entete_texte'     => "ARCHIDIOCESE DE COTONOU\n01 BP 491 Cotonou (RB)\nDIRECTION DIOCESAINE DE LA SANTE (DDIS)\nCENTRALE D'APPROVISIONNEMENT DES FORMATIONS SANITAIRES CATHOLIQUES (CAFSC)\n02 BP : 1306 COTONOU   ;    Tél: 21 31 85 26   ;    Email: phciediocesaine@gmail.com\nTokpa-hoho (Ganhi)   COTONOU",
            'pied_page_texte'  => null,
            'reference_suffix' => 'AC/DDIS/CAFSC/DIR',
            'updated_at'       => now(),
        ]);

        // 3. PADRE PIO (GLO) (Pas de bas de page)
        DB::table('centres')->where('code', 'GLO')->update([
            'nom'              => 'CENTRE DE SANTE A VOCATION HUMANITAIRE PADRE PIO GLO DJIGBE',
            'adresse'          => 'Glo Djigbé, Bénin',
            'telephone'        => '0169813953',
            'email'            => 'Csvhpadrepioglo@gmail.com',
            'logo_path'        => 'letterhead/logo_padre_pio.jpg',
            'entete_texte'     => "Archidiocèse de Cotonou\nDIRECTION DIOCESAINE DE LA SANTE\nCENTRE DE SANTE A VOCATION HUMANITAIRE PADRE PIO GLO DJIGBE\nTÉL : 0169813953 / Whatsapp : 93374043 BP : 0322 GLO DJIGBE\nCsvhpadrepioglo@gmail.com\nREPUBLIQUE DU BENIN",
            'pied_page_texte'  => null,
            'reference_suffix' => 'AC/DDIS/CSVHPPG/DIR/DRH/ARH',
            'updated_at'       => now(),
        ]);

        // 4. MARIA GLETA (Pas de bas de page)
        DB::table('centres')->where('code', 'MARIA_GLETA')->update([
            'nom'              => 'CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT JEAN MARIA GLETA',
            'adresse'          => 'Abomey-Calavi, République du Bénin',
            'telephone'        => '69 27 91 10',
            'email'            => 'cmariagleta@gmail.com',
            'logo_path'        => null,
            'entete_texte'     => "ARCHIDIOCESE DE COTONOU                              02 B.P 1306\nDIRECTION DIOCESAINE DE LA SANTE                               TEL. 69 27 91 10\nCENTRE DE SANTE A VOCATION HUMANITAIRE              Abomey-calavi\nSAINT JEAN MARIA GLETA                                         République du Bénin\nE-mail : cmariagleta@gmail.com",
            'pied_page_texte'  => null,
            'reference_suffix' => 'AC/DDIS/CSVHMG/DIR/DRH/ARH',
            'updated_at'       => now(),
        ]);

        // 5. SEYON (Pied de page exact + IFU)
        DB::table('centres')->where('code', 'SEYON')->update([
            'nom'              => 'CENTRE DE SANTE A VOCATION HUMANITAIRE SEYON',
            'email'            => 'centreseyon@yahoo.com',
            'logo_path'        => 'letterhead/logo_seyon.png',
            'entete_texte'     => "RÉPUBLIQUE DU BÉNIN ..oo0oo.. ARCHIDIOCÈSE DE COTONOU ..oo0oo.. DIRECTION DIOCESAINE DE LA SANTE ....oo0oo.... CENTRE DE SANTE A VOCATION HUMANITAIRE ''SÊYON'' Centre de Soins, de Recherches en Médecines Naturelles, d'Accompagnement Spirituel et de Clinique Psychologique. ..oo0oo..",
            'pied_page_texte'  => "02 BP 666 - Tél. : 69 27 91 06/69 19 09 09 Lot 2022 Zogbohoué derrière le Stade de l'Amitié Kouhounou\nCentre à but non lucratif COTONOU (Rép.du Bénin) - E-mail centreseyon@yahoo.com\nIFU : 6 2010 0106 3709",
            'reference_suffix' => 'AC/DDIS/CSVHSY/DIR/DRH/ARH',
            'updated_at'       => now(),
        ]);

        // 6. ST LUC
        DB::table('centres')->where('code', 'ST_LUC')->update([
            'nom'              => 'CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT LUC',
            'email'            => 'hopitalsaintluc@gmail.com',
            'logo_path'        => 'letterhead/logo_archidiocese.jpeg',
            'entete_texte'     => "ARCHIDIOCESE DE COTONOU\nDIRECTION DIOCESAINE DE LA SANTE\nCENTRE DE SANTE A VOCATION HUMANITAIRE SAINT LUC\nC.S.V.H (ex : Hôpital Saint LUC)\nQtier Missèkplé Ste Rita- 01 BP 3603 Tél : 66 43 44 78 - 90 07 49 67 / Email : hopitalsaintluc@gmail.com / Cotonou - BENIN",
            'pied_page_texte'  => "NOUVELLE AUTORISATION MINISTERIELLE N°071/MS/DC/SGM/CJ/DNSP/SRS/SA/063SGG20 DU 02/07/2020\nN°INSAE : 2988511276715    N° IFU 3200800472415",
            'reference_suffix' => 'AC/DDIS/CSVHHSL/DIR/DRH/ARH',
            'updated_at'       => now(),
        ]);

        // 7. SO_TCHANHOUE (Pas de bas de page)
        DB::table('centres')->where('code', 'SO_TCHANHOUE')->update([
            'nom'              => 'CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT JOSEPH DE SO-TCHANHOUE',
            'adresse'          => 'Sô-Tchanhoué, République du Bénin',
            'telephone'        => '66 62 25 62',
            'email'            => 'csvhsotchanhoue@gmail.com',
            'logo_path'        => 'letterhead/logo_st_joseph.png',
            'entete_texte'     => "Archidiocèse de Cotonou\nDirection Diocésaine de la Santé\nCentre de Santé à Vocation Humanitaire Saint Joseph de Sô-Tchanhoué\nEmail: csvhsotchanhoue@gmail.com\nTél : 66 62 25 62",
            'pied_page_texte'  => null,
            'reference_suffix' => 'AC/DDIS/CSVHSJST/DIR/DRH/ARH',
            'updated_at'       => now(),
        ]);

        // 8. ST_JEAN
        DB::table('centres')->where('code', 'ST_JEAN')->update([
            'nom'              => 'CENTRE DE SANTE A VOCATION HUMANITAIRE SAINT JEAN',
            'adresse'          => 'Gbégamey, Cotonou',
            'telephone'        => '21 30 36 22 / 53 30 69 23',
            'email'            => 'cm_stjean@yahoo.fr',
            'logo_path'        => 'letterhead/logo_archidiocese.jpeg',
            'entete_texte'     => "REPUBLIQUE DU BENIN\nARCHIDIOCESE DE COTONOU\nDIRECTION DIOCESAINE DE LA SANTE (DDIS)\nCENTRE DE SANTE A VOCATION HUMANITAIRE \"SAINT JEAN\" DE COTONOU\nGbégamey, Cotonou — Tél : 21 30 36 22 / 53 30 69 23\nEmail : cm_stjean@yahoo.fr",
            'pied_page_texte'  => "CSVH Saint Jean — Gbégamey, Cotonou — Tél : 21 30 36 22 / 53 30 69 23 — cm_stjean@yahoo.fr — République du Bénin",
            'reference_suffix' => 'AC/DDIS/CSVHSJ/DIR/DRH/ARH',
            'updated_at'       => now(),
        ]);
    }

    public function down(): void
    {
    }
};
