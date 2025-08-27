<?php
// Product Seeder - Automatically populates products table with sample data
function seedProducts($conn) {
    try {
        // Check if products already exist
        $check_sql = "SELECT COUNT(*) as count FROM products";
        $result = $conn->query($check_sql);
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            // Products already exist, skip seeding
            return;
        }
        
        // Sample products data
        $sampleProducts = [
            [
                'name' => "Elegant Brown Summer Dress",
                'description' => "Beautiful brown summer dress with elegant design. Perfect for warm weather and special occasions. Features a flattering silhouette and comfortable fit.",
                'category' => "dresses",
                'price' => 149.99,
                'high_price' => 149.99,
                'stock' => 22,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743960838/vybe/images/b3levgyczjrzrdcr4pxi.jpg",
                'gender' => "women",
                'discount' => 15,
                'sizes' => ["XS", "S", "M", "L", "XL"],
                'colors' => ["Brown", "Beige"]
            ],
            [
                'name' => "Cement Color Evening Dress",
                'description' => "Stunning cement-colored evening dress perfect for formal events and parties. Features elegant design with premium fabric and sophisticated styling.",
                'category' => "dresses",
                'price' => 189.99,
                'high_price' => 189.99,
                'stock' => 18,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743959867/vybe/images/bl8ognimfsgtxvdowvt1.jpg",
                'gender' => "women",
                'discount' => 20,
                'sizes' => ["S", "M", "L", "XL"],
                'colors' => ["Cement", "Gray"]
            ],
            [
                'name' => "Premium Cotton Men's T-Shirt",
                'description' => "High-quality cotton t-shirt with perfect fit for men. Available in multiple colors and sizes. Great for everyday wear and casual occasions.",
                'category' => "t-shirts",
                'price' => 39.99,
                'high_price' => 39.99,
                'stock' => 45,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743996356/vybe/images/diczb7fx588i42ebst9j.jpg",
                'gender' => "men",
                'discount' => 0,
                'sizes' => ["S", "M", "L", "XL", "XXL"],
                'colors' => ["White", "Black", "Gray", "Navy"]
            ],
            [
                'name' => "Stylish Women's Top",
                'description' => "Trendy and fashionable women's top perfect for casual and semi-formal occasions. Features modern design with comfortable fit and versatile styling.",
                'category' => "tops",
                'price' => 69.99,
                'high_price' => 69.99,
                'stock' => 28,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743960279/vybe/images/dohooqr21q0ouqlauwd1.jpg",
                'gender' => "women",
                'discount' => 10,
                'sizes' => ["XS", "S", "M", "L", "XL"],
                'colors' => ["White", "Black", "Pink"]
            ],
            [
                'name' => "Classic Men's Pants",
                'description' => "Classic and comfortable men's pants perfect for everyday wear. Features durable fabric and comfortable fit suitable for both casual and semi-formal occasions.",
                'category' => "pants",
                'price' => 79.99,
                'high_price' => 79.99,
                'stock' => 35,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743996296/vybe/images/dojlaggl6rwuqjkpqd9i.jpg",
                'gender' => "men",
                'discount' => 0,
                'sizes' => ["S", "M", "L", "XL", "XXL"],
                'colors' => ["Black", "Navy", "Gray"]
            ],
            [
                'name' => "Fashionable Men's Casual Shirt",
                'description' => "Stylish casual shirt for men with modern design. Perfect for casual outings and everyday wear. Features comfortable fabric and trendy styling.",
                'category' => "shirts",
                'price' => 89.99,
                'high_price' => 89.99,
                'stock' => 30,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743952549/vybe/images/fnoluqeu2jdkzcnlahnk.jpg",
                'gender' => "men",
                'discount' => 5,
                'sizes' => ["S", "M", "L", "XL"],
                'colors' => ["Blue", "White", "Gray"]
            ],
            [
                'name' => "Beautiful Women's Party Dress",
                'description' => "Gorgeous party dress perfect for special occasions and celebrations. Features elegant design with premium fabric and flattering silhouette.",
                'category' => "dresses",
                'price' => 199.99,
                'high_price' => 199.99,
                'stock' => 20,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743960919/vybe/images/hhmt6rb48boeudzlswop.jpg",
                'gender' => "women",
                'discount' => 25,
                'sizes' => ["XS", "S", "M", "L", "XL"],
                'colors' => ["Red", "Black", "Blue"]
            ],
            [
                'name' => "Elegant Women's Top",
                'description' => "Stylish and elegant women's top perfect for casual and semi-formal occasions. Features modern design with comfortable fit and versatile styling.",
                'category' => "tops",
                'price' => 74.99,
                'high_price' => 74.99,
                'stock' => 25,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743959559/vybe/images/gt985edjupszmeevjdk7.jpg",
                'gender' => "women",
                'discount' => 12,
                'sizes' => ["XS", "S", "M", "L", "XL"],
                'colors' => ["White", "Black", "Pink", "Blue"]
            ],
            [
                'name' => "Men's Formal Shirt & Pant Set",
                'description' => "Professional men's formal shirt and pant combination perfect for office wear and formal occasions. Features premium fabric and comfortable fit.",
                'category' => "formal-wear",
                'price' => 159.99,
                'high_price' => 159.99,
                'stock' => 18,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743954285/vybe/images/iy2rqzm5hjh4a5lrlt2i.jpg",
                'gender' => "men",
                'discount' => 18,
                'sizes' => ["S", "M", "L", "XL", "XXL"],
                'colors' => ["White", "Blue", "Gray", "Black"]
            ],
            [
                'name' => "Elegant Women's Evening Dress",
                'description' => "Stunning evening dress perfect for special occasions and formal events. Features elegant design with premium fabric and sophisticated styling.",
                'category' => "dresses",
                'price' => 179.99,
                'high_price' => 179.99,
                'stock' => 16,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743960607/vybe/images/jqwwg6l3kcvgzjczrdzn.jpg",
                'gender' => "women",
                'discount' => 22,
                'sizes' => ["XS", "S", "M", "L", "XL"],
                'colors' => ["Black", "Red", "Blue", "Purple"]
            ],
            [
                'name' => "Men's Casual Shirt & Pant Combo",
                'description' => "Comfortable casual shirt and pant combination perfect for everyday wear and casual outings. Features durable fabric and relaxed fit.",
                'category' => "casual-wear",
                'price' => 119.99,
                'high_price' => 119.99,
                'stock' => 24,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743955696/vybe/images/kxuuls2qsszpgmfx9zp7.jpg",
                'gender' => "men",
                'discount' => 8,
                'sizes' => ["S", "M", "L", "XL", "XXL"],
                'colors' => ["Blue", "Gray", "White", "Black"]
            ],
            [
                'name' => "Trendy Women's Top",
                'description' => "Fashionable and trendy women's top perfect for casual and semi-formal occasions. Features modern design with comfortable fit and versatile styling.",
                'category' => "tops",
                'price' => 64.99,
                'high_price' => 64.99,
                'stock' => 30,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743960749/vybe/images/lh2fk2c7u9vrnuplz74t.jpg",
                'gender' => "women",
                'discount' => 15,
                'sizes' => ["XS", "S", "M", "L", "XL"],
                'colors' => ["White", "Black", "Pink", "Yellow"]
            ],
            [
                'name' => "Classic Men's Formal Shirt",
                'description' => "Timeless formal shirt perfect for office wear and business meetings. Features premium cotton fabric and professional styling.",
                'category' => "shirts",
                'price' => 94.99,
                'high_price' => 94.99,
                'stock' => 28,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743953499/vybe/images/oqfx0dutl0uwt2asttja.jpg",
                'gender' => "men",
                'discount' => 5,
                'sizes' => ["S", "M", "L", "XL", "XXL"],
                'colors' => ["White", "Blue", "Pink", "Gray"]
            ],
            [
                'name' => "Beautiful Women's Summer Dress",
                'description' => "Gorgeous summer dress perfect for warm weather and casual occasions. Features light fabric and comfortable fit with elegant design.",
                'category' => "dresses",
                'price' => 139.99,
                'high_price' => 139.99,
                'stock' => 22,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743960753/vybe/images/rseu4zhe2wlvl6xjiu6x.jpg",
                'gender' => "women",
                'discount' => 20,
                'sizes' => ["XS", "S", "M", "L", "XL"],
                'colors' => ["Blue", "Green", "Yellow", "Pink"]
            ],
            [
                'name' => "Men's Casual Shorts",
                'description' => "Comfortable and stylish men's casual shorts perfect for summer wear and casual outings. Features breathable fabric and relaxed fit.",
                'category' => "shorts",
                'price' => 54.99,
                'high_price' => 54.99,
                'stock' => 35,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743952134/vybe/images/x3wdzmemfqvgpyrhslaw.jpg",
                'gender' => "men",
                'discount' => 10,
                'sizes' => ["S", "M", "L", "XL", "XXL"],
                'colors' => ["Black", "Gray", "Blue", "Khaki"]
            ],
            [
                'name' => "Men's Athletic Shorts",
                'description' => "High-quality athletic shorts perfect for sports and workout activities. Features moisture-wicking fabric and comfortable elastic waistband.",
                'category' => "shorts",
                'price' => 69.99,
                'high_price' => 69.99,
                'stock' => 28,
                'image' => "https://res.cloudinary.com/ds9ufpxom/image/upload/v1743996153/vybe/images/zlzrcdrttkpvg0bsydzk.jpg",
                'gender' => "men",
                'discount' => 15,
                'sizes' => ["S", "M", "L", "XL", "XXL"],
                'colors' => ["Black", "Gray", "Blue", "Red"]
            ]
        ];
        
        // Insert each product
        foreach ($sampleProducts as $product) {
            // Insert main product
            $insert_sql = "INSERT INTO products (name, description, category, price, high_price, stock, image, gender, discount) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sssddissi", 
                $product['name'], 
                $product['description'], 
                $product['category'], 
                $product['price'], 
                $product['high_price'], 
                $product['stock'], 
                $product['image'], 
                $product['gender'], 
                $product['discount']
            );
            
            if ($stmt->execute()) {
                $product_id = $conn->insert_id;
                
                // Insert sizes
                foreach ($product['sizes'] as $size) {
                    $size_sql = "INSERT INTO product_sizes (product_id, size) VALUES (?, ?)";
                    $size_stmt = $conn->prepare($size_sql);
                    $size_stmt->bind_param("is", $product_id, $size);
                    $size_stmt->execute();
                }
                
                // Insert colors
                foreach ($product['colors'] as $color) {
                    $color_sql = "INSERT INTO product_colors (product_id, color) VALUES (?, ?)";
                    $color_stmt = $conn->prepare($color_sql);
                    $color_stmt->bind_param("is", $product_id, $color);
                    $color_stmt->execute();
                }
            }
        }
        
    } catch (Exception $e) {
        echo "Error seeding products: " . $e->getMessage() . "\n";
    }
}
?>
