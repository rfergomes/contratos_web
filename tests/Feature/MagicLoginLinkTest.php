<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\ContractRequest;
use App\Models\DocumentType;
use App\Models\Provider;
use App\Models\TemporaryAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MagicLoginLinkTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $gestorAlpha;
    protected User $fornecedorBeta;
    protected Company $companyAlpha;
    protected Provider $providerBeta;
    protected Contract $contract;
    protected ContractRequest $contractRequest;
    protected ContractDocument $contractDocument;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyAlpha = Company::create(['name' => 'Alpha LTDA', 'cnpj' => '12.345.678/0001-90', 'active' => true]);
        $this->providerBeta = Provider::create(['name' => 'Beta Serviços', 'cnpj' => '11.222.333/0001-44', 'active' => true]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'active' => true,
        ]);

        $this->gestorAlpha = User::create([
            'name' => 'Gestor Alpha',
            'email' => 'gestor@alpha.com',
            'password' => bcrypt('password'),
            'role' => 'gestor',
            'company_id' => $this->companyAlpha->id,
            'active' => true,
        ]);
        $this->gestorAlpha->companies()->attach($this->companyAlpha->id);

        $this->fornecedorBeta = User::create([
            'name' => 'Fornecedor Beta',
            'email' => 'fornecedor@beta.com',
            'password' => bcrypt('password'),
            'role' => 'fornecedor',
            'provider_id' => $this->providerBeta->id,
            'active' => true,
        ]);

        $this->contract = Contract::create([
            'company_id' => $this->companyAlpha->id,
            'provider_id' => $this->providerBeta->id,
            'contract_number' => 'CTR-999',
            'title' => 'Serviços de TI',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'alert_days' => 30,
            'status' => 'active',
            'signature_validated' => true,
            'responsible_id' => $this->gestorAlpha->id,
        ]);

        $this->contractRequest = ContractRequest::create([
            'contract_id' => $this->contract->id,
            'sender_type' => 'company',
            'user_id' => $this->gestorAlpha->id,
            'type' => 'amendment',
            'title' => 'Pedido de Aditivo',
            'description' => 'Ajuste de escopo.',
            'status' => 'pending',
        ]);

        $docType = DocumentType::create([
            'name' => 'Alvará Municipal',
            'periodicity' => 'annual',
            'required' => true,
        ]);

        $this->contractDocument = ContractDocument::create([
            'contract_id' => $this->contract->id,
            'document_type_id' => $docType->id,
            'status' => 'pending',
            'due_date' => now()->addDays(5)->toDateString(),
        ]);
    }

    /**
     * Teste: Fornecedores não podem gerar links do WhatsApp/Magic Links.
     */
    public function test_fornecedor_cannot_generate_whatsapp_magic_links(): void
    {
        $this->actingAs($this->fornecedorBeta);

        $response1 = $this->postJson(route('contracts.requests.whatsapp-link', $this->contractRequest));
        $response1->assertStatus(403);

        $response2 = $this->postJson(route('contracts.documents.whatsapp-link', $this->contractDocument));
        $response2->assertStatus(403);
    }

    /**
     * Teste: Gestores podem gerar links para solicitações pendentes.
     */
    public function test_gestor_can_generate_request_whatsapp_link(): void
    {
        $this->actingAs($this->gestorAlpha);

        $response = $this->postJson(route('contracts.requests.whatsapp-link', $this->contractRequest));
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'link', 'message']);

        $token = TemporaryAccessToken::first();
        $this->assertNotNull($token);
        $this->assertEquals(ContractRequest::class, $token->tokenable_type);
        $this->assertEquals($this->contractRequest->id, $token->tokenable_id);
    }

    /**
     * Teste: Gestores podem gerar links para obrigações documentais.
     */
    public function test_gestor_can_generate_document_whatsapp_link(): void
    {
        $this->actingAs($this->gestorAlpha);

        $response = $this->postJson(route('contracts.documents.whatsapp-link', $this->contractDocument));
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'link', 'message']);

        $token = TemporaryAccessToken::first();
        $this->assertNotNull($token);
        $this->assertEquals(ContractDocument::class, $token->tokenable_type);
        $this->assertEquals($this->contractDocument->id, $token->tokenable_id);
    }

    /**
     * Teste: Token inválido ou expirado redireciona para login com erro (GET e POST).
     */
    public function test_invalid_or_expired_token_redirects_to_login(): void
    {
        // 1. Token inexistente GET
        $response = $this->get(route('public.access', 'invalid-token-12345'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'O link de acesso expirou ou é inválido.');

        // 2. Token inexistente POST
        $responsePost = $this->post(route('public.access.authenticate', 'invalid-token-12345'));
        $responsePost->assertRedirect(route('login'));
        $responsePost->assertSessionHas('error', 'O link de acesso expirou ou é inválido.');

        // 3. Token expirado GET
        $token = TemporaryAccessToken::generateFor($this->contractRequest);
        $token->update(['expires_at' => now()->subMinutes(1)]);

        $response2 = $this->get(route('public.access', $token->token));
        $response2->assertRedirect(route('login'));
        $response2->assertSessionHas('error', 'O link de acesso expirou ou é inválido.');
        $this->assertDatabaseMissing('temporary_access_tokens', ['id' => $token->id]);

        // 4. Token expirado POST
        $token2 = TemporaryAccessToken::generateFor($this->contractRequest);
        $token2->update(['expires_at' => now()->subMinutes(1)]);

        $response2Post = $this->post(route('public.access.authenticate', $token2->token));
        $response2Post->assertRedirect(route('login'));
        $response2Post->assertSessionHas('error', 'O link de acesso expirou ou é inválido.');
        $this->assertDatabaseMissing('temporary_access_tokens', ['id' => $token2->id]);
    }

    /**
     * Teste: Magic link de solicitação exibe landing page no GET e faz login/redireciona no POST.
     */
    public function test_magic_link_login_and_redirect_for_request(): void
    {
        $token = TemporaryAccessToken::generateFor($this->contractRequest);

        // 1. GET: Exibe landing page
        $responseGet = $this->get(route('public.access', $token->token));
        $responseGet->assertStatus(200);
        $responseGet->assertViewIs('auth.magic_login');
        
        // Não deve ter logado ainda
        $this->assertFalse(Auth::check());
        // Token não deve ter sido apagado
        $this->assertDatabaseHas('temporary_access_tokens', ['id' => $token->id]);

        // 2. POST: Efetua o login e consome o token
        $responsePost = $this->post(route('public.access.authenticate', $token->token));
        
        // Deve autenticar o fornecedorBeta
        $this->assertTrue(Auth::check());
        $this->assertEquals($this->fornecedorBeta->id, Auth::id());

        // Deve redirecionar para a página do contrato com a âncora do timeline
        $responsePost->assertRedirect(route('contracts.show', $this->contract->id) . '#timeline');

        // Deve deletar o token para uso único (Single-Use)
        $this->assertDatabaseMissing('temporary_access_tokens', ['id' => $token->id]);
    }

    /**
     * Teste: Magic link de documento exibe landing page no GET e faz login/redireciona no POST.
     */
    public function test_magic_link_login_and_redirect_for_document(): void
    {
        $token = TemporaryAccessToken::generateFor($this->contractDocument);

        // 1. GET: Exibe landing page
        $responseGet = $this->get(route('public.access', $token->token));
        $responseGet->assertStatus(200);
        $responseGet->assertViewIs('auth.magic_login');

        // Não deve ter logado ainda
        $this->assertFalse(Auth::check());
        // Token não deve ter sido apagado
        $this->assertDatabaseHas('temporary_access_tokens', ['id' => $token->id]);

        // 2. POST: Efetua o login e consome o token
        $responsePost = $this->post(route('public.access.authenticate', $token->token));

        // Deve autenticar o fornecedorBeta
        $this->assertTrue(Auth::check());
        $this->assertEquals($this->fornecedorBeta->id, Auth::id());

        // Deve redirecionar para o GED
        $responsePost->assertRedirect(route('ged.index'));

        // Deve deletar o token
        $this->assertDatabaseMissing('temporary_access_tokens', ['id' => $token->id]);
    }
}
