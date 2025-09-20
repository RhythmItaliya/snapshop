<?php
// About Us Page
session_start();

// Include required files
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/component/ui/toast.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - SnapShop</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="node_modules/aos/dist/aos.css">
    
    <!-- Font Awesome Icons 7.0.0 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
</head>
<body class="bg-light font-spartan text-primary">
    <!-- Header Component -->
    <?php include "component/header.php"; ?>

    <!-- Main Content -->
    <main class="pt-16">
        <!-- About Section -->
        <section class="min-h-screen bg-light py-12 px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                    <!-- Image Section -->
                    <div class="order-2 lg:order-1" data-aos="fade-right" data-aos-delay="200">
                        <div class="flex justify-center lg:justify-start">
                            <img
                                src="assets/img/contact.jpg"
                                alt="About SnapShop - Modern Fashion Store"
                                class="w-64 h-64 lg:w-80 lg:h-80 rounded-2xl shadow-lg object-cover"
                            />
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="order-1 lg:order-2" data-aos="fade-left" data-aos-delay="400">
                        <div class="space-y-6">
                            <h1 class="text-3xl lg:text-4xl font-bold text-primary">About SnapShop</h1>
                            <p class="text-neutral text-lg leading-relaxed">
                                We are a modern fashion store focused on quality, comfort, and value. Our mission is simple:
                                make great style easy for everyone. From everyday essentials to trend-forward pieces, we
                                curate products you'll love to wear.
                            </p>
                            <p class="text-neutral text-lg leading-relaxed">
                                Founded with the vision of making fashion accessible and affordable, SnapShop brings you
                                carefully selected pieces that blend style with functionality. We believe everyone deserves
                                to look and feel their best.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer Component -->
    <?php include "component/footer.php"; ?>

    <!-- AOS JavaScript -->
    <script src="node_modules/aos/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: "ease-in-out",
            once: true,
            offset: 100
        });
    </script>
    
    <!-- Include Toast Notifications -->
    <?php include "component/ui/toast.php"; ?>
</body>
</html>
