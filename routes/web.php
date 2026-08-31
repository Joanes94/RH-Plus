<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\AvancementController;
use App\Http\Controllers\StagiaireController;
use App\Http\Controllers\StagiaireDocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\DemandeController;
use App\Http\Controllers\ConfigRhController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DrhController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\EvaluationController; // 👈 AJOUTÉ
use App\Http\Controllers\CentreController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\PayPeriodController;
use App\Http\Controllers\PaySlipController;
use App\Http\Controllers\PayAdjustmentController;
use Illuminate\Support\Facades\Route;

// ─── Routes publiques ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/',                          [AuthController::class, 'showLogin'])->name('home');
    Route::get('/login',                     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',                    [AuthController::class, 'login'])->name('login.post');

    // Réinitialisation de mot de passe
    Route::get('/forgot-password',           [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
    Route::post('/forgot-password',          [PasswordResetController::class, 'sendResetLink'])->name('password.send');
    Route::get('/reset-password/{token}',    [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password',           [PasswordResetController::class, 'resetPassword'])->name('password.reset');
});

// ─── Routes authentifiées ────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/centre', [DashboardController::class, 'dashboardParCentre'])->name('dashboard.centre');
    Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

    // ── Gestion des centres (CRH uniquement) ─────────────────────────────────
    Route::middleware('role:crh')->prefix('centres')->name('centres.')->group(function () {
        Route::get('/', [CentreController::class, 'index'])->name('index');
        Route::post('/', [CentreController::class, 'store'])->name('store');
        Route::patch('/reorder', [CentreController::class, 'reorder'])->name('reorder');
        Route::patch('/{centre}/toggle-status', [CentreController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{centre}/move/{direction}', [CentreController::class, 'move'])->name('move');
        Route::put('/{centre}', [CentreController::class, 'update'])->name('update');
    });

    // ── Gestion des utilisateurs (CRH uniquement) ────────────────────────────
    Route::middleware('role:crh')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::get('/{user}/transferer', [UserManagementController::class, 'showTransferer'])->name('transferer');
        Route::post('/{user}/transferer', [UserManagementController::class, 'transferer'])->name('transferer.store');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });

    // ── Actions CRH exclusives sur le personnel ──────────────────────────────
    Route::middleware('role:crh')->group(function () {
        Route::get('/personnel/{personnel}/desactiver', [PersonnelController::class, 'showDesactiver'])->name('personnel.desactiver');
        Route::post('/personnel/{personnel}/desactiver', [PersonnelController::class, 'desactiver'])->name('personnel.desactiver.store');
        Route::post('/personnel/{personnel}/reactiver', [PersonnelController::class, 'reactiver'])->name('personnel.reactiver');
        Route::get('/personnel/{personnel}/transferer', [PersonnelController::class, 'showTransferer'])->name('personnel.transferer');
        Route::post('/personnel/{personnel}/transferer', [PersonnelController::class, 'transferer'])->name('personnel.transferer.store');
        Route::post('/personnel/{personnel}/passer-cdi', [PersonnelController::class, 'passerCDI'])->name('personnel.passer-cdi');
    });

    // ── Historique personnel ──────────────────────────────────────────────────
    Route::get('/personnel/{personnel}/historique', [PersonnelController::class, 'historique'])->name('personnel.historique');

    // ── Profil ────────────────────────────────────────────────────────────────
    Route::get('/profile',          [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit',     [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile',       [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Stagiaires ────────────────────────────────────────────────────────────
    Route::get('/stagiaires',                              [StagiaireController::class, 'index'])->name('stagiaires.index');
    Route::get('/stagiaires/create',                       [StagiaireController::class, 'create'])->name('stagiaires.create');
    Route::post('/stagiaires',                             [StagiaireController::class, 'store'])->name('stagiaires.store');
    // Doit être déclarée avant /stagiaires/{stagiaire} pour éviter le conflit de routing
    Route::get('/stagiaires/demandes',                     [StagiaireDocumentController::class, 'demandes'])->name('stagiaires.demandes.index');
    Route::get('/stagiaires/{stagiaire}',                  [StagiaireController::class, 'show'])->name('stagiaires.show');
    Route::get('/stagiaires/{stagiaire}/edit',             [StagiaireController::class, 'edit'])->name('stagiaires.edit');
    Route::put('/stagiaires/{stagiaire}',                  [StagiaireController::class, 'update'])->name('stagiaires.update');
    Route::delete('/stagiaires/{stagiaire}',               [StagiaireController::class, 'destroy'])->name('stagiaires.destroy');

    // ── Documents des stagiaires ──────────────────────────────────────────────
    Route::prefix('stagiaires/{stagiaire}/documents')->name('stagiaires.documents.')->group(function () {
    Route::get('/', [StagiaireDocumentController::class, 'choisir'])->name('choisir');
    
    // Soumission (assistant RH)
    Route::post('/autorisation', [StagiaireDocumentController::class, 'autorisation'])->name('autorisation');
    Route::post('/attestation', [StagiaireDocumentController::class, 'attestation'])->name('attestation');
    Route::get('/attente/{document}', [StagiaireDocumentController::class, 'attente'])->name('attente');
    
    // Visualisation (DRH)
    Route::get('/{document}', [StagiaireDocumentController::class, 'show'])->name('show');
    
    // Actions DRH
    Route::post('/{document}/approuver', [StagiaireDocumentController::class, 'approuver'])->name('approuver')->middleware('role:approver');
    Route::post('/{document}/rejeter', [StagiaireDocumentController::class, 'rejeter'])->name('rejeter')->middleware('role:approver');
    
    // PDF (après approbation)
    Route::get('/{document}/pdf', [StagiaireDocumentController::class, 'pdf'])->name('pdf');
    });

    // ── Évaluations des stagiaires (avec workflow DRH) ──────────────────────
    Route::prefix('evaluations')->name('evaluations.')->group(function () {
        Route::get('/', [EvaluationController::class, 'index'])->name('index');
        Route::get('/create', [EvaluationController::class, 'create'])->name('create');
        Route::post('/', [EvaluationController::class, 'store'])->name('store');
        Route::get('/{evaluation}', [EvaluationController::class, 'show'])->name('show');
        Route::get('/{evaluation}/edit', [EvaluationController::class, 'edit'])->name('edit');
        Route::put('/{evaluation}', [EvaluationController::class, 'update'])->name('update');
        Route::delete('/{evaluation}', [EvaluationController::class, 'destroy'])->name('destroy');

        // Actions DRH uniquement
        Route::post('/{evaluation}/approuver', [EvaluationController::class, 'approuver'])->name('approuver')->middleware('role:approver');
        Route::post('/{evaluation}/rejeter', [EvaluationController::class, 'rejeter'])->name('rejeter')->middleware('role:approver');

        // PDF
        Route::get('/{evaluation}/document', [EvaluationController::class, 'document'])->name('document');
    });

    // ── Personnel — CRUD ouvert aux deux rôles ────────────────────────────────
    Route::get('/personnel',                              [PersonnelController::class, 'index'])->name('personnel.index');
    Route::get('/personnel/create',                       [PersonnelController::class, 'create'])->name('personnel.create')->middleware('role:crh');
    Route::post('/personnel',                             [PersonnelController::class, 'store'])->name('personnel.store')->middleware('role:crh');
    Route::get('/personnel/import',                       [PersonnelController::class, 'importForm'])->name('personnel.import.form')->middleware('role:crh');
    Route::post('/personnel/import',                      [PersonnelController::class, 'import'])->name('personnel.import')->middleware('role:crh');
    Route::get('/personnel/template',                     [PersonnelController::class, 'downloadTemplate'])->name('personnel.template');
    Route::get('/personnel/anciens',                      [PersonnelController::class, 'anciens'])->name('personnel.anciens');
    Route::get('/personnel/{personnel}',                  [PersonnelController::class, 'show'])->name('personnel.show');
    Route::get('/personnel/{personnel}/edit',             [PersonnelController::class, 'edit'])->name('personnel.edit');
    Route::put('/personnel/{personnel}',                  [PersonnelController::class, 'update'])->name('personnel.update');
    Route::post('/personnel/{personnel}/archiver',        [PersonnelController::class, 'archiver'])->name('personnel.archiver');
    Route::post('/personnel/{personnel}/restaurer',       [PersonnelController::class, 'restaurer'])->name('personnel.restaurer');
    Route::post('/personnel/{personnel}/affecter',        [PersonnelController::class, 'affecter'])->name('personnel.affecter');

    // ── Contrats — import Excel/CSV en masse ──────────────────────────────────
    Route::get('/contrats/import',                        [ContratController::class, 'importForm'])->name('contrats.import.form');
    Route::post('/contrats/import',                        [ContratController::class, 'import'])->name('contrats.import');
    Route::get('/contrats/import/modele',                  [ContratController::class, 'downloadTemplate'])->name('contrats.template');

    // ── Contrats — assignation en masse par sélection d'agents ───────────────
    Route::get('/contrats/assigner',  [ContratController::class, 'assignMultipleForm'])->name('contrats.assign-multiple.form');
    Route::post('/contrats/assigner', [ContratController::class, 'assignMultiple'])->name('contrats.assign-multiple');

    // ── Contrats (plusieurs par personnel) ────────────────────────────────────
    Route::prefix('personnel/{personnel}/contrats')->name('contrats.')->scopeBindings()->group(function () {
        Route::get('/create',                    [ContratController::class, 'create'])->name('create');
        Route::post('/',                          [ContratController::class, 'store'])->name('store');
        Route::get('/{contrat}/edit',             [ContratController::class, 'edit'])->name('edit');
        Route::put('/{contrat}',                  [ContratController::class, 'update'])->name('update');
        Route::delete('/{contrat}',               [ContratController::class, 'destroy'])->name('destroy');
        Route::get('/{contrat}/document',         [ContratController::class, 'document'])->name('document');
    });

    // ── Congés — création/modification ouverte aux deux rôles ────────────────
    Route::get('/conges/calcul-date-fin',                 [CongeController::class, 'calculerDateFin'])->name('conges.calcul-date-fin');
    Route::get('/conges',                                 [CongeController::class, 'index'])->name('conges.index');
    Route::get('/conges/create',                          [CongeController::class, 'create'])->name('conges.create');
    Route::post('/conges',                                [CongeController::class, 'store'])->name('conges.store');
    Route::get('/conges/{conge}',                         [CongeController::class, 'show'])->name('conges.show');
    Route::get('/conges/{conge}/document',                [CongeController::class, 'document'])->name('conges.document');
    Route::get('/conges/{conge}/edit',                    [CongeController::class, 'edit'])->name('conges.edit');
    Route::put('/conges/{conge}',                         [CongeController::class, 'update'])->name('conges.update');
    Route::delete('/conges/{conge}',                      [CongeController::class, 'destroy'])->name('conges.destroy');
    // Approbation : DRH seulement
    Route::post('/conges/{conge}/approuver',              [CongeController::class, 'approuver'])->name('conges.approuver')->middleware('role:approver');
    Route::post('/conges/{conge}/rejeter',                [CongeController::class, 'rejeter'])->name('conges.rejeter')->middleware('role:approver');

    // ── Absences ──────────────────────────────────────────────────────────────
    Route::get('/absences',                               [AbsenceController::class, 'index'])->name('absences.index');
    Route::get('/absences/calcul-details',                 [AbsenceController::class, 'calculDetails'])->name('absences.calcul-details');
    Route::get('/absences/create',                        [AbsenceController::class, 'create'])->name('absences.create');
    Route::post('/absences',                              [AbsenceController::class, 'store'])->name('absences.store');
    Route::get('/absences/{absence}',                     [AbsenceController::class, 'show'])->name('absences.show');
    Route::get('/absences/{absence}/document',            [AbsenceController::class, 'document'])->name('absences.document');
    Route::get('/absences/{absence}/edit',                [AbsenceController::class, 'edit'])->name('absences.edit');
    Route::put('/absences/{absence}',                     [AbsenceController::class, 'update'])->name('absences.update');
    Route::delete('/absences/{absence}',                  [AbsenceController::class, 'destroy'])->name('absences.destroy');
    Route::post('/absences/{absence}/approuver',          [AbsenceController::class, 'approuver'])->name('absences.approuver')->middleware('role:approver');
    Route::post('/absences/{absence}/rejeter',            [AbsenceController::class, 'rejeter'])->name('absences.rejeter')->middleware('role:approver');

    // ── Demandes ──────────────────────────────────────────────────────────────
    Route::get('/demandes',                               [DemandeController::class, 'index'])->name('demandes.index');
    Route::get('/demandes/create',                        [DemandeController::class, 'create'])->name('demandes.create');
    Route::post('/demandes',                              [DemandeController::class, 'store'])->name('demandes.store');
    Route::get('/demandes/{demande}',                     [DemandeController::class, 'show'])->name('demandes.show');
    Route::get('/demandes/{demande}/document',            [DemandeController::class, 'document'])->name('demandes.document');
    Route::get('/demandes/{demande}/edit',                [DemandeController::class, 'edit'])->name('demandes.edit');
    Route::put('/demandes/{demande}',                     [DemandeController::class, 'update'])->name('demandes.update');
    Route::delete('/demandes/{demande}',                  [DemandeController::class, 'destroy'])->name('demandes.destroy');
    Route::post('/demandes/{demande}/approuver',          [DemandeController::class, 'approuver'])->name('demandes.approuver')->middleware('role:approver');
    Route::post('/demandes/{demande}/rejeter',            [DemandeController::class, 'rejeter'])->name('demandes.rejeter')->middleware('role:approver');

    // ── Rapports ──────────────────────────────────────────────────────────────
    Route::get('/rapports/personnel',                     [RapportController::class, 'personnel'])->name('rapports.personnel');
    Route::get('/rapports/personnel/pdf',                 [RapportController::class, 'personnelPdf'])->name('rapports.personnel.pdf');
    Route::get('/rapports/absents',                        [RapportController::class, 'absents'])->name('rapports.absents');
    Route::get('/rapports/historique',                    [RapportController::class, 'historique'])->name('rapports.historique');
    Route::get('/rapports/historique/pdf',                [RapportController::class, 'historiquePdf'])->name('rapports.historique.pdf');

    // ── Avancements (échelon / bonification) ────────────────────────────────────
    Route::post('/avancements/verifier',                  [AvancementController::class, 'verifier'])->name('avancements.verifier');
    Route::post('/avancements/{personnel}/verifier',       [AvancementController::class, 'verifierPersonnel'])->name('avancements.verifier-personnel');
    Route::get('/avancements/{avancement}/document',       [AvancementController::class, 'document'])->name('avancements.document');
    Route::get('/avancements',                             [AvancementController::class, 'index'])->name('avancements.index');
    Route::post('/avancements/{avancement}/valider-crh',   [AvancementController::class, 'validerCRH'])->name('avancements.valider-crh');
    Route::post('/avancements/{avancement}/approuver',     [AvancementController::class, 'approuver'])->name('avancements.approuver');
    Route::post('/avancements/{avancement}/rejeter',       [AvancementController::class, 'rejeter'])->name('avancements.rejeter');


    // ── Notifications ────────────────────────────────────────────────────────
    Route::post('/notifications/{notification}/lue',      [NotificationController::class, 'marquerLue'])->name('notifications.lue');
    Route::post('/notifications/lues',                    [NotificationController::class, 'marquerToutesLues'])->name('notifications.lues');

    // ── DRH ───────────────────────────────────────────────────────────────────
    Route::get('/drh/tableau-de-bord',                    [DrhController::class, 'index'])->name('drh.dashboard')->middleware('role:drh,drh_centre,directeur_centre');
    Route::get('/drh/historique',                         [DrhController::class, 'historique'])->name('drh.historique')->middleware('role:drh,drh_centre,directeur_centre');

    // ── Configuration RH (DRH de centre, Directeurs de centre & CRH) ───────────
    Route::get('/config-rh',                              [ConfigRhController::class, 'index'])->name('config-rh.index')->middleware('role:drh,drh_centre,crh,directeur_centre');
    Route::post('/config-rh/save',                        [ConfigRhController::class, 'saveConfig'])->name('config-rh.save')->middleware('role:drh,drh_centre,crh,directeur_centre');
    Route::post('/config-rh/feries/import-fixes',         [ConfigRhController::class, 'importFixesBenin'])->name('config-rh.feries.import')->middleware('role:drh,drh_centre,crh,directeur_centre');
    Route::post('/config-rh/feries',                      [ConfigRhController::class, 'storeFerie'])->name('config-rh.feries.store')->middleware('role:drh,drh_centre,crh,directeur_centre');
    Route::delete('/config-rh/feries/{jourFerie}',        [ConfigRhController::class, 'destroyFerie'])->name('config-rh.feries.destroy')->middleware('role:drh,drh_centre,crh,directeur_centre');
    Route::post('/config-rh/signature-pad',               [ConfigRhController::class, 'saveSignaturePad'])->name('config-rh.signature-pad')->middleware('role:drh,drh_centre,crh,directeur_centre');

    // ── Configuration DDIS (Exclusive DDIS) ──────────────────────────────────
    Route::get('/config-ddis',                             [App\Http\Controllers\ConfigDdisController::class, 'index'])->name('config-ddis.index')->middleware('role:ddis,crh');
    Route::post('/config-ddis/save',                       [App\Http\Controllers\ConfigDdisController::class, 'saveConfig'])->name('config-ddis.save')->middleware('role:ddis,crh');
    Route::post('/config-ddis/signature-pad',              [App\Http\Controllers\ConfigDdisController::class, 'saveSignaturePad'])->name('config-ddis.signature-pad')->middleware('role:ddis,crh');

    // ── Gestion de la paie ────────────────────────────────────────────────────
    Route::prefix('paie')->group(function () {
        // Périodes de paie
        Route::get('/periodes',                           [PayPeriodController::class, 'index'])->name('pay-periods.index');
        Route::post('/periodes',                          [PayPeriodController::class, 'store'])->name('pay-periods.store');
        Route::get('/periodes/{payPeriod}',               [PayPeriodController::class, 'show'])->name('pay-periods.show');
        Route::post('/periodes/{payPeriod}/cloturer',      [PayPeriodController::class, 'cloture'])->name('pay-periods.cloturer');
        Route::post('/periodes/{payPeriod}/envoyer-email', [PayPeriodController::class, 'envoyerBulletinsEmail'])->name('pay-periods.envoyer-email');


        // Exports globaux de la période
        Route::get('/periodes/{payPeriod}/centres/{centre}/livre',      [PayPeriodController::class, 'livreDePaie'])->name('pay-periods.livre');
        Route::get('/periodes/{payPeriod}/centres/{centre}/registre',   [PayPeriodController::class, 'registreDePaie'])->name('pay-periods.registre');
        Route::get('/periodes/{payPeriod}/centres/{centre}/virements',  [PayPeriodController::class, 'virements'])->name('pay-periods.virements');
        Route::get('/periodes/{payPeriod}/centres/{centre}/cnss',       [PayPeriodController::class, 'declarationCnss'])->name('pay-periods.cnss');
        Route::get('/periodes/{payPeriod}/centres/{centre}/its',        [PayPeriodController::class, 'declarationIts'])->name('pay-periods.its');

        // Bulletins individuels
        Route::get('/bulletins/{paySlip}',                [PaySlipController::class, 'show'])->name('pay-slips.show');
        Route::put('/bulletins/{paySlip}',                [PaySlipController::class, 'update'])->name('pay-slips.update');
        Route::get('/bulletins/{paySlip}/pdf',            [PaySlipController::class, 'bulletin'])->name('pay-slips.pdf');
        Route::get('/bulletins/{paySlip}/solde-tout-compte', [PaySlipController::class, 'soldeToutCompte'])->name('pay-slips.solde-tout-compte');

        // Ajustements / Échéanciers salariaux
        Route::get('/ajustements',                        [PayAdjustmentController::class, 'index'])->name('pay-adjustments.index');
        Route::post('/ajustements',                       [PayAdjustmentController::class, 'store'])->name('pay-adjustments.store');
        Route::patch('/ajustements/{payAdjustment}/toggle',[PayAdjustmentController::class, 'toggleStatus'])->name('pay-adjustments.toggle');
        Route::delete('/ajustements/{payAdjustment}',     [PayAdjustmentController::class, 'destroy'])->name('pay-adjustments.destroy');
    });
});

// ── Fallback Media Storage pour Windows (Servir les photos et uploads si storage:link n'est pas créé)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (file_exists($fullPath)) {
        $mime = mime_content_type($fullPath) ?: 'image/jpeg';
        return response()->file($fullPath, ['Content-Type' => $mime]);
    }
    abort(404);
})->where('path', '.*')->name('storage.fallback');