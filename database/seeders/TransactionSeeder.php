<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
            return;
        }

        $gajiCat = Category::where('user_id', $admin->id)->where('name', 'Gaji Utama')->first();
        $freelanceCat = Category::where('user_id', $admin->id)->where('name', 'Freelance / Bisnis')->first();
        $makanCat = Category::where('user_id', $admin->id)->where('name', 'Makanan & Minuman')->first();
        $transportCat = Category::where('user_id', $admin->id)->where('name', 'Transportasi')->first();
        $belanjaCat = Category::where('user_id', $admin->id)->where('name', 'Belanja Harian')->first();
        $tagihanCat = Category::where('user_id', $admin->id)->where('name', 'Tagihan & Utilitas')->first();

        $today = now();

        $sampleData = [
            // Incomes
            [
                'user_id' => $admin->id,
                'category_id' => $gajiCat?->id,
                'type' => 'income',
                'amount' => 7500000,
                'transaction_date' => $today->copy()->startOfMonth()->format('Y-m-d'),
                'description' => 'Gaji bulanan kantor',
            ],
            [
                'user_id' => $admin->id,
                'category_id' => $freelanceCat?->id,
                'type' => 'income',
                'amount' => 1250000,
                'transaction_date' => $today->copy()->subDays(4)->format('Y-m-d'),
                'description' => 'Project pembuatan landing page',
            ],
            // Expenses
            [
                'user_id' => $admin->id,
                'category_id' => $tagihanCat?->id,
                'type' => 'expense',
                'amount' => 450000,
                'transaction_date' => $today->copy()->startOfMonth()->addDays(2)->format('Y-m-d'),
                'description' => 'Bayar internet & listrik rumah',
            ],
            [
                'user_id' => $admin->id,
                'category_id' => $makanCat?->id,
                'type' => 'expense',
                'amount' => 65000,
                'transaction_date' => $today->copy()->subDays(2)->format('Y-m-d'),
                'description' => 'Makan siang ayam geprek + es teh',
            ],
            [
                'user_id' => $admin->id,
                'category_id' => $transportCat?->id,
                'type' => 'expense',
                'amount' => 50000,
                'transaction_date' => $today->copy()->subDays(1)->format('Y-m-d'),
                'description' => 'Isi bensin motor Pertamax',
            ],
            [
                'user_id' => $admin->id,
                'category_id' => $belanjaCat?->id,
                'type' => 'expense',
                'amount' => 175000,
                'transaction_date' => $today->format('Y-m-d'),
                'description' => 'Belanja mingguan supermarket',
            ],
            [
                'user_id' => $admin->id,
                'category_id' => $makanCat?->id,
                'type' => 'expense',
                'amount' => 35000,
                'transaction_date' => $today->format('Y-m-d'),
                'description' => 'Kopi susu gula aren',
            ],
        ];

        foreach ($sampleData as $data) {
            if ($data['category_id']) {
                Transaction::create($data);
            }
        }
    }
}
