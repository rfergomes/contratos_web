<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    /**
     * Exibe a listagem de fornecedores.
     */
    public function index()
    {
        abort_if(auth()->user()->isFornecedor(), 403, 'Acesso restrito.');

        $providers = Provider::orderBy('name', 'asc')->get();

        return view('providers.index', compact('providers'));
    }



    /**
     * Salva novo fornecedor.
     */
    public function store(Request $request)
    {
        abort_if(auth()->user()->isFornecedor(), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:20|unique:providers,cnpj',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        Provider::create([
            'name' => $request->name,
            'cnpj' => $request->cnpj,
            'email' => $request->email,
            'phone' => $request->phone,
            'active' => true,
        ]);

        return redirect()->route('providers.index')->with('success', 'Fornecedor cadastrado com sucesso!');
    }



    /**
     * Atualiza dados do fornecedor.
     */
    public function update(Request $request, Provider $provider)
    {
        abort_if(auth()->user()->isFornecedor(), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'required|string|max:20|unique:providers,cnpj,' . $provider->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $provider->update([
            'name' => $request->name,
            'cnpj' => $request->cnpj,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('providers.index')->with('success', 'Dados do fornecedor atualizados com sucesso!');
    }

    /**
     * Alterna status ativo/inativo.
     */
    public function toggle(Provider $provider)
    {
        abort_if(auth()->user()->isFornecedor(), 403);

        $provider->update([
            'active' => !$provider->active
        ]);

        return back()->with('success', 'Status do fornecedor alterado com sucesso!');
    }
}
