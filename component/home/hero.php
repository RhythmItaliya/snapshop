<?php
$slides = [
    [
        'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
        'title' => "Men's Collection",
        'subtitle' => 'Sophisticated Style',
        'tag' => "Men's Fashion",
        'link' => '/snapshop/products.php?category=men',
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80',
        'title' => "Women's Collection",
        'subtitle' => 'Elegant & Chic',
        'tag' => "Women's Fashion",
        'link' => '/snapshop/products.php?category=women',
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&auto=format&fit=crop&w=2071&q=80',
        'title' => 'New Arrivals',
        'subtitle' => 'Latest Trends',
        'tag' => 'Hot & New',
        'link' => '/snapshop/products.php?category=sale',
    ],
];
?>

<link rel="stylesheet" type="text/css" href="node_modules/slick-carousel/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="node_modules/slick-carousel/slick/slick-theme.css"/>

<style> 
.hero-carousel {
    width: 100%;
    height: 100%;
}

.hero-slide {
    width: 100%;
    height: 100%;
    position: relative;
}

.hero-slide > div {
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.hero-carousel .slick-list,
.hero-carousel .slick-track {
    height: 100%;
}

.hero-carousel .slick-slide {
    height: 100%;
}

.hero-carousel .slick-slide > div {
    height: 100%;
}

.slick-prev, .slick-next {
    display: none !important;
}

.slick-dots {
    display: none !important;
}

.hero-height-mobile {
    height: 500px;
}

.hero-height-desktop {
    height: 700px;
}

@media (min-width: 768px) {
    .hero-height-mobile {
        height: 600px;
    }
}

@media (min-width: 1024px) {
    .hero-height-desktop {
        height: 800px;
    }
}
</style>

<section class="relative hero-height-mobile hero-height-desktop overflow-hidden">
    <div class="hero-carousel">
        <?php foreach ($slides as $index => $slide): ?>
            <div class="hero-slide relative">
                <div class="w-full h-full bg-cover bg-center" style="background-image: url('<?php echo $slide['image']; ?>')">
                    <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                    
                    <div class="absolute inset-0 z-10 container mx-auto px-4 flex items-center justify-center">
                        <div class="text-center text-white max-w-3xl mx-auto slide-content" data-slide="<?php echo $index; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>">
                            <div class="flex items-center justify-center mb-6" data-aos="fade-down" data-aos-delay="200">
                                <div class="w-12 h-1 bg-secondary mr-4"></div>
                                <span class="text-base uppercase tracking-wide"><?php echo $slide['tag']; ?></span>
                            </div>
                            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6" data-aos="fade-up" data-aos-delay="400">
                                <?php echo $slide['title']; ?>
                                <br />
                                <span class="font-light"><?php echo $slide['subtitle']; ?></span>
                            </h1>
                            <a href="<?php echo $slide['link']; ?>" class="inline-block bg-white text-primary hover:bg-gray-100 px-10 py-5 rounded-lg font-semibold text-lg transition-colors" data-aos="fade-up" data-aos-delay="600">
                                Shop Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <button id="prevSlide" class="absolute left-6 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-3 rounded-full transition-colors group z-20" title="Previous slide">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </button>
    <button id="nextSlide" class="absolute right-6 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white p-3 rounded-full transition-colors group z-20" title="Next slide">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>

    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex space-x-3 z-20">
        <?php foreach ($slides as $index => $slide): ?>
            <button class="slide-dot w-4 h-4 rounded-full transition-colors <?php echo $index === 0 ? 'bg-white' : 'bg-white/50'; ?>" data-slide="<?php echo $index; ?>" title="Go to slide <?php echo $index + 1; ?>"></button>
        <?php endforeach; ?>
    </div>
</section>

<script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="node_modules/slick-carousel/slick/slick.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const slides = <?php echo json_encode($slides); ?>;
    let currentSlide = 0;
    const slideContents = document.querySelectorAll('.slide-content');
    const slideDots = document.querySelectorAll('.slide-dot');
    const prevButton = document.getElementById('prevSlide');
    const nextButton = document.getElementById('nextSlide');

    function showSlide(index) {
        
        slideContents.forEach((content, i) => {
            content.style.display = i === index ? 'block' : 'none';
        });
        
        slideDots.forEach((dot, i) => {
            if (i === index) {
                dot.classList.remove('bg-white/50');
                dot.classList.add('bg-white');
            } else {
                dot.classList.remove('bg-white');
                dot.classList.add('bg-white/50');
            }
        });
        
        const prevIndex = (index - 1 + slides.length) % slides.length;
        const nextIndex = (index + 1) % slides.length;
        prevButton.title = `Previous slide: ${slides[prevIndex].title}`;
        nextButton.title = `Next slide: ${slides[nextIndex].title}`;
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
        $('.hero-carousel').slick('slickGoTo', currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
        $('.hero-carousel').slick('slickGoTo', currentSlide);
    }

    nextButton.addEventListener('click', nextSlide);
    prevButton.addEventListener('click', prevSlide);
    
    slideDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
            $('.hero-carousel').slick('slickGoTo', index);
        });
    });

    try {
        $('.hero-carousel').slick({
            dots: false,
            infinite: true,
            speed: 1000,
            fade: true,
            cssEase: 'linear',
            autoplay: true,
            autoplaySpeed: 5000,
            arrows: false,
            pauseOnHover: false
        });
    } catch (error) {
        console.error('Error initializing slick carousel:', error);
    }

    $('.hero-carousel').on('beforeChange', function(event, slick, currentSlideIndex, nextSlideIndex) {
        currentSlide = nextSlideIndex;
        showSlide(currentSlide);
    });

    setInterval(nextSlide, 5000);
});
</script>
