<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Exibe os dados do perfil do usuário logado.
     */
    public function index()
    {
        $user = auth()->user();

        return view('profile.index', compact('user'));
    }

    /**
     * Atualiza dados de nome, e-mail ou senha do perfil.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Se preencheu a nova senha, criptografa e salva
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Trata o upload da foto de perfil se houver
        if ($request->hasFile('profile_photo')) {
            // Remove a foto antiga se existir
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->profile_photo_path = $path;
        }

        $user->save();

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }
}
