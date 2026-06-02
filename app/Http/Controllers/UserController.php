<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Exibe a listagem de usuários.
     */
    public function index()
    {
        abort_if(auth()->user()->isFornecedor(), 403, 'Acesso negado.');

        $currentUser = auth()->user();

        // O CompanyScope cuida do filtro automático no banco de dados
        $users = User::with(['company', 'provider'])->orderBy('name', 'asc')->get();

        if ($currentUser->isSuperAdmin()) {
            $companies = Company::where('active', true)->orderBy('name', 'asc')->get();
            $providers = Provider::where('active', true)->orderBy('name', 'asc')->get();
        } else {
            // Gestores veem apenas as empresas a que têm acesso
            $companies = $currentUser->companies()->where('companies.active', true)->orderBy('name', 'asc')->get();
            $providers = Provider::where('active', true)->orderBy('name', 'asc')->get();
        }

        return view('users.index', compact('users', 'companies', 'providers'));
    }

    /**
     * Cadastra um novo usuário no banco.
     */
    public function store(Request $request)
    {
        abort_if(auth()->user()->isFornecedor(), 403);
        $currentUser = auth()->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', Password::defaults()],
            'role' => 'required|in:super_admin,gestor,fornecedor',
            'company_id' => 'nullable|exists:companies,id',
            'provider_id' => 'nullable|required_if:role,fornecedor|exists:providers,id',
            'companies' => 'nullable|array',
            'companies.*' => 'exists:companies,id',
        ];

        // Restringe gestores
        if ($currentUser->isGestor()) {
            $rules['role'] = 'required|in:gestor,fornecedor';
            // Força a empresa ativa do gestor caso não envie
            $request->merge(['company_id' => $request->company_id ?? $currentUser->company_id]);
        }

        $request->validate($rules);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'company_id' => $request->role === 'super_admin' ? null : $request->company_id,
            'provider_id' => $request->role === 'fornecedor' ? $request->provider_id : null,
            'active' => true,
        ]);

        // Vincula as empresas para gestão se o perfil for gestor
        if ($request->role === 'gestor') {
            if ($request->has('companies')) {
                $user->companies()->sync($request->companies);
            } else {
                $user->companies()->sync([$request->company_id]);
            }
        }

        return redirect()->route('users.index')->with('success', 'Usuário cadastrado com sucesso!');
    }

    /**
     * Atualiza dados de um usuário existente.
     */
    public function update(Request $request, User $user)
    {
        abort_if(auth()->user()->isFornecedor(), 403);
        $currentUser = auth()->user();

        // Se for gestor, garante que o usuário pertence à sua empresa
        if ($currentUser->isGestor()) {
            abort_if($user->company_id !== $currentUser->company_id, 403);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', Password::defaults()],
            'role' => 'required|in:super_admin,gestor,fornecedor',
            'company_id' => 'nullable|exists:companies,id',
            'provider_id' => 'nullable|required_if:role,fornecedor|exists:providers,id',
            'companies' => 'nullable|array',
            'companies.*' => 'exists:companies,id',
        ];

        // Restringe gestores
        if ($currentUser->isGestor()) {
            $rules['role'] = 'required|in:gestor,fornecedor';
            $request->merge(['company_id' => $request->company_id ?? $currentUser->company_id]);
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->role = $request->role;
        $user->company_id = $request->role === 'super_admin' ? null : $request->company_id;
        $user->provider_id = $request->role === 'fornecedor' ? $request->provider_id : null;
        $user->save();

        // Sincroniza as empresas para gestão se for gestor
        if ($request->role === 'gestor') {
            if ($request->has('companies')) {
                $user->companies()->sync($request->companies);
            } else {
                $user->companies()->sync([$request->company_id]);
            }
        } else {
            $user->companies()->detach();
        }

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Alterna o status ativo/inativo do usuário.
     */
    public function toggle(User $user)
    {
        abort_if(auth()->user()->isFornecedor(), 403);
        $currentUser = auth()->user();

        // Se for gestor, garante que o usuário pertence à sua empresa
        if ($currentUser->isGestor()) {
            abort_if($user->company_id !== $currentUser->company_id, 403);
        }

        // Não permite desativar a si próprio
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Você não pode desativar seu próprio usuário.');
        }

        $user->update([
            'active' => ! $user->active,
        ]);

        return back()->with('success', 'Status do usuário alterado com sucesso!');
    }
}
