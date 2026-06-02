<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure all contracts with existing provider_id have 'external'
        DB::table('contracts')
            ->whereNotNull('provider_id')
            ->update(['management_type' => 'external']);

        // 2. Get contracts that are currently internal (provider_id is null)
        $internalContracts = DB::table('contracts')
            ->whereNull('provider_id')
            ->get();

        $now = Carbon::now();

        foreach ($internalContracts as $contract) {
            // Find company name to create a specific internal provider
            $company = DB::table('companies')
                ->where('id', $contract->company_id)
                ->first();

            $companySuffix = $company ? $company->name : 'Geral';
            $providerName = "Controle Interno - {$companySuffix}";

            // Find or create the internal provider
            $providerId = DB::table('providers')
                ->where('name', $providerName)
                ->value('id');

            if (! $providerId) {
                $providerId = DB::table('providers')->insertGetId([
                    'name' => $providerName,
                    'cnpj' => null,
                    'email' => null,
                    'phone' => null,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Update the contract
            DB::table('contracts')
                ->where('id', $contract->id)
                ->update([
                    'provider_id' => $providerId,
                    'management_type' => 'internal',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Get the provider IDs of Controle Interno providers
        $providerIds = DB::table('providers')
            ->where('name', 'like', 'Controle Interno - %')
            ->pluck('id');

        // 2. Set provider_id to null for those contracts
        DB::table('contracts')
            ->whereIn('provider_id', $providerIds)
            ->update(['provider_id' => null]);

        // 3. Delete those providers
        DB::table('providers')
            ->whereIn('id', $providerIds)
            ->delete();
    }
};
