<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTypeController extends Controller
{
    /**
     * Listagem dos tipos de documentos.
     */
    public function index()
    {
        abort_if(!auth()->user() || !auth()->user()->isSuperAdmin(), 403, 'Acesso restrito ao Administrador Global.');

        $documentTypes = DocumentType::orderBy('name', 'asc')->get();
        return view('document_types.index', compact('documentTypes'));
    }

    /**
     * Cadastra um novo tipo de documento.
     */
    public function store(Request $request)
    {
        abort_if(!auth()->user() || !auth()->user()->isSuperAdmin(), 403, 'Acesso restrito ao Administrador Global.');

        $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name',
            'description' => 'nullable|string|max:1000',
            'periodicity' => 'required|string|in:monthly,quarterly,semi-annual,annual,once',
            'required' => 'nullable|boolean',
        ]);

        DocumentType::create([
            'name' => $request->name,
            'description' => $request->description,
            'periodicity' => $request->periodicity,
            'required' => $request->has('required') ? (bool)$request->required : false,
        ]);

        return redirect()->route('document-types.index')->with('success', 'Tipo de documento cadastrado com sucesso!');
    }

    /**
     * Atualiza dados de um tipo de documento.
     */
    public function update(Request $request, DocumentType $documentType)
    {
        abort_if(!auth()->user() || !auth()->user()->isSuperAdmin(), 403, 'Acesso restrito ao Administrador Global.');

        $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name,' . $documentType->id,
            'description' => 'nullable|string|max:1000',
            'periodicity' => 'required|string|in:monthly,quarterly,semi-annual,annual,once',
            'required' => 'nullable|boolean',
        ]);

        $documentType->update([
            'name' => $request->name,
            'description' => $request->description,
            'periodicity' => $request->periodicity,
            'required' => $request->has('required') ? (bool)$request->required : false,
        ]);

        return redirect()->route('document-types.index')->with('success', 'Tipo de documento atualizado com sucesso!');
    }

    /**
     * Alterna a obrigatoriedade do documento.
     */
    public function toggle(DocumentType $documentType)
    {
        abort_if(!auth()->user() || !auth()->user()->isSuperAdmin(), 403, 'Acesso restrito ao Administrador Global.');

        $documentType->update([
            'required' => !$documentType->required,
        ]);

        return redirect()->route('document-types.index')->with('success', 'Configuração de obrigatoriedade alterada!');
    }
}
