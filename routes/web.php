<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\DriveExplorerController;
use App\Http\Controllers\Admin\EmailSubmissionController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\GFormSyncController;
use App\Http\Controllers\Admin\GoogleAuthController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\Admin\SubmissionHrgaController;
use App\Http\Controllers\Admin\SubmissionParamedicController;
use App\Http\Controllers\Admin\SubmissionTodController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PortalDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortalController::class, 'home'])->name('portal.index');
Route::get('/index.php', [PortalController::class, 'home'])->name('portal.index.php');
Route::get('/portal/forms', [PortalController::class, 'forms'])->name('portal.forms');
Route::get('/portal/forms.php', [PortalController::class, 'forms'])->name('portal.forms.php');
Route::get('/portal.php', [PortalController::class, 'forms'])->name('portal.legacy.forms');
Route::get('/portal/simper', [PortalController::class, 'simper'])->name('portal.simper');
Route::get('/portal/documents/{code}', [PortalDocumentController::class, 'show'])
    ->where('code', '[A-Za-z0-9._-]+')
    ->name('portal.documents.show');
Route::get('/portal/documents/{code}/file', [PortalDocumentController::class, 'file'])
    ->where('code', '[A-Za-z0-9._-]+')
    ->name('portal.documents.file');

Route::prefix('admin')->middleware(['legacy.admin.headers'])->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::get('/login.php', [AuthController::class, 'showLogin'])->name('admin.login.php');
    Route::post('/login.php', [AuthController::class, 'login'])->name('admin.login.submit.php');
    Route::post('/quick-login', [AuthController::class, 'autoLogin'])->name('admin.quick-login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::post('/logout.php', [AuthController::class, 'logout'])->name('admin.logout.php');
    Route::post('/sync/gform', [GFormSyncController::class, 'sync'])->name('sync.gform');

    Route::middleware(['legacy.admin.auth', 'legacy.password.rotation'])->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard.php', [DashboardController::class, 'index'])->name('admin.dashboard.php');

        Route::middleware(['simper.role:admin,she'])->group(function (): void {
            Route::get('/forms', [FormController::class, 'index'])->name('admin.forms');
            Route::get('/forms.php', [FormController::class, 'index'])->name('admin.forms.php');
            Route::post('/forms', [FormController::class, 'store'])->name('admin.forms.store');
            Route::post('/forms.php', [FormController::class, 'store'])->name('admin.forms.store.php');
            Route::post('/forms/{id}/update', [FormController::class, 'update'])->name('admin.forms.update');
            Route::post('/forms/{id}/archive', [FormController::class, 'archive'])->name('admin.forms.archive');

            Route::get('/monitoring', [MonitoringController::class, 'index'])->name('admin.monitoring');
            Route::get('/monitoring.php', [MonitoringController::class, 'index'])->name('admin.monitoring.php');
            Route::get('/monitoring/forms/{id}/open', [MonitoringController::class, 'openMonitoringForm'])
                ->where('id', '[0-9]+')
                ->name('admin.monitoring.forms.open');

            Route::get('/audit-log', [AuditLogController::class, 'index'])->name('admin.audit-log');
            Route::get('/audit-log.php', [AuditLogController::class, 'index'])->name('admin.audit-log.php');

            Route::get('/documents', [DocumentController::class, 'index'])->name('admin.documents');
            Route::get('/documents.php', [DocumentController::class, 'index'])->name('admin.documents.php');
            Route::post('/documents/meta', [DocumentController::class, 'updateMeta'])->name('admin.documents.meta');
            Route::post('/documents/meta.php', [DocumentController::class, 'updateMeta'])->name('admin.documents.meta.php');
            Route::post('/documents/revisions', [DocumentController::class, 'uploadRevision'])->name('admin.documents.revisions.store');
            Route::post('/documents/revisions.php', [DocumentController::class, 'uploadRevision'])->name('admin.documents.revisions.store.php');

            Route::middleware(['legacy.admin.email-workflow'])->group(function (): void {
                Route::get('/email-submissions', [EmailSubmissionController::class, 'index'])->name('admin.email-submissions');
                Route::get('/email-submissions.php', [EmailSubmissionController::class, 'index'])->name('admin.email-submissions.php');
                Route::post('/email-submissions', [EmailSubmissionController::class, 'storeSubmission'])->name('admin.email-submissions.store');
                Route::post('/email-submissions.php', [EmailSubmissionController::class, 'storeSubmission'])->name('admin.email-submissions.store.php');
                Route::post('/email-submissions/templates', [EmailSubmissionController::class, 'storeTemplate'])->name('admin.email-submissions.templates.store');
                Route::post('/email-submissions/templates.php', [EmailSubmissionController::class, 'storeTemplate'])->name('admin.email-submissions.templates.store.php');
                Route::post('/email-submissions/{id}/send', [EmailSubmissionController::class, 'send'])
                    ->where('id', '[0-9]+')
                    ->name('admin.email-submissions.send');
                Route::post('/email-submissions/{id}/send.php', [EmailSubmissionController::class, 'send'])
                    ->where('id', '[0-9]+')
                    ->name('admin.email-submissions.send.php');
            });
        });

        Route::middleware(['simper.role:admin,she,hrga,subcon,tod'])->group(function (): void {
            Route::get('/google/auth', [GoogleAuthController::class, 'auth'])->name('admin.google.auth');
            Route::get('/google/callback', [GoogleAuthController::class, 'callback'])->name('admin.google.callback');
        });

        // Shared Services
        Route::middleware(['simper.role'])->group(function (): void {
            Route::get('/drive-explorer', [DriveExplorerController::class, 'index'])->name('admin.drive-explorer');
        });

        // Unified Submission Monitoring System
        Route::middleware(['simper.role'])->group(function (): void {
            Route::get('/submissions', [SubmissionController::class, 'index'])->name('admin.submissions.index');
            Route::get('/submissions/create', [SubmissionController::class, 'create'])->name('admin.submissions.create');
            Route::post('/submissions', [SubmissionController::class, 'store'])->name('admin.submissions.store');
            Route::get('/submissions/{id}', [SubmissionController::class, 'show'])->name('admin.submissions.show');
            Route::post('/submissions/{id}/update', [SubmissionController::class, 'update'])->name('admin.submissions.update');
            Route::get('/submissions/{id}/download/{fileId}', [SubmissionController::class, 'downloadFile'])->name('admin.submissions.download');
            Route::post('/submissions/{id}/sync-drive-folder', [SubmissionController::class, 'syncDriveFolder'])->name('admin.submissions.sync-drive-folder');
            Route::post('/submissions/{id}/link-drive', [SubmissionController::class, 'linkDriveFile'])->name('admin.submissions.link-drive');
        });

        Route::middleware(['simper.role:she,admin'])->group(function (): void {
            Route::post('/submissions/{id}/approve', [SubmissionController::class, 'approve'])->name('admin.submissions.approve');
            Route::post('/submissions/{id}/reject', [SubmissionController::class, 'reject'])->name('admin.submissions.reject');
            Route::delete('/submissions/{id}', [SubmissionController::class, 'destroy'])->name('admin.submissions.destroy');
        });

        Route::middleware(['simper.role'])->group(function (): void {
            Route::delete('/submissions/{id}/file/{fileId}', [SubmissionController::class, 'deleteFile'])->name('admin.submissions.delete-file');
        });

        Route::middleware(['simper.role:hrga,subcon'])->group(function (): void {
            Route::post('/submissions/{id}/upload-hrga', [SubmissionHrgaController::class, 'upload'])->name('admin.submissions.upload-hrga');
            Route::post('/submissions/{id}/forward-hrga', [SubmissionController::class, 'forwardHrga'])->name('admin.submissions.forward-hrga');
        });

        Route::middleware(['simper.role:tod,admin'])->group(function (): void {
            Route::post('/submissions/{id}/upload-tod', [SubmissionTodController::class, 'upload'])->name('admin.submissions.upload-tod');
            Route::post('/submissions/{id}/forward-tod', [SubmissionController::class, 'forwardTod'])->name('admin.submissions.forward-tod');
        });

        Route::middleware(['simper.role:paramedic'])->group(function (): void {
            Route::post('/submissions/{id}/paramedic-verify', [SubmissionParamedicController::class, 'verify'])->name('admin.submissions.paramedic-verify');
            Route::post('/submissions/{id}/paramedic-reject', [SubmissionParamedicController::class, 'reject'])->name('admin.submissions.paramedic-reject');
            Route::post('/submissions/{id}/paramedic-feedback', [SubmissionParamedicController::class, 'saveFeedback'])->name('admin.submissions.paramedic-feedback');
        });

        // User Management (RBAC) System
        Route::middleware(['simper.role:admin'])->group(function (): void {
            Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
            Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
            Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
            Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
            Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        });

        Route::get('/change-password', [PasswordController::class, 'edit'])->name('admin.change-password');
        Route::post('/change-password', [PasswordController::class, 'update'])->name('admin.change-password.update');
        Route::get('/change-password.php', [PasswordController::class, 'edit'])->name('admin.change-password.php');
        Route::post('/change-password.php', [PasswordController::class, 'update'])->name('admin.change-password.update.php');
    });
});
