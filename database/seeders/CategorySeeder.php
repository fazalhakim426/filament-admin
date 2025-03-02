<?php

namespace Database\Seeders;
use App\Models\Category;
use App\Models\SubCategory;

use Illuminate\Database\Seeder;
class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Electronics' => [
                'description' => 'Devices, gadgets, and accessories.',
                'sub_categories' => [
                    'Mobile Phones', 'Laptops', 'Tablets', 'Cameras', 'Smart Watches'
                ]
            ],
            'Clothing' => [
                'description' => 'Apparel for men, women, and children.',
                'sub_categories' => [
                    'Men\'s Clothing', 'Women\'s Clothing', 'Kids\' Clothing', 'Footwear', 'Accessories'
                ]
            ],
            'Home and Kitchen' => [
                'description' => 'Furniture, appliances, and decor for your home.',
                'sub_categories' => [
                    'Furniture', 'Kitchenware', 'Home Decor', 'Lighting', 'Bedding'
                ]
            ],
            'Health and Beauty' => [
                'description' => 'Products for personal care and wellness.',
                'sub_categories' => [
                    'Skincare', 'Haircare', 'Makeup', 'Supplements', 'Personal Care'
                ]
            ],
            'Sports and Outdoors' => [
                'description' => 'Equipment and gear for outdoor activities.',
                'sub_categories' => [
                    'Gym Equipment', 'Outdoor Gear', 'Sportswear', 'Camping Equipment', 'Cycling'
                ]
            ],
            'Toys and Games' => [
                'description' => 'Games, puzzles, and toys for all ages.',
                'sub_categories' => [
                    'Board Games', 'Action Figures', 'Educational Toys', 'Puzzles', 'Video Games'
                ]
            ],
            'Books' => [
                'description' => 'Fiction, non-fiction, and educational books.',
                'sub_categories' => [
                    'Fiction', 'Non-Fiction', 'Educational', 'Comics', 'Children\'s Books'
                ]
            ],
            'Automotive' => [
                'description' => 'Parts, accessories, and tools for vehicles.',
                'sub_categories' => [
                    'Car Accessories', 'Motorcycle Accessories', 'Spare Parts', 'Car Care', 'GPS & Electronics'
                ]
            ],
            'Jewelry' => [
                'description' => 'Rings, necklaces, and other fine jewelry.',
                'sub_categories' => [
                    'Rings', 'Necklaces', 'Bracelets', 'Earrings', 'Watches'
                ]
            ],
            'Baby Products' => [
                'description' => 'Essentials for babies and toddlers.',
                'sub_categories' => [
                    'Baby Clothing', 'Baby Toys', 'Diapers & Wipes', 'Feeding & Nursing', 'Baby Gear'
                ]
            ],
            'Pet Supplies' => [
                'description' => 'Food, toys, and accessories for pets.',
                'sub_categories' => [
                    'Pet Food', 'Pet Toys', 'Pet Grooming', 'Pet Health', 'Pet Accessories'
                ]
            ],
            'Office Supplies' => [
                'description' => 'Stationery, furniture, and office essentials.',
                'sub_categories' => [
                    'Stationery', 'Office Chairs', 'Printers & Scanners', 'Desks', 'Filing & Storage'
                ]
            ],
            'Grocery and Gourmet Food' => [
                'description' => 'Fresh food and gourmet items.',
                'sub_categories' => [
                    'Snacks', 'Beverages', 'Dairy & Eggs', 'Meat & Seafood', 'Organic Food'
                ]
            ],
            'Music and Movies' => [
                'description' => 'CDs, DVDs, and streaming devices.',
                'sub_categories' => [
                    'Music CDs', 'Vinyl Records', 'DVDs', 'Streaming Devices', 'Concert Merchandise'
                ]
            ],
            'Arts and Crafts' => [
                'description' => 'Supplies for creative projects and hobbies.',
                'sub_categories' => [
                    'Painting Supplies', 'Drawing Supplies', 'DIY Kits', 'Sewing & Fabric', 'Craft Tools'
                ]
            ],
            'Tools and Home Improvement' => [
                'description' => 'Hardware and tools for home projects.',
                'sub_categories' => [
                    'Power Tools', 'Hand Tools', 'Building Materials', 'Paint & Wallpaper', 'Safety Equipment'
                ]
            ],
            'Travel and Luggage' => [
                'description' => 'Travel bags, accessories, and essentials.',
                'sub_categories' => [
                    'Suitcases', 'Backpacks', 'Travel Accessories', 'Duffel Bags', 'Luggage Sets'
                ]
            ],
            'Garden and Outdoor' => [
                'description' => 'Plants, tools, and decor for your garden.',
                'sub_categories' => [
                    'Plants & Seeds', 'Gardening Tools', 'Outdoor Furniture', 'BBQ & Grilling', 'Lawn Care'
                ]
            ],
            'Digital Goods' => [
                'description' => 'E-books, software, and online subscriptions.',
                'sub_categories' => [
                    'E-books', 'Software', 'Online Courses', 'Digital Art', 'Subscription Services'
                ]
            ],
        ];

        foreach ($categories as $name => $data) {
            $category = Category::firstOrCreate(['name' => $name], [
                'description' => $data['description']
            ]);

            foreach ($data['sub_categories'] as $subCategoryName) {
                SubCategory::firstOrCreate([
                    'category_id' => $category->id,
                    'name' => $subCategoryName,
                    'description' => 'description is null'
                ]);
            }
        }
    }
}
