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
            ["type" => "Neuro Examination Fee", "parent" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "Drug Test", "parent" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "GSS Validation", "parent" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "Maritime FVCC", "parent" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "MERALCO", "parent" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "NCRPO TRUST/BID DOCS", "parent" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "SAF TRUST RECEIPT", "parent" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "PNP TRUST RECEIPT", "parent" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "Others", "parent" => "trust-receipt-funds", "lbp_bank_account_number" => "001-3012-1701"],
            ["type" => "PNP Support Fund", "parent" => "trust-liabilities", "lbp_bank_account_number" => "001-3012-1729"],
            ["type" => "Others", "parent" => "trust-liabilities", "lbp_bank_account_number" => "001-3012-1740"],
            ["type" => "Lost PA", "parent" => "general-funds", "lbp_bank_account_number" => "001-3012-1229"],
            ["type" => "BER", "parent" => "general-funds", "lbp_bank_account_number" => "001-3012-1349"],
            ["type" => "Overpayment", "parent" => "general-funds", "lbp_bank_account_number" => "001-3012-4729"],
            ["type" => "Others", "parent" => "general-funds", "lbp_bank_account_number" => "001-3012-9240"],
        ];

        foreach ($data as $item) {
            NatureOfCollection::create($item);
        }
    }
}