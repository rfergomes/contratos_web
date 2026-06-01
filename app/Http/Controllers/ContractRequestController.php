<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractRequest;
use App\Models\ContractHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractRequestController extends Controller
{
    /**
     * Cria uma nova solicitação bidirecional vinculada ao contrato.
     */
    public function store(Request $request, Contract $contract)
    {
        $user = Auth::user();

        // Validação adicional de ACL por garantia
        if ($user->isFornecedor()) {
            abort_if($contract->provider_id !== $user->provider_id, 403, 'Acesso não autorizado.');
            $senderType = 'provider';
        } else {
            if ($user->isGestor()) {
                abort_if($contract->company_id !== $user->company_id, 403, 'Acesso não autorizado.');
            }
            $senderType = 'company';
        }

        $request->validate([
            'type' => 'required|string|in:clarification,amendment,renewal,document,other',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
        ]);

        $contractRequest = ContractRequest::create([
            'contract_id' => $contract->id,
            'sender_type' => $senderType,
            'user_id' => $user->id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        // Traduzir o tipo de solicitação para o log
        $types = [
            'clarification' => 'Esclarecimento',
            'amendment' => 'Aditivo Contratual',
            'renewal' => 'Renovação',
            'document' => 'Ajuste de Documento',
            'other' => 'Outro'
        ];
        $typeName = $types[$request->type] ?? $request->type;

        // Registrar no histórico do contrato
        ContractHistory::log(
            $contract->id,
            'request_opened',
            'Solicitação Aberta: ' . $typeName,
            "Solicitação \"{$request->title}\" aberta por {$user->name}."
        );

        return back()->with('success', 'Solicitação enviada com sucesso!');
    }

    /**
     * Responde a uma solicitação existente (Aprova/Resolve ou Rejeita).
     */
    public function respond(Request $request, ContractRequest $contractRequest)
    {
        $user = Auth::user();

        // Validar quem pode responder:
        // 1. Se quem enviou foi a empresa (sender_type == company), o fornecedor deve responder.
        // 2. Se quem enviou foi o fornecedor (sender_type == provider), a empresa (gestor/admin) deve responder.
        if ($contractRequest->sender_type === 'company') {
            abort_unless($user->isFornecedor(), 403, 'Apenas o fornecedor pode responder a esta solicitação.');
            // Valida se o fornecedor está associado ao contrato
            abort_if($contractRequest->contract->provider_id !== $user->provider_id, 403);
        } else {
            abort_if($user->isFornecedor(), 403, 'Apenas gestores da contratante podem responder a esta solicitação.');
            if ($user->isGestor()) {
                abort_if($contractRequest->contract->company_id !== $user->company_id, 403);
            }
        }

        $request->validate([
            'status' => 'required|string|in:resolved,rejected',
            'response_text' => 'required|string|max:2000',
        ]);

        $contractRequest->update([
            'status' => $request->status,
            'response_text' => $request->response_text,
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        // Registrar no histórico do contrato
        $actionName = $request->status === 'resolved' ? 'Resolvida' : 'Recusada';
        $historyAction = $request->status === 'resolved' ? 'request_resolved' : 'request_rejected';

        ContractHistory::log(
            $contractRequest->contract_id,
            $historyAction,
            "Solicitação {$actionName}",
            "A solicitação \"{$contractRequest->title}\" foi respondida como {$actionName} por {$user->name}. Resposta: \"{$request->response_text}\""
        );

        $msgType = $request->status === 'resolved' ? 'success' : 'warning';
        return back()->with($msgType, "Solicitação respondida como {$actionName}!");
    }
}
