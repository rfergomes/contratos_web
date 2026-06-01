<?php

namespace App\Http\Controllers;

use App\Models\ContractDocument;
use App\Models\ContractRequest;
use App\Models\TemporaryAccessToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicAccessController extends Controller
{
    /**
     * Valida o token temporário, autentica o fornecedor e o redireciona ao recurso.
     */
    public function login(string $token)
    {
        $accessToken = TemporaryAccessToken::where('token', $token)->first();

        if (!$accessToken || $accessToken->expires_at->isPast()) {
            if ($accessToken) {
                $accessToken->delete(); // Limpa token expirado
            }
            return redirect()->route('login')->with('error', 'O link de acesso expirou ou é inválido.');
        }

        $item = $accessToken->tokenable;
        if (!$item) {
            $accessToken->delete();
            return redirect()->route('login')->with('error', 'O recurso associado a este link não existe mais.');
        }

        $contract = $item->contract;
        if (!$contract) {
            $accessToken->delete();
            return redirect()->route('login')->with('error', 'Contrato não encontrado para este recurso.');
        }

        // Localiza o primeiro usuário fornecedor ativo atrelado a este provedor
        $user = User::withoutGlobalScopes()
            ->where('provider_id', $contract->provider_id)
            ->where('role', 'fornecedor')
            ->where('active', true)
            ->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Nenhum usuário ativo de fornecedor configurado para este contrato.');
        }

        // Login automático do usuário
        Auth::login($user);

        // Deleta o token para garantir uso único (Single-Use)
        $accessToken->delete();

        // Determina o destino baseado no tipo do recurso
        if ($accessToken->tokenable_type === ContractRequest::class) {
            return redirect()->to(route('contracts.show', $contract->id) . '#timeline')
                ->with('success', 'Acesso autenticado via Link Mágico. Você foi direcionado para a solicitação.');
        }

        if ($accessToken->tokenable_type === ContractDocument::class) {
            return redirect()->route('ged.index')
                ->with('success', 'Acesso autenticado via Link Mágico. Envie o documento exigido abaixo.');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Gera o link temporário e mensagem de WhatsApp para uma solicitação pendente.
     */
    public function generateRequestLink(ContractRequest $request)
    {
        abort_if(Auth::user()->isFornecedor(), 403, 'Acesso não autorizado.');

        $token = TemporaryAccessToken::generateFor($request);
        $link = route('public.access', $token->token);

        $message = "Olá! Nova solicitação pendente no Contrato {$request->contract->contract_number}.\n\n"
                 . "Por favor, acesse o link temporário seguro para responder:\n"
                 . $link;

        // Registrar no histórico do contrato
        \App\Models\ContractHistory::log(
            $request->contract_id,
            'whatsapp_charge',
            'Cobrança via WhatsApp',
            'Uma cobrança/notificação sobre a solicitação "' . $request->title . '" foi gerada para envio via WhatsApp por ' . Auth::user()->name
        );

        return response()->json([
            'status' => 'success',
            'link' => $link,
            'message' => $message,
        ]);
    }

    /**
     * Gera o link temporário e mensagem de WhatsApp para uma obrigação pendente.
     */
    public function generateDocumentLink(ContractDocument $document)
    {
        abort_if(Auth::user()->isFornecedor(), 403, 'Acesso não autorizado.');

        $token = TemporaryAccessToken::generateFor($document);
        $link = route('public.access', $token->token);

        $message = "Olá! O documento '{$document->documentType->name}' do Contrato {$document->contract->contract_number} está pendente de envio.\n\n"
                 . "Por favor, acesse o link temporário seguro para fazer o upload:\n"
                 . $link;

        // Registrar no histórico do contrato
        \App\Models\ContractHistory::log(
            $document->contract_id,
            'whatsapp_charge',
            'Cobrança via WhatsApp',
            'Uma cobrança/notificação sobre o documento "' . $document->documentType->name . '" foi gerada para envio via WhatsApp por ' . Auth::user()->name
        );

        return response()->json([
            'status' => 'success',
            'link' => $link,
            'message' => $message,
        ]);
    }
}
