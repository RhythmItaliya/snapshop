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
        <!-- Simple About Section -->
        <section class="min-h-screen bg-light py-8 px-4">
            <div class="max-w-6xl mx-auto mt-20">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="order-2 lg:order-1">
                        <img
                            src="assets/img/contact.jpg"
                            alt="About us"
                            class="w-full h-auto rounded-3xl shadow-xl object-cover"
                            style="min-height: 420px;"
                        />
                    </div>

                    <div class="order-1 lg:order-2">
                        <h1 class="text-3xl lg:text-4xl font-bold text-primary mb-4">About Us</h1>
                        <p class="text-neutral text-lg leading-8">
                            We are a modern fashion store focused on quality, comfort, and value. Our mission is simple:
                            make great style easy for everyone. From everyday essentials to trend-forward pieces, we
                            curate products you'll love to wear.
                        </p>
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
