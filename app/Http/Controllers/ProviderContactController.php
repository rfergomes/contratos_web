<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderContact;
use Illuminate\Http\Request;

class ProviderContactController extends Controller
{
    /**
     * Retorna a lista de contatos de um fornecedor em formato JSON.
     */
    public function index(Provider $provider)
    {
        abort_if(auth()->user()->isFornecedor(), 403, 'Acesso não autorizado.');

        $contacts = $provider->contacts()->orderBy('is_main', 'desc')->orderBy('name', 'asc')->get();
        return response()->json($contacts);
    }

    /**
     * Salva um novo contato para o fornecedor.
     */
    public function store(Request $request, Provider $provider)
    {
        abort_if(auth()->user()->isFornecedor(), 403, 'Acesso não autorizado.');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_main' => 'nullable|boolean',
        ]);

        $contact = $provider->contacts()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_main' => $request->boolean('is_main'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Contato adicionado com sucesso!',
            'contact' => $contact
        ]);
    }

    /**
     * Atualiza um contato existente.
     */
    public function update(Request $request, ProviderContact $contact)
    {
        abort_if(auth()->user()->isFornecedor(), 403, 'Acesso não autorizado.');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_main' => 'nullable|boolean',
        ]);

        $contact->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'is_main' => $request->boolean('is_main'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Contato atualizado com sucesso!',
            'contact' => $contact
        ]);
    }

    /**
     * Exclui um contato do fornecedor.
     */
    public function destroy(ProviderContact $contact)
    {
        abort_if(auth()->user()->isFornecedor(), 403, 'Acesso não autorizado.');

        $contact->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contato removido com sucesso!'
        ]);
    }

    /**
     * Alterna/Define o contato como principal.
     */
    public function toggleMain(ProviderContact $contact)
    {
        abort_if(auth()->user()->isFornecedor(), 403, 'Acesso não autorizado.');

        $contact->update([
            'is_main' => true // O model se encarrega de desmarcar os outros no event saving
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Contato marcado como principal com sucesso!'
        ]);
    }
}
