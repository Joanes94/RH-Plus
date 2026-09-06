# TEKTON SIRH — RH Plus Central

Système d'Information des Ressources Humaines et de Gestion de la Paie multi-centres conçu pour la **Direction Diocésaine des Institutions Sanitaires (DDIS)** de l'Archidiocèse de Cotonou et ses formations sanitaires rattachées (CSVH Saint Luc, CSVH Saint Jean Cotonou, CSVH Saint Joseph Sô-Tchanhoué, CSVH Sêyon, CSVH Padre Pio de Glo, CSVH Saint Jean de Maria-Gléta, CAFSC, DDIS).

Développé avec **Laravel 13** (PHP 8.3+), architecture multi-centres avec gestion locale autonome et supervision centrale.

---

## 📌 Sommaire

- [Architecture & Principes](#architecture--principes)
- [Stack Technique](#stack-technique)
- [Rôles & Habilitations](#rôles--habilitations)
- [Formations Sanitaires Gérées](#formations-sanitaires-gérées)
- [Modules Fonctionnels](#modules-fonctionnels)
- [Installation & Déploiement](#installation--déploiement)
- [Configuration Clé](#configuration-clé)
- [Tâches Planifiées (Cron)](#tâches-planifiées-cron)
- [Auteur & Contact](#auteur--contact)

---

## 🏛️ Architecture & Principes

- **Multi-centres étanche** : chaque centre sanitaire opère sur ses propres agents, contrats, congés et paies, tandis que la direction diocésaine (DDIS, DDRH, CRH) bénéficie d'une vision consolidée en temps réel.
- **Conformité droit du travail & conventions béninoises** : barèmes de paie, ITS, CNSS, conventions collectives du secteur de la santé, accord d'établissement.
- **Dématérialisation complète** : génération automatique d'actes administratifs, décisions d'avancement signées, contrats et bulletins de paie téléchargeables à l'unité ou en archive ZIP par centre et par période.

---

## 🛠️ Stack Technique

| Composant | Détail |
|---|---|
| **Backend** | PHP 8.3+ / Laravel 13 |
| **Base de données** | MySQL 8+ |
| **Génération PDF & Documents** | `barryvdh/laravel-dompdf` + formats HTML imprimables haute fidélité |
| **Compression** | Extension PHP `ZipArchive` (téléchargement groupé des bulletins par centre) |
| **Envois Emails** | Laravel Mailer (`payroll`) pour transmission des fiches de paie aux salariés |
| **Interface Frontend** | Blade, CSS natif moderne responsive, Google Fonts (*Plus Jakarta Sans*, *Fraunces*, *DM Sans*) |
| **Sécurité** | Authentification par session, protection CSRF, hachage bcrypt, contrôle d'accès strict par centre et rôle |

---

## 👥 Rôles & Habilitations

L'application distingue deux grandes familles d'utilisateurs :

### 🌐 Rôles Globaux (Supervision Diocésaine)
1. **Conseiller RH (CRH)** : Super-administrateur, gestion globale multi-centres, configuration des centres et des paramètres généraux.
2. **Directeur DDIS** : Supervision générale diocésaine, consultation et suivi stratégique.
3. **Directeur DRH (DDRH)** : Pilotage global des carrières et de la politique des ressources humaines.

### 🏥 Rôles Locaux (Par Centre Sanitaire)
4. **DRH Centre** : Gestion complète du personnel, contrats, validation des congés, demandes et opérations RH du centre.
5. **Directeur de Centre** : Direction de l'établissement sanitaire, validation/approbation (en autonomie ou en lien avec le DRH dédié).
6. **Assistant RH** : Saisie et gestion administrative opérationnelle du centre (agents, dossiers, absences, congés, pièces jointes).

---

## 🏥 Formations Sanitaires Gérées

- **CSVH Saint Luc**
- **CSVH Saint Jean (Cotonou)**
- **CSVH Saint Joseph (Sô-Tchanhoué)**
- **CSVH Sêyon**
- **CSVH Padre Pio de Glo**
- **CSVH Saint Jean de Maria-Gléta**
- **CAFSC**
- **DDIS (Direction Diocésaine)**

Chaque centre dispose de son propre logo, en-tête officiel, signature de direction et paramètres personnalisés pour les actes et bulletins.

---

## 📦 Modules Fonctionnels

### 1. 👤 Gestion du Personnel & Dossier Collaborateur
- Fiche individuelle complète (état civil, contact, situation matrimoniale, corporation, service, centre).
- Gestion des ayants droit (conjoints et enfants avec calcul automatique de majorité à 21 ans).
- Import massif Excel avec modèle téléchargeable.
- Historique des affectations inter-centres et gestion des anciens travailleurs (archivage avec traçabilité et restauration).

### 2. 📄 Contrats de Travail & Actes
- Types supportés : **CDI**, **CDD**, **Prestation de soins**, **Stage**, **Vacation**.
- Calcul automatique des échéances : limite d'âge de retraite (60 ans pour CDI) ou fin de terme.
- Génération et impression des contrats conformes avec photos et signatures officielles.

### 3. 💳 Paie & Traitements Salariaux
- Moteur de paie automatisé conforme à la réglementation béninoise (salaire de base, primes, heures/honoraires de garde, cotisations CNSS, retenues ITS, acomptes).
- Ouverture et clôture mensuelle des périodes de paie par centre.
- **Bulletins de paie PDF** individuels générés fidèlement aux maquettes officielles (avec variantes par établissement, ex: *Saint Jean*).
- **Export ZIP groupé** : téléchargement en un clic de l'ensemble des bulletins PDF d'un centre pour une période donnée.
- **Envoi groupé par email** des bulletins aux salariés disposant d'une adresse email.
- Génération des états récapitulatifs : **Livre de paie**, **Registre de paie**, **États de virement bancaire** (BOA, Ecobank, etc.), déclarations **CNSS** et **ITS**.

### 4. 📈 Avancements de Carrière & Grille Salariale
- Grille salariale hiérarchique intégrée (catégories E1→E6, M1→M3, C1→C2 × 11 échelons).
- Traitement automatique des avancements d'échelon biennaux.
- Bonification à 58 ans (Article 88 de l'accord d'établissement) avec génération des décisions correspondantes.

### 5. 🏖️ Congés, Absences & Demandes Administratives
- Suivi des congés (administratifs, techniques, maternité 98 jours).
- Workflow de soumission, examen et validation par les responsables habilités.
- Édition automatique des titres de congé, attestations de travail, autorisations d'absence et reprises de service.

### 6. 🎓 Stagiaires
- Enregistrement des conventions de stage, suivi de la période d'immersion, fin de cycle et archivage des attestations.

### 7. 📊 Tableaux de Bord & Reporting
- Tableaux de bord personnalisés selon le rôle et le centre de rattachement.
- Rapports RH dynamiques en situation actuelle et historique mensuel/annuel avec export PDF.

---

## 🚀 Installation & Déploiement

### Prérequis
- PHP ≥ 8.3 (extensions : `pdo_mysql`, `mbstring`, `zip`, `gd`, `xml`, `curl`)
- Composer
- Serveur MySQL 8+

### Étapes d'installation

```bash
# 1. Cloner le projet
git clone https://github.com/votre-compte/rh_plus_central2.git
cd rh_plus_central2

# 2. Installer les dépendances PHP
composer install

# 3. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Configurer la base de données dans le fichier .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=rh_plus_central
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Exécuter les migrations et les seeders
php artisan migrate --seed

# 6. Créer le lien symbolique vers le stockage public
php artisan storage:link

# 7. Lancer le serveur local
php artisan serve
```

---

## ⚙️ Configuration Clé

### Paramètres RH & Centres
Les données administratives, barèmes et en-têtes sont configurables via l'interface d'administration sous **Paramètres → Configuration RH** et **Gestion des Centres**.

### Envois d'Emails pour les Bulletins
Dans le fichier `.env`, configurez le mailer dédié `payroll` :

```env
PAYROLL_MAIL_MAILER=smtp
PAYROLL_MAIL_HOST=smtp.exemple.com
PAYROLL_MAIL_PORT=587
PAYROLL_MAIL_USERNAME=ddis@tekton.com
PAYROLL_MAIL_PASSWORD=votre_mot_de_passe
PAYROLL_MAIL_ENCRYPTION=tls
```

---

## ⏰ Tâches Planifiées (Cron)

Pour activer le traitement automatique des avancements et des alertes d'échéance :

```bash
# Ajouter dans le crontab du serveur (chaque minute)
* * * * * cd /chemin/vers/rh_plus_central2 && php artisan schedule:run >> /dev/null 2>&1
```

Exécution manuelle en invite de commande :
```bash
php artisan avancements:traiter
```

---

## 👤 Auteur & Contact

**AZON Yélian Joanès**  
Étudiant en Génie Logiciel — IFRI  
Développeur Full Stack Web & Mobile (Laravel • React • Node.js)

*Projet institutionnel dédié à la Direction Diocésaine des Institutions Sanitaires (DDIS) — Archidiocèse de Cotonou.*