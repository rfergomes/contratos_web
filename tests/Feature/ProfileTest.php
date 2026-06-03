<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Teste: Página de perfil carrega para usuário autenticado.
     */
    public function test_profile_page_loads_for_authenticated_user(): void
    {
        $user = User::create([
            'name' => 'Teste Rodrigo',
            'email' => 'rodrigo@teste.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('profile.index'));
        $response->assertStatus(200);
        $response->assertSee('Informações Cadastrais');
    }

    /**
     * Teste: Usuário pode atualizar nome, e-mail e fazer upload de foto de perfil.
     */
    public function test_user_can_update_profile_and_upload_photo(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Teste Rodrigo',
            'email' => 'rodrigo@teste.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->create('profile.jpg', 100, 'image/jpeg');

        $response = $this->put(route('profile.update'), [
            'name' => 'Rodrigo Editado',
            'email' => 'rodrigo.editado@teste.com',
            'profile_photo' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Rodrigo Editado', $user->name);
        $this->assertEquals('rodrigo.editado@teste.com', $user->email);
        $this->assertNotNull($user->profile_photo_path);

        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    /**
     * Teste: Foto antiga é removida ao fazer upload de uma nova foto de perfil.
     */
    public function test_old_photo_is_deleted_when_new_photo_is_uploaded(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Teste Rodrigo',
            'email' => 'rodrigo@teste.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'profile_photo_path' => 'profile_photos/old_photo.jpg',
        ]);

        // Crie o arquivo falso correspondente à foto antiga
        Storage::disk('public')->put('profile_photos/old_photo.jpg', 'conteudo');

        $this->actingAs($user);

        $newFile = UploadedFile::fake()->create('new_profile.jpg', 100, 'image/jpeg');

        $response = $this->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => $newFile,
        ]);

        $response->assertRedirect();
        $user->refresh();

        // A foto antiga deve ter sido removida e a nova deve existir
        Storage::disk('public')->assertMissing('profile_photos/old_photo.jpg');
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }
}
