<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Expenses
            ['name' => 'Makanan & Minuman', 'type' => 'expense', 'icon' => 'utensils', 'color' => '#f97316'],
            ['name' => 'Transportasi', 'type' => 'expense', 'icon' => 'car', 'color' => '#3b82f6'],
            ['name' => 'Belanja Harian', 'type' => 'expense', 'icon' => 'shopping-cart', 'color' => '#8b5cf6'],
            ['name' => 'Tagihan & Utilitas', 'type' => 'expense', 'icon' => 'receipt', 'color' => '#ef4444'],
            ['name' => 'Hiburan & Hobi', 'type' => 'expense', 'icon' => 'gamepad-2', 'color' => '#ec4899'],
            ['name' => 'Kesehatan', 'type' => 'expense', 'icon' => 'heart-pulse', 'color' => '#06b6d4'],
            ['name' => 'Pendidikan', 'type' => 'expense', 'icon' => 'graduation-cap', 'color' => '#eab308'],
            ['name' => 'Tempat Tinggal', 'type' => 'expense', 'icon' => 'home', 'color' => '#6366f1'],
            ['name' => 'Pengeluaran Lain', 'type' => 'expense', 'icon' => 'layers', 'color' => '#64748b'],

            // Incomes
            ['name' => 'Gaji Utama', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#10b981'],
            ['name' => 'Bonus & THR', 'type' => 'income', 'icon' => 'gift', 'color' => '#14b8a6'],
            ['name' => 'Freelance / Bisnis', 'type' => 'income', 'icon' => 'laptop', 'color' => '#3b82f6'],
            ['name' => 'Investasi', 'type' => 'income', 'icon' => 'trending-up', 'color' => '#059669'],
            ['name' => 'Pemasukan Lain', 'type' => 'income', 'icon' => 'wallet', 'color' => '#64748b'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name'], 'type' => $category['type']],
                $category
            );
        }
    }
}
