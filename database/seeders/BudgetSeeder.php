<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $foodCat = Category::where('name', 'Makanan & Minuman')->first();

        // 1. Global Budget (Siklus Gajian 25 s/d 24 atau Bulan Ini)
        Budget::firstOrCreate([
            'name' => 'Anggaran Pengeluaran Bulan Ini',
            'category_id' => null,
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->endOfMonth()->format('Y-m-d'),
        ], [
            'amount' => 4500000,
            'notes' => 'Total batas pengeluaran operasional bulan ini',
        ]);

        // 2. Kategori Khusus Makan
        if ($foodCat) {
            Budget::firstOrCreate([
                'name' => 'Batas Makan & Kuliner',
                'category_id' => $foodCat->id,
                'start_date' => now()->startOfMonth()->format('Y-m-d'),
                'end_date' => now()->endOfMonth()->format('Y-m-d'),
            ], [
                'amount' => 1800000,
                'notes' => 'Alokasi makan siang kantor & jajan',
            ]);
        }
    }
}
