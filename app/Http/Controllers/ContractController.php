<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\ContractHistory;
use App\Models\DocumentType;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    /**
     * Exibe a listagem de contratos.
     */
    public function index()
    {
        // Fornecedores veem apenas seus contratos. Gestores veem os da sua empresa.
        // O CompanyScope cuida desse filtro automaticamente no banco!
        $contracts = Contract::with(['company', 'provider', 'responsible'])
            ->orderBy('end_date', 'asc')
            ->get();

        $companies = [];
        $providers = [];
        $documentTypes = [];
        $responsibles = [];

        if (! auth()->user()->isFornecedor()) {
            $companies = Company::where('active', true)->orderBy('name', 'asc')->get();
            $providers = Provider::where('active', true)->orderBy('name', 'asc')->get();
            $documentTypes = DocumentType::where('required', true)->get();
            $responsibles = User::whereIn('role', ['gestor', 'super_admin'])
                ->where('active', true)
                ->orderBy('name', 'asc')
                ->get();
        }

        return view('contracts.index', compact('contracts', 'companies', 'providers', 'documentTypes', 'responsibles'));
    }

    /**
     * Salva o novo contrato e gera obrigações documentais automaticamente.
     */
    public function store(Request $request)
    {
        abort_if(auth()->user()->isFornecedor(), 403);

        $user = auth()->user();

        // Se for gestor, força a empresa do usuário logado
        if ($user->isGestor()) {
            $request->merge(['company_id' => $user->company_id]);
        }

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'provider_id' => 'nullable|exists:providers,id',
            'responsible_id' => 'nullable|exists:users,id',
            'contract_number' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'alert_days' => 'required|integer|min:0|max:365',
            'document_types' => 'nullable|array',
            'document_types.*' => 'exists:document_types,id',
        ]);

        $contract = Contract::create([
            'company_id' => $request->company_id,
            'provider_id' => $request->provider_id,
            'responsible_id' => $request->responsible_id ?? auth()->id(),
            'contract_number' => $request->contract_number,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'alert_days' => $request->alert_days ?? 30,
            'status' => 'pending',
        ]);

        // Gera as obrigações documentais se houver tipos selecionados
        if ($request->has('document_types')) {
            // Define o prazo de vencimento padrão como dia 15 do próximo mês
            $defaultDueDate = now()->addMonth()->startOfMonth()->addDays(14); // Dia 15

            foreach ($request->document_types as $typeId) {
                ContractDocument::create([
                    'contract_id' => $contract->id,
                    'document_type_id' => $typeId,
                    'due_date' => $defaultDueDate,
                    'status' => 'pending',
                ]);
            }
        }

        // Registrar no histórico do contrato
        ContractHistory::log($contract->id, 'created', 'Contrato Criado', 'O contrato foi cadastrado no sistema por '.auth()->user()->name);

        return redirect()->route('contracts.index')->with('success', 'Contrato cadastrado com sucesso e obrigações documentais geradas!');
    }

    /**
     * Atualiza dados do contrato.
     */
    public function update(Request $request, Contract $contract)
    {
        abort_if(auth()->user()->isFornecedor(), 403);

        if (auth()->user()->isGestor()) {
            abort_if($contract->company_id !== auth()->user()->company_id, 403);
            $request->merge(['company_id' => auth()->user()->company_id]);
        }

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'provider_id' => 'nullable|exists:providers,id',
            'responsible_id' => 'nullable|exists:users,id',
            'contract_number' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'alert_days' => 'required|integer|min:0|max:365',
            'status' => 'required|in:pending,active,expired,suspended,draft',
        ]);

        $oldStatus = $contract->status;

        $contract->update([
            'company_id' => $request->company_id,
            'provider_id' => $request->provider_id,
            'responsible_id' => $request->responsible_id ?? auth()->id(),
            'contract_number' => $request->contract_number,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'alert_days' => $request->alert_days,
            'status' => $request->status,
        ]);

        // Registrar no histórico do contrato
        if ($oldStatus !== $contract->status) {
            ContractHistory::log($contract->id, 'status_changed', 'Status Alterado', 'O status do contrato foi alterado de '.$oldStatus.' para '.$contract->status.' por '.auth()->user()->name);
        } else {
            ContractHistory::log($contract->id, 'updated', 'Contrato Atualizado', 'As informações do contrato foram atualizadas por '.auth()->user()->name);
        }

        return redirect()->route('contracts.index')->with('success', 'Contrato atualizado com sucesso!');
    }

    /**
     * Exibe o detalhe de um contrato (Histórico, Linha do Tempo e Solicitações).
     */
    public function show(Contract $contract)
    {
        $user = auth()->user();

        // Validação adicional de ACL por segurança
        if ($user->isFornecedor()) {
            abort_if($contract->provider_id !== $user->provider_id, 403, 'Acesso não autorizado.');
        } elseif ($user->isGestor()) {
            abort_if($contract->company_id !== $user->company_id, 403, 'Acesso não autorizado.');
        }

        // Carregar relacionamentos necessários
        $contract->load([
            'company',
            'provider',
            'responsible',
            'documents.documentType',
            'documents.reviewer',
            'requests' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'requests.user',
            'requests.responder',
            'histories' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'histories.user',
        ]);

        $documentTypes = [];
        if (! auth()->user()->isFornecedor()) {
            $documentTypes = DocumentType::orderBy('name', 'asc')->get();
        }

        return view('contracts.show', compact('contract', 'documentTypes'));
    }

    /**
     * Adiciona uma obrigação documental avulsa a um contrato existente.
     */
    public function addObligation(Request $request, Contract $contract)
    {
        $user = auth()->user();

        // Apenas Gestores e SuperAdmin podem adicionar obrigações
        if ($user->isFornecedor()) {
            abort(403);
        }

        if ($user->isGestor() && $contract->company_id !== $user->company_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        // Evita duplicar a mesma obrigação pendente/enviada para o mesmo contrato
        $exists = ContractDocument::where('contract_id', $contract->id)
            ->where('document_type_id', $request->document_type_id)
            ->whereIn('status', ['pending', 'submitted'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Já existe essa obrigação documental pendente ou aguardando análise para este contrato.');
        }

        $doc = ContractDocument::create([
            'contract_id' => $contract->id,
            'document_type_id' => $request->document_type_id,
            'due_date' => $request->due_date,
            'status' => 'pending',
        ]);

        // Registrar no histórico do contrato
        ContractHistory::log(
            $contract->id,
            'document_required',
            'Nova Obrigação Exigida',
            'Uma nova obrigação documental ("'.$doc->documentType->name.'") com vencimento em '.$doc->due_date->format('d/m/Y').' foi exigida por '.$user->name
        );

        return back()->with('success', 'Nova obrigação documental adicionada com sucesso!');
    }

    /**
     * Valida a assinatura do contrato.
     */
    public function validateSignature(Contract $contract)
    {
        $user = auth()->user();

        // Apenas Gestores e SuperAdmin podem validar a assinatura
        if ($user->isFornecedor()) {
            abort(403);
        }

        if ($user->isGestor() && $contract->company_id !== $user->company_id) {
            abort(403, 'Acesso não autorizado.');
        }

        $contract->update([
            'signature_validated' => true,
        ]);

        // Registrar no histórico do contrato
        ContractHistory::log(
            $contract->id,
            'signature_validated',
            'Assinatura Validada',
            'A assinatura do contrato foi validada e confirmada por '.$user->name
        );

        return back()->with('success', 'Assinatura do contrato validada com sucesso! O status foi atualizado.');
    }
}
