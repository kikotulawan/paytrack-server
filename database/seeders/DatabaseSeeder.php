<?php

namespace Database\Seeders;

use App\Models\{User, UserInfo, Payment, NatureOfCollection};
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create superadmin user
        $superadmin = User::factory()->create([
            'email' => 'admin@paytrack.com',
        ]);

        UserInfo::create([
            'user_id' => $superadmin->id,
            'abbreviation' => 'PMSg',
            'firstname' => 'Paytrack',
            'middlename' => '',
            'lastname' => 'Administrator',
            'suffix' => '',
        ]);

        // Create staff user
        $staff = User::factory()->create([
            'email' => 'staff@paytrack.com',
        ]);

        UserInfo::create([
            'user_id' => $staff->id,
            'abbreviation' => 'PSSg',
            'firstname' => 'Paytrack',
            'middlename' => '',
            'lastname' => 'Staff',
            'suffix' => 'II',
        ]);

        // Create cashier user
        $cashier = User::factory()->create([
            'email' => 'ezi@paytrack.com',
        ]);

        UserInfo::create([
            'user_id' => $cashier->id,
            'abbreviation' => '',
            'firstname' => 'Ezi',
            'middlename' => '',
            'lastname' => 'Pura',
            'suffix' => '',
        ]);

        $this->call(RolePermissionSeeder::class);

        // Fetch all nature_of_collections
        $natureOfCollections = NatureOfCollection::all();
        $orStart = 10717663; // Starting OR number

        // Generate payments for August 2025
        $currentOr = $orStart;
        for ($day = 1; $day <= 31; $day++) {
            $dailyPaymentsCount = rand(1, 20); // Random number of payments per day (1 to 20)

            for ($i = 0; $i < $dailyPaymentsCount; $i++) {
                // Randomly select a nature_of_collection
                $natureOfCollection = $natureOfCollections->random();
                
                Payment::create([
                    'user_id' => $cashier->id,
                    'amount' => round(rand(10000, 1000000) / 100, 2), // Random float amount with 2 decimal places
                    'or' => $currentOr++, // Incremental OR number
                    'payor_name' => fake()->name(), // Random payor name
                    'payment_date' => Carbon::create(2025, 8, $day), // Specific date in August 2025
                    'mode_of_payment' => 'Cash', // Always Cash
                    'reference' => 'REF-' . fake()->unique()->numerify('########'), // Random reference number
                    'nature_of_collection' => $natureOfCollection->type, // Selected nature_of_collection type
                    'type' => $natureOfCollection->parent, // Parent type from the selected nature_of_collection
                ]);
            }
        }

        // Generate payments for August 2024
        for ($day = 1; $day <= 31; $day++) {
            $dailyPaymentsCount = rand(1, 20); // Random number of payments per day (1 to 20)

            for ($i = 0; $i < $dailyPaymentsCount; $i++) {
                // Randomly select a nature_of_collection
                $natureOfCollection = $natureOfCollections->random();

                Payment::create([
                    'user_id' => $cashier->id,
                    'amount' => round(rand(10000, 1000000) / 100, 2), // Random float amount with 2 decimal places
                    'or' => $currentOr++, // Incremental OR number
                    'payor_name' => fake()->name(), // Random payor name
                    'payment_date' => Carbon::create(2024, 8, $day), // Specific date in August 2024
                    'mode_of_payment' => 'Cash', // Always Cash
                    'reference' => 'REF-' . fake()->unique()->numerify('########'), // Random reference number
                    'nature_of_collection' => $natureOfCollection->type, // Selected nature_of_collection type
                    'type' => $natureOfCollection->parent, // Parent type from the selected nature_of_collection
                ]);
            }
        }
    }
}