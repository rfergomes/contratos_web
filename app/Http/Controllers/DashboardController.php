<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\Provider;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Carrega a visão geral do Dashboard e gera alertas.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Gera alertas de prazos dinamicamente
        Alert::generateAlertsForUser($user);

        // 2. Estatísticas dos Widgets baseadas no Perfil
        $stats = [];
        if ($user->isSuperAdmin()) {
            $stats = [
                'companies' => Company::count(),
                'providers' => Provider::where('active', true)->count(),
                'contracts' => Contract::count(),
                'documents' => ContractDocument::count(),
            ];
        } elseif ($user->isGestor()) {
            $stats = [
                'active_contracts' => Contract::where('status', 'active')->count(),
                'submitted_documents' => ContractDocument::where('status', 'submitted')->count(),
                'pending_documents' => ContractDocument::whereIn('status', ['pending', 'rejected'])->count(),
                'approved_documents' => ContractDocument::where('status', 'approved')->count(),
            ];
        } elseif ($user->isFornecedor()) {
            $stats = [
                'pending_obligations' => ContractDocument::whereIn('status', ['pending', 'rejected'])->count(),
                'submitted_documents' => ContractDocument::where('status', 'submitted')->count(),
                'compliant_documents' => ContractDocument::where('status', 'approved')->count(),
            ];
        }

        // 3. Últimos 5 contratos de acordo com o contexto do usuário
        $recentContracts = Contract::with(['company', 'provider', 'responsible'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 4. Carregar alertas não lidos do usuário
        $unreadAlerts = Alert::where('user_id', $user->id)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard', compact('stats', 'recentContracts', 'unreadAlerts'));
    }

    /**
     * Marca um alerta específico como lido (via AJAX).
     */
    public function markAsRead(Alert $alert)
    {
        abort_unless($alert->user_id === Auth::id(), 403, 'Acesso não autorizado.');

        $alert->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Alerta marcado como lido!',
        ]);
    }

    /**
     * Marca todos os alertas do usuário logado como lidos (via AJAX).
     */
    public function markAllAsRead()
    {
        Alert::where('user_id', Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Todos os alertas foram marcados como lidos!',
        ]);
    }

    /**
     * Redireciona o usuário para o local de origem do alerta e o marca como lido.
     */
    public function navigate(Alert $alert)
    {
        abort_unless($alert->user_id === Auth::id(), 403, 'Acesso não autorizado.');

        $alert->update(['read_at' => now()]);

        return redirect($alert->link);
    }
}
