<?php
// FeaturedCollection Component
// This component displays featured product collections with hover effects

$products = [
    [
        'id' => 1,
        'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
        'createdAt' => '2024-01-15'
    ],
    [
        'id' => 2,
        'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&auto=format&fit=crop&w=2071&q=80',
        'createdAt' => '2024-01-20'
    ],
    [
        'id' => 3,
        'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
        'createdAt' => '2024-01-25'
    ]
];

function getCollections($products) {
    if (empty($products)) return [];

    $collections = [];

    $bestProducts = array_slice($products, 0, 6);
    if (!empty($bestProducts)) {
        $collections[] = [
            'id' => 'best',
            'image' => $bestProducts[0]['image'] ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
            'tag' => 'BEST SELLERS',
            'title' => 'BEST COLLECTION',
            'subtitle' => 'TOP RATED PRODUCTS',
            'accentColor' => 'bg-secondary',
            'link' => '/products',
            'productCount' => count($bestProducts),
        ];
    }

    $newProducts = array_slice($products, 0, 4);
    if (!empty($newProducts)) {
        $collections[] = [
            'id' => 'new',
            'image' => $newProducts[0]['image'] ?? 'https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&auto=format&fit=crop&w=2071&q=80',
            'tag' => 'NEW ARRIVALS',
            'title' => 'NEW COLLECTION',
            'subtitle' => 'FRESH & TRENDING',
            'accentColor' => 'bg-accent',
            'link' => '/products?category=new',
            'productCount' => count($newProducts),
        ];
    }

    $latestProducts = array_slice($products, 0, 6);
    if (!empty($latestProducts)) {
        $collections[] = [
            'id' => 'latest',
            'image' => $latestProducts[0]['image'] ?? 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
            'tag' => 'LATEST TRENDS',
            'title' => 'LATEST COLLECTION',
            'subtitle' => 'HOT & POPULAR',
            'accentColor' => 'bg-success',
            'link' => '/products?category=latest',
            'productCount' => count($latestProducts),
        ];
    }

    return $collections;
}

$collections = getCollections($products);
$loading = false;
$error = null;
?>

<section class="py-16 bg-light">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl md:text-4xl font-bold text-primary mb-4">Featured Collections</h2>
            <p class="text-neutral text-lg max-w-2xl mx-auto">
                Discover our latest curated collections designed for every style and occasion
            </p>
        </div>

        <?php if ($loading): ?>
            <div class="text-center py-12">
                <div class="text-neutral text-6xl mb-4">⏳</div>
                <p class="text-neutral text-lg">Loading collections...</p>
            </div>
        <?php elseif ($error): ?>
            <div class="text-center py-12">
                <div class="text-neutral text-6xl mb-4">❌</div>
                <h3 class="text-xl font-semibold text-primary mb-2">Error Loading Collections</h3>
                <p class="text-neutral mb-4"><?php echo htmlspecialchars($error); ?></p>
                <button onclick="window.location.reload()" class="bg-accent text-white px-6 py-3 rounded-lg font-semibold hover:bg-accent/80 transition-colors">
                    Try Again
                </button>
            </div>
        <?php elseif (!empty($collections)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($collections as $index => $collection): ?>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2"
                        data-aos="fade-up"
                        data-aos-delay="<?php echo $index * 200; ?>"
                    >
                        <div
                            class="relative h-80 bg-cover bg-center bg-no-repeat"
                            style="background-image: url('<?php echo htmlspecialchars($collection['image']); ?>')"
                        >
                            <div class="absolute inset-0 bg-black bg-opacity-40 group-hover:bg-opacity-30 transition-all duration-300"></div>

                            <div class="absolute inset-0 p-6 flex flex-col justify-center items-center text-center">
                                <div class="flex items-center mb-4">
                                    <div class="w-8 h-0.5 <?php echo $collection['accentColor']; ?> mr-3"></div>
                                    <span class="text-white text-xs font-semibold tracking-wider uppercase">
                                        <?php echo htmlspecialchars($collection['tag']); ?>
                                    </span>
                                </div>

                                <div class="mb-4">
                                    <h3 class="text-white text-2xl md:text-3xl font-bold uppercase mb-2 leading-tight">
                                        <?php echo htmlspecialchars($collection['title']); ?>
                                    </h3>
                                    <p class="text-white text-lg md:text-xl font-light uppercase">
                                        <?php echo htmlspecialchars($collection['subtitle']); ?>
                                    </p>
                                </div>

                                <div class="mb-4">
                                    <span class="text-white text-sm opacity-80">
                                        <?php echo $collection['productCount']; ?> Products Available
                                    </span>
                                </div>

                                <div>
                                    <a
                                        href="<?php echo htmlspecialchars($collection['link']); ?>"
                                        class="inline-block text-white font-semibold uppercase text-sm tracking-wider relative group/btn"
                                    >
                                        Discover More
                                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-white group-hover/btn:w-full transition-all duration-300"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="text-neutral text-6xl mb-4">📦</div>
                <h3 class="text-xl font-semibold text-primary mb-2">No Collections Available</h3>
                <p class="text-neutral mb-4">
                    <?php echo empty($products) 
                        ? "We're currently setting up our collections. Check back soon!"
                        : 'No collections found at the moment.'; ?>
                </p>
                <?php if (empty($products)): ?>
                    <a href="/snapshop/products" class="inline-block bg-accent text-white px-6 py-3 rounded-lg font-semibold hover:bg-accent/80 transition-colors">
                        Browse All Products
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
