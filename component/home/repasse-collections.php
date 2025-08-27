<?php
// RepasseCollections Component - Converted from React
// This component displays the Repasse men's and women's collections

$collections = [
    [
        'id' => 1,
        'type' => 'men',
        'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        'title' => 'REPASSE MEN',
        'subtitle' => 'Sophisticated Collection',
        'link' => '/snapshop/products.php?category=men&brand=repasse',
    ],
    [
        'id' => 2,
        'type' => 'women',
        'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        'title' => 'REPASSE WOMEN',
        'subtitle' => 'Elegant Collection',
        'link' => '/snapshop/products.php?category=women&brand=repasse',
    ],
];
?>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Repasse Collections</h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Discover our exclusive men's and women's collections
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-5xl mx-auto">
            <?php foreach ($collections as $index => $collection): ?>
                <div class="group" data-aos="fade-up" data-aos-delay="<?php echo $index * 200; ?>">
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                        <div class="relative overflow-hidden">
                            <img
                                src="<?php echo htmlspecialchars($collection['image']); ?>"
                                alt="<?php echo htmlspecialchars($collection['title']); ?>"
                                class="w-full h-80 object-cover group-hover:scale-105 transition-transform duration-500"
                            />
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300"></div>
                        </div>

                        <div class="p-8 text-center">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($collection['title']); ?></h3>
                            <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($collection['subtitle']); ?></p>

                            <a href="<?php echo htmlspecialchars($collection['link']); ?>" 
                               class="inline-block px-8 py-3 border-2 border-blue-600 text-blue-600 font-semibold rounded-lg hover:bg-blue-600 hover:text-white transition-all duration-300">
                                View Collection
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
