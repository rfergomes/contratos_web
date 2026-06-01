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

// Área Autenticada
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

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

    // Rotas de Contratos
    Route::resource('contracts', ContractController::class)->except(['destroy', 'create', 'edit']);

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
