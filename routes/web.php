<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\GedController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContractRequestController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\ProviderContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicAccessController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Rotas de Autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Recuperação de Senha
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->middleware('guest')
    ->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');

Route::get('/public/access/{token}', [PublicAccessController::class, 'showLandingPage'])->name('public.access');
Route::post('/public/access/{token}', [PublicAccessController::class, 'authenticate'])->name('public.access.authenticate');

// Área Autenticada
Route::middleware(['auth'])->group(function () {
    // Dashboard e Alertas
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/alerts/{alert}/read', [DashboardController::class, 'markAsRead'])->name('alerts.read');
    Route::post('/alerts/read-all', [DashboardController::class, 'markAllAsRead'])->name('alerts.read-all');
    Route::get('/alerts/{alert}/go', [DashboardController::class, 'navigate'])->name('alerts.go');

    // Rotas de GED (Upload, Download, Aprovação/Recusa)
    Route::get('/ged', [GedController::class, 'index'])->name('ged.index');
    Route::post('/ged/upload/{document}', [GedController::class, 'upload'])->name('ged.upload');
    Route::get('/ged/download/{document}', [GedController::class, 'download'])->name('ged.download');
    Route::post('/ged/approve/{document}', [GedController::class, 'approve'])->name('ged.approve');
    Route::post('/ged/reject/{document}', [GedController::class, 'reject'])->name('ged.reject');

    // Rota para alternar a empresa ativa (contexto)
    Route::post('/switch-company', [CompanyController::class, 'switchCompany'])->name('companies.switch');

    // Rotas de Empresas Contratantes
    Route::resource('companies', CompanyController::class)->except(['destroy', 'create', 'edit']);
    Route::patch('/companies/{company}/toggle', [CompanyController::class, 'toggle'])->name('companies.toggle');

    // Rotas de Fornecedores
    Route::resource('providers', ProviderController::class)->except(['destroy', 'create', 'edit']);
    Route::patch('/providers/{provider}/toggle', [ProviderController::class, 'toggle'])->name('providers.toggle');
    Route::get('/providers/{provider}/contacts', [ProviderContactController::class, 'index'])->name('providers.contacts.index');
    Route::post('/providers/{provider}/contacts', [ProviderContactController::class, 'store'])->name('providers.contacts.store');
    Route::put('/provider-contacts/{contact}', [ProviderContactController::class, 'update'])->name('providers.contacts.update');
    Route::delete('/provider-contacts/{contact}', [ProviderContactController::class, 'destroy'])->name('providers.contacts.destroy');
    Route::patch('/provider-contacts/{contact}/toggle-main', [ProviderContactController::class, 'toggleMain'])->name('providers.contacts.toggle-main');

    // Rotas de Contratos
    Route::resource('contracts', ContractController::class)->except(['destroy', 'create', 'edit']);
    Route::post('/contracts/{contract}/documents', [ContractController::class, 'addObligation'])->name('contracts.documents.store');
    Route::post('/contracts/{contract}/validate-signature', [ContractController::class, 'validateSignature'])->name('contracts.validate-signature');
    Route::post('/contracts/{contract}/requests', [ContractRequestController::class, 'store'])->name('contracts.requests.store');
    Route::post('/contracts/requests/{contractRequest}/respond', [ContractRequestController::class, 'respond'])->name('contracts.requests.respond');
    Route::get('/contracts/requests/{contractRequest}/download/{side}', [ContractRequestController::class, 'downloadAttachment'])->name('contracts.requests.download');
    Route::post('/contracts/requests/{request}/whatsapp-link', [PublicAccessController::class, 'generateRequestLink'])->name('contracts.requests.whatsapp-link');
    Route::post('/contracts/documents/{document}/whatsapp-link', [PublicAccessController::class, 'generateDocumentLink'])->name('contracts.documents.whatsapp-link');

    // Rotas de Tipos de Documentos (Obrigações Gerais)
    Route::resource('document-types', DocumentTypeController::class)->except(['destroy', 'create', 'edit']);
    Route::patch('/document-types/{documentType}/toggle', [DocumentTypeController::class, 'toggle'])->name('document-types.toggle');

    // Rotas de Usuários
    Route::resource('users', UserController::class)->except(['destroy', 'create', 'edit']);
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

    // Rota de Perfil do Usuário
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Rota de Health Check utilizada pelo script de deploy
Route::get('/_health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'ok'], 200);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

// Rota para resetar o OPcache localmente após o deploy (apenas requisições locais)
Route::post('/_deploy/opcache-reset', function () {
    if (!in_array(request()->ip(), ['127.0.0.1', '::1'])) {
        abort(403);
    }

    if (function_exists('opcache_reset')) {
        opcache_reset();
        return response()->json(['status' => 'ok', 'message' => 'OPcache resetado com sucesso']);
    }

    return response()->json(['status' => 'ok']);
});
