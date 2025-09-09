<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NatureOfCollection;

class NatureOfCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ["type" => "Neuro Examination Fee", "account_name" => "PNP Neuro Psycho Val Fee Fund", "particular" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "Drug Test", "account_name" => "PNP Crime Lab Svc Fund", "particular" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "GSS Validation", "account_name" => "PNP Firearms License Fee Fund", "particular" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "Electric Consumption", "account_name" => "NCRPO Quarters Utilities", "particular" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "NCRPO TRUST/BID DOCS", "account_name" => "PNP SAF Trust Receipts Fund", "particular" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "PNP Support Fund", "account_name" => "PNP Support Fund", "particular" => "trust-liabilities", "lbp_bank_account_number" => "001-3012-1729"],
            ["type" => "Lost Firearms", "account_name" => "Bureau of the Treasury", "particular" => "general-funds", "lbp_bank_account_number" => "001-3012-1229"],
            ["type" => "Overpayment", "account_name" => "Bureau of the Treasury", "particular" => "general-funds", "lbp_bank_account_number" => "001-3012-4729"],
        ];

        foreach ($data as $item) {
            NatureOfCollection::create($item);
        }
    }
}