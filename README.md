# RH Plus

Application de gestion des Ressources Humaines pour les **Institutions Sanitaires Diocésaines (ISD)** de l'Archidiocèse de Cotonou (CSVH Saint Luc). Développée avec **Laravel 13**.

RH Plus centralise la gestion du personnel, des contrats, des congés/absences, des avancements de carrière, des stagiaires et des rapports RH pour deux profils d'utilisateurs : **Assistant RH** et **DRH** (Directeur des Ressources Humaines).

---

## Sommaire

- [Stack technique](#stack-technique)
- [Fonctionnalités](#fonctionnalités)
- [Rôles](#rôles)
- [Installation](#installation)
- [Configuration](#configuration)
- [Tâches planifiées (cron)](#tâches-planifiées-cron)
- [Structure du projet](#structure-du-projet)
- [Notes et limites connues](#notes-et-limites-connues)

---

## Stack technique

| Composant       | Détail                              |
|-----------------|--------------------------------------|
| Framework       | Laravel 13 (PHP 8.3+)               |
| Base de données | MySQL                                |
| Vues            | Blade (pas de framework JS front)   |
| Styles          | CSS natif (`public/css/app.css`)    |
| Auth            | Laravel Auth (session, 2 rôles)     |
| Documents       | Pages HTML imprimables → PDF via le navigateur (`window.print()`) |

---

## Fonctionnalités

### 👤 Personnel
- Fiche complète : identité, nationalité, résidence, situation matrimoniale, diplômes, service, corporation, CNSS, dates d'embauche...
- **Ayants droits** : conjoint(s) et enfants (nom, prénom, sexe, date de naissance), avec limite d'âge automatique (21 ans) pour les enfants.
- Import Excel en masse + téléchargement d'un modèle.
- Affectation vers un autre service/centre.
- Photo de profil.

### 📄 Contrats (plusieurs par personnel)
- Un personnel peut avoir **plusieurs contrats successifs** dans le temps : CDI, CDD, Prestataire, Stagiaire, Vacataire, Autre.
- **CDI** : date de fin calculée automatiquement (60 ans de l'agent).
- **CDD** et **Prestataire** : durée en mois saisie, date de fin calculée automatiquement.
- **Catégorie / Échelon** reliés à la grille salariale (salaire pré-rempli automatiquement).
- **Contrat de Prestation** : honoraires de garde et de permanence, distincts du salaire classique.
- Génération de documents imprimables fidèles aux modèles officiels (CDD, CDI, Contrat de Prestation), avec en-tête complet (photo + logo Archidiocèse).

### 📈 Avancements de carrière (grille salariale)
- Grille salariale complète importée (catégories E1→E6, M1→M3, C1-C2 × 11 échelons).
- **Avancement d'échelon automatique** tous les 2 ans d'ancienneté (lettre générée, signée DRH).
- **Bonification à 58 ans** (article 88 de l'Accord d'Établissement) : coefficient spécial appliqué au salaire de catégorie, avec double lettre (employé + Directeur du centre).
- Vérification automatique programmable (cron quotidien) ou déclenchable manuellement depuis la page Personnel.
- Historique complet des avancements consultable sur chaque fiche personnel.

### 🏖️ Congés
- Types : Administratif, Technique, **Maternité**.
- **Congé de maternité** : 98 jours calendaires (droit béninois), réservé au personnel féminin, calcul automatique de la date de reprise (premier jour ouvrable suivant).
- Calcul du solde de congés selon l'ancienneté.
- Workflow de validation (soumission → approbation/rejet par le DRH).
- Génération de lettre de notification imprimable.

### ⏱️ Absences
- Déclaration d'absences exceptionnelles avec motif.
- Lettre de reprise de service imprimable.

### 📝 Demandes (diverses)
- Attestations de travail, autorisations, etc., avec documents imprimables dédiés.

### 🎓 Stagiaires
- Gestion des stagiaires et des demandes de stage.
- Documents et pièces jointes par stagiaire.

### 📊 Rapports
- **Rapport situation actuelle** : filtrable par service, sexe, statut, type de contrat, corporation.
- **Rapport historique (année / mois)** : reconstitue qui travaillait où et en quelle qualité à une période donnée, à partir des dates réelles de contrat (utile pour un contrôle a posteriori, ex. "qui était en poste en juin 2026 ?").
- Export PDF pour les deux rapports.

### 📦 Anciens travailleurs
- Un agent devient « Ancien travailleur » lorsqu'il est **affecté ailleurs**, son **contrat est terminé**, il est **débauché** ou **retraité**.
- Archivage depuis la fiche personnel (motif + date), avec **restauration possible** à tout moment.
- Section dédiée sur les deux tableaux de bord (Assistant RH et DRH).

### 🖥️ Tableaux de bord
- **Assistant RH** : vue d'ensemble personnel, congés/absences/demandes en attente, anciens travailleurs récents.
- **DRH** : KPI globaux, validations en attente, historique des décisions.

### ⚙️ Configuration RH
- Informations de l'organisation (nom, ville, signataires...), jours fériés, signature électronique du DRH (pad de signature).

---

## Rôles

| Rôle            | Accès                                                                 |
|-----------------|------------------------------------------------------------------------|
| **Assistant RH**| Gestion quotidienne : personnel, contrats, congés, absences, stagiaires, demandes |
| **DRH**         | Tout ce que fait l'Assistant RH + validation/rejet des demandes, tableau de bord de pilotage, historique des décisions |

Le rôle est défini par le champ `role` (`assistant_rh` ou `drh`) sur le modèle `User`.

---

## Installation

### Prérequis
- PHP ≥ 8.3 avec les extensions habituelles de Laravel
- Composer
- MySQL 8+
- Node.js (facultatif, seulement si vous touchez aux assets Vite/Tailwind)

### Étapes

```bash
# 1. Cloner / copier le projet, puis installer les dépendances PHP
composer install

# 2. Copier le fichier d'environnement et générer la clé d'application
cp .env.example .env
php artisan key:generate

# 3. Configurer la base de données dans .env
#    DB_DATABASE=rh_plus
#    DB_USERNAME=...
#    DB_PASSWORD=...

# 4. Créer la base puis exécuter les migrations
php artisan migrate

# 5. Lier le stockage public (photos, signatures, documents stagiaires)
php artisan storage:link

# 6. (Optionnel) Compiler les assets si nécessaire
npm install && npm run build

# 7. Créer le premier compte utilisateur (DRH ou Assistant RH)
php artisan tinker
>>> \App\Models\User::create([
        'name' => 'Nom Prénom',
        'email' => 'email@exemple.com',
        'password' => bcrypt('mot-de-passe'),
        'role' => 'drh', // ou 'assistant_rh'
    ]);

# 8. Lancer le serveur de développement
php artisan serve
```

---

## Configuration

### `.env` — variables clés

```env
APP_NAME="RH Plus"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rh_plus
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

### Table `config_rh`

Les informations affichées sur les documents officiels (nom de l'organisation, ville, signataires, adresse de l'employeur, coefficient de bonification, modèles de numéros de référence...) sont stockées en base et modifiables depuis **Paramètres → Configuration RH**, sans toucher au code. Clés notables :

| Clé | Rôle |
|---|---|
| `organisation`, `ville` | En-têtes des documents |
| `contrat_employeur_nom`, `contrat_employeur_adresse` | Bloc "employeur" des contrats |
| `contrat_representant_nom/titre`, `contrat_delegataire_nom/titre` | Signataires des contrats |
| `coefficient_bonification` | Coefficient appliqué à 58 ans (défaut `2.3`) |
| `reference_echelon`, `reference_bonification` | Modèles de numéro de référence des lettres d'avancement |

### Images d'en-tête

Les documents officiels utilisent deux images situées dans :
```
public/images/letterhead/logo_archidiocese.jpeg
public/images/letterhead/photo_eveque.jpeg
```

---

## Tâches planifiées (cron)

La vérification automatique des avancements d'échelon et des bonifications à 58 ans est programmée pour s'exécuter **tous les jours à 6h**. Pour que cela fonctionne en production, le scheduler Laravel doit être actif sur le serveur :

```bash
# Crontab du serveur
* * * * * cd /chemin/vers/le/projet && php artisan schedule:run >> /dev/null 2>&1
```

Sans cron configuré, la vérification peut toujours être déclenchée manuellement via le bouton **« Vérifier les avancements »** sur la page Personnel, ou en ligne de commande :

```bash
php artisan avancements:traiter
```

---

## Structure du projet

```
app/
  Console/Commands/       Commandes artisan (avancements:traiter)
  Http/Controllers/       Contrôleurs (Personnel, Contrat, Conge, Absence, Demande,
                           Stagiaire, Avancement, Rapport, Dashboard, Drh, ConfigRh...)
  Models/                 Personnel, Contrat, Conge, Absence, Demande, Stagiaire,
                           Avancement, GrilleSalariale, PersonnelEnfant,
                           PersonnelConjoint, ConfigRh, JourFerie, User...
  Services/                CalendrierService (jours ouvrables, soldes, maternité),
                           AvancementService (échelon / bonification),
                           DocumentService

database/migrations/       Historique des migrations (voir ci-dessous)
resources/views/
  personnel/                Fiches, formulaires, liste, anciens travailleurs
  contrats/                 Formulaires + documents imprimables (CDD/CDI/Prestation)
  avancements/               Lettres d'avancement / bonification
  conges/, absences/, demandes/   Formulaires + documents imprimables
  stagiaires/                Gestion des stagiaires
  rapports/                  Rapport situation actuelle + rapport historique
  dashboard/, drh/            Tableaux de bord
  layouts/app.blade.php       Layout principal (sidebar, topbar)

public/css/app.css          Feuille de style unique de l'application
public/images/letterhead/   Images d'en-tête des documents officiels
```

---

## Notes et limites connues

- Les documents officiels sont des pages HTML imprimables (bouton **Imprimer / Sauvegarder en PDF**) plutôt que des PDF générés côté serveur — aucune dépendance type `dompdf` n'est nécessaire.
- Les montants dans les lettres sont affichés en chiffres (pas en toutes lettres).
- Le numéro de référence des lettres d'avancement est généré automatiquement (compteur par type/année) selon un modèle par défaut, personnalisable via `config_rh`.
- La carte "Solde de congés" du formulaire de création de congé n'est pas encore branchée à une route dédiée.

---

## Licence

Projet interne — Institutions Sanitaires Diocésaines de Cotonou.


# 👤 Auteur

**AZON Yélian Joanès**

Étudiant en Génie Logiciel — IFRI

Développeur Full Stack Laravel • React • Node.js

---

⭐ N'hésitez pas à mettre une étoile au projet si vous le trouvez utile.