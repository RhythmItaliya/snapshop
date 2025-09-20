<?php
// Contact Us Page
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
    <title>Contact Us - SnapShop</title>
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
        <!-- Contact Section with Form -->
        <section class="min-h-screen bg-light py-8 px-4">
            <div class="max-w-6xl mx-auto">
                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center mt-20">
                    <div class="order-2 lg:order-1" data-aos="fade-right" data-aos-delay="200">
                        <div class="relative">
                            <img
                                src="assets/img/contact.jpg"
                                alt="Contact Us"
                                class="w-full h-auto rounded-3xl shadow-xl object-cover"
                                style="min-height: 500px;"
                            />
                        </div>
                    </div>

                    <!-- Right Side - Contact Form -->
                    <div class="order-1 lg:order-2" data-aos="fade-left" data-aos-delay="400">
                        <div class="bg-white rounded-3xl shadow-xl p-8 lg:p-12">
                            <div class="mb-8">
                                <h3 class="text-2xl lg:text-3xl font-bold text-primary mb-3">Send us a Message</h3>
                                <p class="text-neutral">
                                    Fill out the form below and we'll get back to you within 24 hours.
                                </p>
                            </div>

                            <form id="contactForm" class="space-y-6">
                                <?php 
                                // Include UI components
                                require_once __DIR__ . '/component/ui/input.php';
                                require_once __DIR__ . '/component/ui/button.php';
                                ?>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <?php echo renderInput([
                                            'label' => 'Full Name',
                                            'type' => 'text',
                                            'name' => 'name',
                                            'id' => 'name',
                                            'required' => true,
                                            'placeholder' => 'Enter your full name',
                                            'icon' => '<i class="fas fa-user"></i>',
                                            'className' => 'rounded-2xl'
                                        ]); ?>
                                    </div>

                                    <div>
                                        <?php echo renderInput([
                                            'label' => 'Email Address',
                                            'type' => 'email',
                                            'name' => 'email',
                                            'id' => 'email',
                                            'required' => true,
                                            'placeholder' => 'Enter your email',
                                            'icon' => '<i class="fas fa-envelope"></i>',
                                            'className' => 'rounded-2xl'
                                        ]); ?>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-primary mb-3">
                                        Message <span class="text-danger ml-1">*</span>
                                    </label>
                                    <textarea
                                        name="message"
                                        id="message"
                                        required
                                        placeholder="Tell us how we can help you..."
                                        class="w-full px-6 py-4 border-2 border-gray-200 rounded-2xl transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-primary/20 focus:border-primary hover:border-gray-300 resize-none text-base"
                                        rows="5"
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    id="submitBtn"
                                    class="w-full py-4 text-lg font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 bg-primary text-white hover:bg-primary/90 flex items-center justify-center"
                                >
                                    <span id="btnText">Send Message</span>
                                    <i class="fas fa-spinner fa-spin ml-2 hidden" id="loadingIcon"></i>
                                </button>

                                <p class="text-sm text-neutral text-center">
                                    By submitting this form, you agree to our
                                    <a href="#" class="text-primary hover:underline font-medium">Privacy Policy</a>
                                </p>
                            </form>
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
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Contact form handling
        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const loadingIcon = document.getElementById('loadingIcon');
            
            // Get form data
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Form validation
            if (!data.name.trim()) {
                showToast('Name is required', 'error');
                return;
            }

            if (!data.email.trim()) {
                showToast('Email is required', 'error');
                return;
            }

            if (!data.message.trim()) {
                showToast('Message is required', 'error');
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(data.email)) {
                showToast('Please enter a valid email address', 'error');
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            btnText.textContent = 'Sending Message...';
            loadingIcon.classList.remove('hidden');

            try {
                // Send form data to server
                const response = await fetch('api/contact-submit.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message || 'Message sent successfully!', 'success');
                    this.reset();
                } else {
                    throw new Error(result.message || 'Failed to send message');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                const errorMessage = error.message || 'Failed to send message. Please try again.';
                showToast(errorMessage, 'error');
            } finally {
                // Always reset button state
                submitBtn.disabled = false;
                btnText.textContent = 'Send Message';
                loadingIcon.classList.add('hidden');
            }
        });

        // Reset loading state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const loadingIcon = document.getElementById('loadingIcon');
            
            // Ensure button is in normal state
            submitBtn.disabled = false;
            btnText.textContent = 'Send Message';
            loadingIcon.classList.add('hidden');
            loadingIcon.style.display = 'none';
        });

        // Additional safety check - reset state when form is reset
        document.getElementById('contactForm').addEventListener('reset', function() {
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const loadingIcon = document.getElementById('loadingIcon');
            
            submitBtn.disabled = false;
            btnText.textContent = 'Send Message';
            loadingIcon.classList.add('hidden');
            loadingIcon.style.display = 'none';
        });

        // Toast notification function
        function showToast(message, type = 'info') {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `fixed top-20 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
            
            // Set colors based on type
            if (type === 'success') {
                toast.classList.add('bg-success', 'text-white');
            } else if (type === 'error') {
                toast.classList.add('bg-danger', 'text-white');
            } else {
                toast.classList.add('bg-accent', 'text-white');
            }
            
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-3"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            // Remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 5000);
        }
    </script>
    
    <!-- Include Toast Notifications -->
    <?php include "component/ui/toast.php"; ?>
</body>
</html>
