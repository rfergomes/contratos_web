<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\DocumentType;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar Empresas Contratantes (Tenants)
        $companyAlpha = Company::create([
            'name' => 'Empresa Contratante Alpha LTDA',
            'cnpj' => '12.345.678/0001-90',
            'active' => true,
        ]);

        $companyOmega = Company::create([
            'name' => 'Empresa Contratante Omega S.A.',
            'cnpj' => '98.765.432/0001-10',
            'active' => true,
        ]);

        // 2. Criar Fornecedores
        $providerBeta = Provider::create([
            'name' => 'Fornecedor de Limpeza Beta S/S',
            'cnpj' => '11.222.333/0001-44',
            'email' => 'contato@limpezabeta.com.br',
            'phone' => '(19) 3344-5566',
            'active' => true,
        ]);

        $providerGama = Provider::create([
            'name' => 'Segurança e Vigilância Gama LTDA',
            'cnpj' => '55.666.777/0001-88',
            'email' => 'comercial@vigilanciagama.com.br',
            'phone' => '(19) 99888-7766',
            'active' => true,
        ]);

        // 3. Criar Tipos de Documentação padrão
        $docTypes = [
            [
                'name' => 'Certidão Negativa de Débitos do FGTS (CRF)',
                'description' => 'Comprova a regularidade do empregador perante o FGTS.',
                'periodicity' => 'monthly',
                'required' => true,
            ],
            [
                'name' => 'Certidão de Débitos Relativos a Créditos Tributários Federais e à Dívida Ativa da União (INSS/RFB)',
                'description' => 'Certidão conjunta sobre tributos federais e previdência social.',
                'periodicity' => 'monthly',
                'required' => true,
            ],
            [
                'name' => 'Certidão Negativa de Débitos Trabalhistas (CNDT)',
                'description' => 'Comprova a inexistência de débitos perante a Justiça do Trabalho.',
                'periodicity' => 'semi-annual',
                'required' => true,
            ],
            [
                'name' => 'Apólice de Seguro de Acidentes de Trabalho / Seguro de Vida',
                'description' => 'Seguro cobrindo acidentes dos funcionários alocados no serviço.',
                'periodicity' => 'annual',
                'required' => true,
            ],
            [
                'name' => 'Contrato Social ou Estatuto Atualizado',
                'description' => 'Documento constitutivo da empresa fornecedora.',
                'periodicity' => 'once',
                'required' => true,
            ],
        ];

        foreach ($docTypes as $doc) {
            DocumentType::create($doc);
        }

        // 4. Criar Usuários com Perfis Distintos

        // Super Admin (Sem empresa vinculada, visualiza tudo)
        User::create([
            'name' => 'Administrador Global',
            'email' => 'admin@contratos.local',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'active' => true,
        ]);

        // Gestor da Empresa Alpha
        $gestorAlpha = User::create([
            'name' => 'Gestor Contratos Alpha',
            'email' => 'gestor@alpha.local',
            'password' => Hash::make('password'),
            'role' => 'gestor',
            'company_id' => $companyAlpha->id,
            'active' => true,
        ]);
        $gestorAlpha->companies()->attach([$companyAlpha->id, $companyOmega->id]);

        // Gestor da Empresa Omega
        $gestorOmega = User::create([
            'name' => 'Gestora Contratos Omega',
            'email' => 'gestor@omega.local',
            'password' => Hash::make('password'),
            'role' => 'gestor',
            'company_id' => $companyOmega->id,
            'active' => true,
        ]);
        $gestorOmega->companies()->attach([$companyOmega->id]);

        // Fornecedor Beta (Acesso ao painel do fornecedor)
        User::create([
            'name' => 'Preposto Fornecedor Beta',
            'email' => 'fornecedor@beta.local',
            'password' => Hash::make('password'),
            'role' => 'fornecedor',
            'provider_id' => $providerBeta->id,
            'active' => true,
        ]);

        // Fornecedor Gama (Acesso ao painel do fornecedor Gama)
        User::create([
            'name' => 'Preposto Fornecedor Gama',
            'email' => 'fornecedor@gama.local',
            'password' => Hash::make('password'),
            'role' => 'fornecedor',
            'provider_id' => $providerGama->id,
            'active' => true,
        ]);

        // 5. Criar Contratos de Teste
        $contract1 = Contract::create([
            'company_id' => $companyAlpha->id,
            'provider_id' => $providerBeta->id,
            'contract_number' => 'CTR-2026-001',
            'title' => 'Prestação de Serviços de Limpeza e Conservação',
            'description' => 'Contrato de prestação de serviços continuados de conservação predial e limpeza.',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
        ]);

        $contract2 = Contract::create([
            'company_id' => $companyAlpha->id,
            'provider_id' => $providerGama->id,
            'contract_number' => 'CTR-2026-002',
            'title' => 'Segurança Patrimonial e Monitoramento de Câmeras',
            'description' => 'Serviços de vigilância armada e segurança perimetral.',
            'start_date' => '2026-01-15',
            'end_date' => '2027-01-14',
            'status' => 'active',
        ]);

        // 6. Criar Obrigações Documentais (GED) vinculadas
        $types = DocumentType::all();

        // Para o Contrato 1 (Fornecedor Beta):

        // CRF do FGTS - Pendente (Atrasado)
        ContractDocument::create([
            'contract_id' => $contract1->id,
            'document_type_id' => $types->where('name', 'Certidão Negativa de Débitos do FGTS (CRF)')->first()->id,
            'due_date' => now()->subDays(5), // Vencido há 5 dias
            'status' => 'pending',
        ]);

        // INSS/RFB - Enviado (Aguardando Análise)
        // Criar uma pasta e arquivo dummy para teste de download
        $dummyPath = 'private/documents/contracts/'.$contract1->id.'/receita_federal.pdf';
        Storage::put($dummyPath, 'Dummy PDF content for testing');

        ContractDocument::create([
            'contract_id' => $contract1->id,
            'document_type_id' => $types->where('name', 'Certidão de Débitos Relativos a Créditos Tributários Federais e à Dívida Ativa da União (INSS/RFB)')->first()->id,
            'file_path' => $dummyPath,
            'original_name' => 'certidao_receita_federal_maio.pdf',
            'due_date' => now()->addDays(15),
            'status' => 'submitted',
            'submitted_at' => now()->subDay(),
        ]);

        // CNDT - Recusado
        ContractDocument::create([
            'contract_id' => $contract1->id,
            'document_type_id' => $types->where('name', 'Certidão Negativa de Débitos Trabalhistas (CNDT)')->first()->id,
            'file_path' => 'private/documents/contracts/'.$contract1->id.'/cndt_velha.pdf',
            'original_name' => 'cndt_ano_passado.pdf',
            'due_date' => now()->addDays(20),
            'status' => 'rejected',
            'rejection_reason' => 'O documento enviado está vencido. Favor extrair a certidão atualizada no site do TST.',
            'submitted_at' => now()->subDays(3),
            'reviewed_by' => $gestorAlpha->id,
        ]);

        // Contrato Social - Aprovado
        ContractDocument::create([
            'contract_id' => $contract1->id,
            'document_type_id' => $types->where('name', 'Contrato Social ou Estatuto Atualizado')->first()->id,
            'file_path' => 'private/documents/contracts/'.$contract1->id.'/contrato_social.pdf',
            'original_name' => 'contrato_social_assinado.pdf',
            'due_date' => now()->subDays(10),
            'status' => 'approved',
            'submitted_at' => now()->subDays(12),
            'approved_at' => now()->subDays(11),
            'reviewed_by' => $gestorAlpha->id,
        ]);
    }
}
