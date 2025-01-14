<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Devices, gadgets, and accessories.'],
            ['name' => 'Clothing', 'description' => 'Apparel for men, women, and children.'],
            ['name' => 'Home and Kitchen', 'description' => 'Furniture, appliances, and decor for your home.'],
            ['name' => 'Health and Beauty', 'description' => 'Products for personal care and wellness.'],
            ['name' => 'Sports and Outdoors', 'description' => 'Equipment and gear for outdoor activities.'],
            ['name' => 'Toys and Games', 'description' => 'Games, puzzles, and toys for all ages.'],
            ['name' => 'Books', 'description' => 'Fiction, non-fiction, and educational books.'],
            ['name' => 'Automotive', 'description' => 'Parts, accessories, and tools for vehicles.'],
            ['name' => 'Jewelry', 'description' => 'Rings, necklaces, and other fine jewelry.'],
            ['name' => 'Baby Products', 'description' => 'Essentials for babies and toddlers.'],
            ['name' => 'Pet Supplies', 'description' => 'Food, toys, and accessories for pets.'],
            ['name' => 'Office Supplies', 'description' => 'Stationery, furniture, and office essentials.'],
            ['name' => 'Grocery and Gourmet Food', 'description' => 'Fresh food and gourmet items.'],
            ['name' => 'Music and Movies', 'description' => 'CDs, DVDs, and streaming devices.'],
            ['name' => 'Arts and Crafts', 'description' => 'Supplies for creative projects and hobbies.'],
            ['name' => 'Tools and Home Improvement', 'description' => 'Hardware and tools for home projects.'],
            ['name' => 'Travel and Luggage', 'description' => 'Travel bags, accessories, and essentials.'],
            ['name' => 'Garden and Outdoor', 'description' => 'Plants, tools, and decor for your garden.'],
            ['name' => 'Digital Goods', 'description' => 'E-books, software, and online subscriptions.'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
