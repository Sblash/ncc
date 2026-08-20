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
            'Nomi',
            'Cose',
            'Citta',
            'Animali',
            'Cibi',
            'Paesi',
            'Fiumi',
            'Mestiere',
            'Piante',
            'Film',
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate([
                'name' => $category,
            ]);
        }
    }
}
