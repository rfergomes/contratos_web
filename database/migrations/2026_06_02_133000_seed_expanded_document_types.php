<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $newDocTypes = [
            [
                'name' => 'Nota Fiscal de Serviços Eletrônica (NFS-e)',
                'description' => 'Documento oficial de comprovação de prestação de serviços emitida via Prefeitura municipal.',
                'periodicity' => 'monthly',
                'required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Recibo de Pagamento Autônomo (RPA)',
                'description' => 'Utilizado para comprovação de prestação de serviços por prestador Pessoa Física.',
                'periodicity' => 'monthly',
                'required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Guia e Comprovante do Simples Nacional (DAS)',
                'description' => 'Documento de Arrecadação do Simples Nacional com respectivo comprovante de pagamento.',
                'periodicity' => 'monthly',
                'required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Guia e Comprovante de Tributos Federais (DARF/DARM)',
                'description' => 'Documento de Arrecadação de Receitas Federais e comprovante de pagamento de impostos.',
                'periodicity' => 'monthly',
                'required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Recibo de Entrega da DCTFWeb / EFD-Reinf',
                'description' => 'Comprovante de transmissão das declarações fiscais e previdenciárias de controle do Fisco Federal.',
                'periodicity' => 'monthly',
                'required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Recibo de Envio do eSocial',
                'description' => 'Protocolo de envio de informações trabalhistas e de folha de pagamento ao sistema do governo.',
                'periodicity' => 'monthly',
                'required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Extrato do PGDAS-D',
                'description' => 'Extrato gerador declaratório mensal de tributos do regime Simples Nacional.',
                'periodicity' => 'monthly',
                'required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Declaração do Simples Nacional (DEFIS)',
                'description' => 'Declaração anual simplificada de informações socioeconômicas e fiscais.',
                'periodicity' => 'annual',
                'required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Certidão Negativa de Débitos Estaduais (CND Estadual)',
                'description' => 'Comprovante de regularidade fiscal perante a Fazenda Pública Estadual.',
                'periodicity' => 'semi-annual',
                'required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Certidão Negativa de Débitos Municipais (CND Municipal)',
                'description' => 'Comprovante de regularidade fiscal perante a Fazenda Pública Municipal (Prefeitura).',
                'periodicity' => 'semi-annual',
                'required' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Folha de Pagamento e Encargos Sociais (GFIP/SEFIP/FGTS)',
                'description' => 'Comprovantes de salários e guias de recolhimento de encargos sociais de funcionários alocados.',
                'periodicity' => 'monthly',
                'required' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($newDocTypes as $docType) {
            DB::table('document_types')->updateOrInsert(
                ['name' => $docType['name']],
                [
                    'description' => $docType['description'],
                    'periodicity' => $docType['periodicity'],
                    'required' => $docType['required'],
                    'created_at' => $docType['created_at'],
                    'updated_at' => $docType['updated_at'],
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $names = [
            'Nota Fiscal de Serviços Eletrônica (NFS-e)',
            'Recibo de Pagamento Autônomo (RPA)',
            'Guia e Comprovante do Simples Nacional (DAS)',
            'Guia e Comprovante de Tributos Federais (DARF/DARM)',
            'Recibo de Entrega da DCTFWeb / EFD-Reinf',
            'Recibo de Envio do eSocial',
            'Extrato do PGDAS-D',
            'Declaração do Simples Nacional (DEFIS)',
            'Certidão Negativa de Débitos Estaduais (CND Estadual)',
            'Certidão Negativa de Débitos Municipais (CND Municipal)',
            'Folha de Pagamento e Encargos Sociais (GFIP/SEFIP/FGTS)',
        ];

        DB::table('document_types')->whereIn('name', $names)->delete();
    }
};
