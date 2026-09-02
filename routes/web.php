<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BadmintonMatchController;
use App\Http\Controllers\CollectiveRegistrationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JuriController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\PicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/lomba/{slug}', [HomeController::class, 'competitionDetail'])->name('competition.detail');
Route::get('/lomba/{slug}/spin-viewer', [HomeController::class, 'spinViewer'])->name('spin.viewer');
Route::get('/cek-status', [HomeController::class, 'checkStatus'])->name('check.status');
Route::get('/live-scoreboard/{slug?}', [HomeController::class, 'liveScoreboard'])->name('live.scoreboard');
Route::get('/badminton/scoreboard/{id?}', [BadmintonMatchController::class, 'scoreboard'])->name('badminton.scoreboard');
Route::get('/badminton/arena', [BadmintonMatchController::class, 'arenaScoreboard'])->name('badminton.arena');
Route::get('/badminton/umpire/{id}', [BadmintonMatchController::class, 'umpire'])->name('badminton.umpire');
Route::get('/badminton/matches/{id}/state', [BadmintonMatchController::class, 'apiState'])->name('badminton.api.state');
Route::get('/badminton/matches/{id}/stream', [BadmintonMatchController::class, 'streamState'])->name('badminton.api.stream');
Route::post('/badminton/matches/{id}/score', [BadmintonMatchController::class, 'apiScore'])->name('badminton.api.score');
Route::get('/badminton/api/active-courts', [BadmintonMatchController::class, 'apiActiveCourts'])->name('badminton.api.active_courts');
Route::get('/badminton/api/arena-stream', [BadmintonMatchController::class, 'arenaStream'])->name('badminton.api.arena_stream');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});
Route::get('/bukti-akun', [AuthController::class, 'showRegisterSuccess'])->name('register.success')->middleware('auth');
Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('user.password.update')->middleware('auth');
Route::any('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Peserta (Pendaftar / Kontingen) Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:peserta,superadmin'])->prefix('peserta')->name('peserta.')->group(function () {
    Route::get('/dashboard', [PesertaController::class, 'dashboard'])->name('dashboard');
    Route::get('/pendaftaran-saya', [PesertaController::class, 'myRegistrations'])->name('registrations');
    Route::get('/daftar/{slug}', [PesertaController::class, 'showRegisterCompetition'])->name('register.competition');
    Route::post('/daftar/{slug}', [PesertaController::class, 'storeRegistration'])->name('register.competition.store');
    Route::get('/pendaftaran/{id}', [PesertaController::class, 'showRegistrationDetail'])->name('registration.detail');
    Route::post('/pendaftaran/{id}/revisi', [PesertaController::class, 'updateRevision'])->name('registration.revision');
    Route::get('/pendaftaran/{id}/cetak-kartu', [PesertaController::class, 'printIdCard'])->name('print.idcard');
    Route::get('/pendaftaran/{id}/kartu', [PesertaController::class, 'printIdCard'])->name('card');

    // Collective Registration (Excel) & Invoices
    Route::get('/collective', [CollectiveRegistrationController::class, 'wizard'])->name('collective.wizard');
    Route::get('/collective/template', [CollectiveRegistrationController::class, 'downloadTemplate'])->name('collective.template');
    Route::post('/collective/parse', [CollectiveRegistrationController::class, 'parseExcel'])->name('collective.parse');
    Route::post('/collective/confirm', [CollectiveRegistrationController::class, 'confirmBatch'])->name('collective.confirm');
    Route::get('/invoices/{id}', [CollectiveRegistrationController::class, 'showInvoice'])->name('invoices.show');
    Route::post('/invoices/{id}/upload-proof', [CollectiveRegistrationController::class, 'uploadPaymentProof'])->name('invoices.upload');
});

/*
|--------------------------------------------------------------------------
| Dokumen Administrasi Cetak (Peserta & Panitia)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('dokumen')->name('document.')->group(function () {
    Route::get('/bukti-akun/{registration_id}', [DocumentController::class, 'printAccountProof'])->name('print.account');
    Route::get('/bukti-pendaftaran/{registration_id}', [DocumentController::class, 'printRegistrationForm'])->name('print.registration');
    Route::get('/kwitansi/{registration_id}', [DocumentController::class, 'printReceipt'])->name('print.receipt');
    Route::get('/cetak-semua-bukti', [DocumentController::class, 'printCollectiveRegistrations'])->name('print.collective');
});

/*
|--------------------------------------------------------------------------
| PIC Cabang Lomba Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:pic_lomba,superadmin'])->prefix('pic')->name('pic.')->group(function () {
    Route::get('/dashboard', [PicController::class, 'dashboard'])->name('dashboard');
    Route::get('/lomba/{competition_id}/peserta', [PicController::class, 'participants'])->name('participants');
    Route::get('/peserta/cetak-pdf', [PicController::class, 'printParticipantsPdf'])->name('participants.print.pdf');
    Route::get('/peserta/export-excel', [PicController::class, 'exportParticipantsExcel'])->name('participants.export.excel');
    Route::post('/peserta/{registration_id}/verifikasi', [PicController::class, 'verifyParticipant'])->name('verify.participant');
    Route::post('/peserta/{registration_id}/update', [PicController::class, 'updateParticipantData'])->name('update.participant');
    Route::post('/peserta/{registration_id}/batalkan-verifikasi', [PicController::class, 'unverifyParticipant'])->name('unverify.participant');
    Route::post('/peserta/{registration_id}/hapus', [PicController::class, 'deleteParticipant'])->name('delete.participant');
    Route::get('/undi-peserta', [PicController::class, 'drawIndex'])->name('undian');
    Route::get('/lomba/{competition_id}/undi', [PicController::class, 'hackerDraw'])->name('hacker.draw');
    Route::get('/lomba/{competition_id}/spin-wheel', [PicController::class, 'spinWheel'])->name('spin.wheel');
    Route::post('/lomba/{competition_id}/spin-wheel/save', [PicController::class, 'storeDrawResult'])->name('spin.wheel.save');
    Route::post('/lomba/{competition_id}/spin-wheel/reset', [PicController::class, 'resetDraws'])->name('spin.wheel.reset');
});

/*
|--------------------------------------------------------------------------
| Dewan Juri Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:juri,superadmin'])->prefix('juri')->name('juri.')->group(function () {
    Route::get('/dashboard', [JuriController::class, 'dashboard'])->name('dashboard');
    Route::get('/lomba/{competition_id}/scoring', [JuriController::class, 'scoringSheet'])->name('scoring');
    Route::post('/lomba/{competition_id}/peserta/{registration_id}/score', [JuriController::class, 'storeScore'])->name('score.store');
});

/*
|--------------------------------------------------------------------------
| Pertandingan Bulu Tangkis & Wasit Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:superadmin,pic_lomba,juri'])->prefix('badminton')->name('badminton.')->group(function () {
    Route::get('/matches', [BadmintonMatchController::class, 'index'])->name('index');
    Route::post('/matches', [BadmintonMatchController::class, 'store'])->name('store');
    Route::post('/matches/{id}/update', [BadmintonMatchController::class, 'update'])->name('update');
    Route::post('/matches/{id}/delete', [BadmintonMatchController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/competitions', [AdminController::class, 'competitions'])->name('competitions');
    Route::post('/competitions', [AdminController::class, 'storeCompetition'])->name('competitions.store');
    Route::post('/competitions/{id}/update', [AdminController::class, 'updateCompetition'])->name('competitions.update');
    Route::post('/competitions/{id}/delete', [AdminController::class, 'deleteCompetition'])->name('competitions.delete');
    Route::post('/competitions/{id}/toggle-live-score', [AdminController::class, 'toggleLiveScore'])->name('competitions.toggle-live-score');
    
    // Category Routes
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::post('/categories/{id}/update', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::post('/categories/{id}/delete', [AdminController::class, 'deleteCategory'])->name('categories.delete');

    // Timeline Routes
    Route::post('/timeline', [AdminController::class, 'storeTimeline'])->name('timeline.store');
    Route::post('/timeline/{id}/update', [AdminController::class, 'updateTimeline'])->name('timeline.update');
    Route::post('/timeline/{id}/delete', [AdminController::class, 'deleteTimeline'])->name('timeline.delete');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{id}/update', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{id}/reset-password', [AdminController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{id}/delete', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::get('/recap', [AdminController::class, 'recap'])->name('recap');
    Route::get('/rekap-nilai', [AdminController::class, 'recap'])->name('scores');

    // Operational & Competition Routes (Super Admin Access)
    Route::get('/verifikasi', [PicController::class, 'dashboard'])->name('verifications');
    Route::get('/peserta', [PicController::class, 'dashboard'])->name('participants.index');
    Route::get('/juri-wasit', [AdminController::class, 'juriWasitUndian'])->name('juri.wasit');
    Route::get('/undi-peserta', [PicController::class, 'drawIndex'])->name('undian');

    // Invoice & Payment Verification Routes
    Route::get('/invoices', [CollectiveRegistrationController::class, 'adminInvoices'])->name('invoices.index');
    Route::get('/invoices/{id}', [CollectiveRegistrationController::class, 'adminShowInvoice'])->name('invoices.show');
    Route::post('/invoices/{id}/verify', [CollectiveRegistrationController::class, 'adminVerifyInvoice'])->name('invoices.verify');

    // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/general', [AdminSettingsController::class, 'generalSettings'])->name('general');
        Route::post('/general', [AdminSettingsController::class, 'updateGeneralSettings'])->name('general.update');
        Route::get('/whatsapp-blast', [AdminSettingsController::class, 'whatsappBlast'])->name('whatsapp.blast');
        Route::get('/whatsapp-blast/check-status', [AdminSettingsController::class, 'checkWablasStatus'])->name('whatsapp.blast.check-status');
        Route::get('/whatsapp-blast/template', [AdminSettingsController::class, 'downloadWhatsappTemplate'])->name('whatsapp.blast.template');
        Route::post('/whatsapp-blast', [AdminSettingsController::class, 'sendWhatsappBlast'])->name('whatsapp.blast.send');
        Route::post('/whatsapp-blast/logs/{id}/delete', [AdminSettingsController::class, 'deleteBroadcastLog'])->name('whatsapp.blast.logs.delete');
        Route::post('/whatsapp-blast/logs/clear-all', [AdminSettingsController::class, 'clearAllBroadcastLogs'])->name('whatsapp.blast.logs.clear-all');
        Route::post('/whatsapp-blast/contacts', [AdminSettingsController::class, 'storeCustomContact'])->name('whatsapp.blast.contacts.store');
        Route::post('/whatsapp-blast/contacts/{id}/delete', [AdminSettingsController::class, 'deleteCustomContact'])->name('whatsapp.blast.contacts.delete');
        Route::post('/whatsapp-blast/contacts/clear-all', [AdminSettingsController::class, 'clearAllCustomContacts'])->name('whatsapp.blast.contacts.clear-all');
        Route::post('/whatsapp-blast/templates', [AdminSettingsController::class, 'storeWhatsappTemplate'])->name('whatsapp.blast.templates.store');
        Route::post('/whatsapp-blast/templates/{id}/update', [AdminSettingsController::class, 'updateWhatsappTemplate'])->name('whatsapp.blast.templates.update');
        Route::post('/whatsapp-blast/templates/{id}/delete', [AdminSettingsController::class, 'deleteWhatsappTemplate'])->name('whatsapp.blast.templates.delete');
        Route::post('/whatsapp-blast/templates/{id}/toggle', [AdminSettingsController::class, 'toggleWhatsappTemplate'])->name('whatsapp.blast.templates.toggle');
        Route::post('/whatsapp-blast/save-credentials', [AdminSettingsController::class, 'saveWablasCredentials'])->name('whatsapp.blast.save-credentials');
        Route::post('/whatsapp-blast/test-connection', [AdminSettingsController::class, 'testWablasConnection'])->name('whatsapp.blast.test-connection');
        Route::get('/changelog', [AdminSettingsController::class, 'changelog'])->name('changelog');
        Route::get('/app-info', [AdminSettingsController::class, 'appInfo'])->name('app.info');
    });
});
