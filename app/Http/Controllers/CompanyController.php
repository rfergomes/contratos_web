<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Exibe a listagem de empresas.
     */
    public function index()
    {
        abort_if(!auth()->user()->isSuperAdmin(), 403, 'Acesso restrito a Administradores Globais.');

        $companies = Company::orderBy('name', 'asc')->get();

        return view('companies.index', compact('companies'));
    }



    /**
     * Salva nova empresa no banco.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->isSuperAdmin(), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:20|unique:companies,cnpj',
            'active' => 'nullable|boolean',
        ]);

        Company::create([
            'name' => $request->name,
            'cnpj' => $request->cnpj,
            'active' => $request->has('active'),
        ]);

        return redirect()->route('companies.index')->with('success', 'Empresa contratante cadastrada com sucesso!');
    }

    /**
     * Alterna o contexto da empresa ativa para o usuário atual.
     */
    public function switchCompany(Request $request)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            $request->validate([
                'company_id' => 'nullable|exists:companies,id',
            ]);
            $user->update(['company_id' => $request->company_id]);
            $msg = $request->company_id 
                ? 'Empresa ativa alterada com sucesso!' 
                : 'Exibindo dados de todas as empresas contratantes.';
            return back()->with('success', $msg);
        }

        if ($user->isGestor()) {
            $request->validate([
                'company_id' => 'required|exists:companies,id',
            ]);

            if (!$user->companies()->where('companies.id', $request->company_id)->exists()) {
                abort(403, 'Você não tem acesso a esta empresa.');
            }

            $user->update(['company_id' => $request->company_id]);
            return back()->with('success', 'Empresa ativa alterada com sucesso!');
        }

        abort(403, 'Acesso restrito.');
    }

    /**
     * Atualiza dados da empresa.
     */
    public function update(Request $request, Company $company)
    {
        abort_if(!auth()->user()->isSuperAdmin(), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:20|unique:companies,cnpj,' . $company->id,
            'active' => 'nullable|boolean',
        ]);

        $company->update([
            'name' => $request->name,
            'cnpj' => $request->cnpj,
            'active' => $request->has('active'),
        ]);

        return redirect()->route('companies.index')->with('success', 'Dados da empresa atualizados com sucesso!');
    }

    /**
     * Alterna o status ativo/inativo da empresa.
     */
    public function toggle(Company $company)
    {
        abort_if(!auth()->user()->isSuperAdmin(), 403);

        $company->update([
            'active' => !$company->active
        ]);

        return back()->with('success', 'Status da empresa alterado com sucesso!');
    }
}
