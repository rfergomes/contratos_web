<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractDocument;
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

        if (!auth()->user()->isFornecedor()) {
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
            'provider_id' => 'required|exists:providers,id',
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
            'status' => 'active',
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
            'provider_id' => 'required|exists:providers,id',
            'responsible_id' => 'nullable|exists:users,id',
            'contract_number' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'alert_days' => 'required|integer|min:0|max:365',
            'status' => 'required|in:active,expired,suspended,draft',
        ]);

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

        return redirect()->route('contracts.index')->with('success', 'Contrato atualizado com sucesso!');
    }
}
