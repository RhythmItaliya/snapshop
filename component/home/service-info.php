<?php
$services = [
    [
        'icon' => '<i class="fas fa-shipping-fast text-2xl"></i>',
        'title' => 'Free Shipping',
        'description' => 'Free shipping on orders over ₹999',
        'color' => 'text-accent',
        'bgColor' => 'bg-accent/10',
    ],
    [
        'icon' => '<i class="fas fa-undo text-2xl"></i>',
        'title' => 'Easy Returns',
        'description' => '30-day return policy, no questions asked',
        'color' => 'text-success',
        'bgColor' => 'bg-success/10',
    ],
    [
        'icon' => '<i class="fas fa-shield-alt text-2xl"></i>',
        'title' => 'Secure Payment',
        'description' => '100% secure payment with Razorpay',
        'color' => 'text-secondary',
        'bgColor' => 'bg-secondary/10',
    ],
    [
        'icon' => '<i class="fas fa-headset text-2xl"></i>',
        'title' => '24/7 Support',
        'description' => 'Get help anytime via chat or email',
        'color' => 'text-primary',
        'bgColor' => 'bg-primary/10',
    ],
    [
        'icon' => '<i class="fas fa-credit-card text-2xl"></i>',
        'title' => 'Multiple Payment',
        'description' => 'Credit cards, UPI, net banking & more',
        'color' => 'text-accent',
        'bgColor' => 'bg-accent/10',
    ],
];
?>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8" data-aos="fade-up">
            <h3 class="text-xl font-medium text-primary">What Makes Us Special</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            <?php foreach ($services as $index => $service): ?>
                <div
                    class="group p-6 rounded-xl border border-gray-100 hover:border-gray-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1"
                    data-aos="fade-up"
                    data-aos-delay="<?php echo $index * 100; ?>"
                >
                    <div class="w-16 h-16 rounded-full <?php echo $service['bgColor']; ?> flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <div class="<?php echo $service['color']; ?>"><?php echo $service['icon']; ?></div>
                    </div>

                    <h3 class="text-xl font-semibold text-primary mb-2"><?php echo $service['title']; ?></h3>
                    <p class="text-neutral leading-relaxed"><?php echo $service['description']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
