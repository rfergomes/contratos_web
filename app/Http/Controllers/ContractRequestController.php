<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Contract;
use App\Models\ContractHistory;
use App\Models\ContractRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'due_date' => 'nullable|date|after_or_equal:today',
            'requires_attachment' => 'nullable|boolean',
            'file' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:10240', // Max 10MB
        ]);

        $filePath = null;
        $originalName = null;

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $filePath = $request->file('file')->store('private/requests/sender/'.$contract->id);
            $originalName = $request->file('file')->getClientOriginalName();
        }

        $contractRequest = ContractRequest::create([
            'contract_id' => $contract->id,
            'sender_type' => $senderType,
            'user_id' => $user->id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'original_name' => $originalName,
            'due_date' => $request->due_date,
            'requires_attachment' => $request->boolean('requires_attachment'),
            'status' => 'pending',
        ]);

        // Traduzir o tipo de solicitação para o log
        $types = [
            'clarification' => 'Esclarecimento',
            'amendment' => 'Aditivo Contratual',
            'renewal' => 'Renovação',
            'document' => 'Ajuste de Documento',
            'other' => 'Outro',
        ];
        $typeName = $types[$request->type] ?? $request->type;

        // Registrar no histórico do contrato
        ContractHistory::log(
            $contract->id,
            'request_opened',
            'Solicitação Aberta: '.$typeName,
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

        // Valida se o anexo é obrigatório ao resolver/aprovar a solicitação
        if ($contractRequest->requires_attachment && $request->status === 'resolved') {
            $request->validate([
                'status' => 'required|string|in:resolved,rejected',
                'response_text' => 'required|string|max:2000',
                'response_file' => 'required|file|mimes:pdf,jpg,png,jpeg|max:10240',
            ]);
        } else {
            $request->validate([
                'status' => 'required|string|in:resolved,rejected',
                'response_text' => 'required|string|max:2000',
                'response_file' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:10240',
            ]);
        }

        $responseFilePath = null;
        $responseOriginalName = null;

        if ($request->hasFile('response_file') && $request->file('response_file')->isValid()) {
            // Remove o anexo anterior de resposta se houver
            if ($contractRequest->response_file_path && Storage::exists($contractRequest->response_file_path)) {
                Storage::delete($contractRequest->response_file_path);
            }
            $responseFilePath = $request->file('response_file')->store('private/requests/responder/'.$contractRequest->contract_id);
            $responseOriginalName = $request->file('response_file')->getClientOriginalName();
        }

        $contractRequest->update([
            'status' => $request->status,
            'response_text' => $request->response_text,
            'response_file_path' => $responseFilePath ?: $contractRequest->response_file_path,
            'response_original_name' => $responseOriginalName ?: $contractRequest->response_original_name,
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        Alert::createForRequestResponse($contractRequest);

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

    /**
     * Download seguro do anexo da solicitação (tanto do autor quanto da resposta).
     */
    public function downloadAttachment(ContractRequest $contractRequest, $side)
    {
        $user = Auth::user();
        $contract = $contractRequest->contract;

        // Validar ACL:
        // 1. SuperAdmin tem acesso total.
        // 2. Gestor tem acesso se o contrato for da empresa dele.
        // 3. Fornecedor tem acesso se o contrato for do provider dele.
        if ($user->isSuperAdmin()) {
            $hasAccess = true;
        } elseif ($user->isGestor() && $contract->company_id === $user->company_id) {
            $hasAccess = true;
        } elseif ($user->isFornecedor() && $contract->provider_id === $user->provider_id) {
            $hasAccess = true;
        } else {
            $hasAccess = false;
        }

        if (! $hasAccess) {
            abort(403, 'Acesso não autorizado a este anexo.');
        }

        if ($side === 'sender') {
            $path = $contractRequest->file_path;
            $name = $contractRequest->original_name;
        } elseif ($side === 'responder') {
            $path = $contractRequest->response_file_path;
            $name = $contractRequest->response_original_name;
        } else {
            abort(404, 'Lado do anexo inválido.');
        }

        if (! $path || ! Storage::exists($path)) {
            abort(404, 'Arquivo de anexo não disponível.');
        }

        return Storage::download($path, $name);
    }
}
