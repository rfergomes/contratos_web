<?php

namespace App\Http\Controllers;

use App\Models\ContractDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GedController extends Controller
{
    /**
     * Lista os documentos de acordo com o perfil.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isFornecedor()) {
            // Fornecedor vê os documentos exigidos para seus contratos
            // Graças ao DocumentScope, isso já é filtrado automaticamente!
            $documents = ContractDocument::with(['contract', 'documentType'])
                ->orderBy('due_date', 'asc')
                ->get();

            return view('ged.fornecedor', compact('documents'));
        }

        // Gestor e SuperAdmin veem os documentos enviados aguardando análise
        // Filtrado por empresa via DocumentScope (para gestores)
        $documents = ContractDocument::with(['contract.provider', 'documentType'])
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'asc')
            ->get();

        return view('ged.analise', compact('documents'));
    }

    /**
     * Upload seguro do documento (Fornecedor).
     */
    public function upload(Request $request, ContractDocument $document)
    {
        $user = Auth::user();

        // Validação adicional de segurança (caso o scope seja burlado)
        if ($user->isFornecedor() && $document->contract->provider_id !== $user->provider_id) {
            abort(403, 'Acesso não autorizado a este contrato.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,png,jpeg|max:10240', // PDF ou Imagem, max 10MB
        ]);

        if ($request->file('file')->isValid()) {
            // Deleta o arquivo anterior se existir
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Salva na pasta privada do storage (não pública)
            $path = $request->file('file')->store('private/documents/contracts/' . $document->contract_id);

            $document->update([
                'file_path' => $path,
                'original_name' => $request->file('file')->getClientOriginalName(),
                'status' => 'submitted',
                'submitted_at' => now(),
                'rejection_reason' => null, // Limpa motivo anterior se houver
                'approved_at' => null,
            ]);

            return back()->with('success', 'Documento enviado com sucesso! Aguarde a análise do gestor.');
        }

        return back()->with('error', 'Falha ao processar arquivo.');
    }

    /**
     * Download seguro do documento (Verifica ACL antes de transmitir).
     */
    public function download(ContractDocument $document)
    {
        $user = Auth::user();

        // Regra de Acesso:
        // 1. SuperAdmin tem acesso total.
        // 2. Gestor tem acesso se o contrato for da empresa dele.
        // 3. Fornecedor tem acesso se o contrato for do provider dele.
        if ($user->isSuperAdmin()) {
            $hasAccess = true;
        } elseif ($user->isGestor() && $document->contract->company_id === $user->company_id) {
            $hasAccess = true;
        } elseif ($user->isFornecedor() && $document->contract->provider_id === $user->provider_id) {
            $hasAccess = true;
        } else {
            $hasAccess = false;
        }

        if (!$hasAccess || !$document->file_path || !Storage::exists($document->file_path)) {
            abort(403, 'Documento não disponível ou acesso negado.');
        }

        return Storage::download($document->file_path, $document->original_name);
    }

    /**
     * Aprovação de Documento (Gestor).
     */
    public function approve(ContractDocument $document)
    {
        $user = Auth::user();

        if ($user->isFornecedor()) {
            abort(403);
        }

        // Validação adicional de segurança para o Gestor
        if ($user->isGestor() && $document->contract->company_id !== $user->company_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $document->update([
            'status' => 'approved',
            'approved_at' => now(),
            'reviewed_by' => $user->id,
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Documento aprovado com sucesso!');
    }

    /**
     * Recusa/Rejeição de Documento (Gestor).
     */
    public function reject(Request $request, ContractDocument $document)
    {
        $user = Auth::user();

        if ($user->isFornecedor()) {
            abort(403);
        }

        // Validação adicional de segurança para o Gestor
        if ($user->isGestor() && $document->contract->company_id !== $user->company_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => $user->id,
            'approved_at' => null,
        ]);

        return back()->with('warning', 'Documento recusado. O fornecedor foi notificado sobre a pendência.');
    }
}
