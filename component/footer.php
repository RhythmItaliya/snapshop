<?php
// Footer Component - UI only, no functionality
?>
<footer class="bg-primary text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0 bg-gradient-to-br from-secondary/20 to-accent/20"></div>
    </div>

    <div class="relative z-10">
        <div class="container mx-auto px-4 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Brand Section -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-accent font-bold text-xl">S</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">SnapShop</h2>
                            <p class="text-secondary text-sm font-medium">Premium Fashion</p>
                        </div>
                    </div>

                    <p class="text-gray-300 text-base leading-relaxed max-w-md">
                        Discover exceptional style and quality clothing for men and women. Elevate your wardrobe
                        with our curated collections.
                    </p>
                </div>

                <!-- Quick Links Section -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-white border-b-2 border-secondary pb-2 inline-block">
                        Quick Links
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <a href="/" class="text-gray-300 hover:text-secondary transition-all duration-300 text-base flex items-center group">
                                <span class="w-1 h-1 bg-secondary rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-all duration-300"></span>
                                Home
                            </a>
                        </div>
                        <div class="space-y-3">
                            <a href="/snapshop/products?category=men" class="text-gray-300 hover:text-secondary transition-all duration-300 text-base flex items-center group">
                                <span class="w-1 h-1 bg-secondary rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-all duration-300"></span>
                                Men
                            </a>
                        </div>
                        <div class="space-y-3">
                            <a href="/snapshop/products?category=new" class="text-gray-300 hover:text-secondary transition-all duration-300 text-base flex items-center group">
                                <span class="w-1 h-1 bg-secondary rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-all duration-300"></span>
                                New
                            </a>
                        </div>
                        <div class="space-y-3">
                            <a href="/snapshop/products?category=women" class="text-gray-300 hover:text-secondary transition-all duration-300 text-base flex items-center group">
                                <span class="w-1 h-1 bg-secondary rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-all duration-300"></span>
                                Women
                            </a>
                        </div>
                        <div class="space-y-3">
                            <a href="/snapshop/products?category=sale" class="text-gray-300 hover:text-secondary transition-all duration-300 text-base flex items-center group">
                                <span class="w-1 h-1 bg-secondary rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-all duration-300"></span>
                                Sale
                            </a>
                        </div>
                        <div class="space-y-3">
                            <a href="/contact" class="text-gray-300 hover:text-secondary transition-all duration-300 text-base flex items-center group">
                                <span class="w-1 h-1 bg-secondary rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-all duration-300"></span>
                                Contact Us
                            </a>
                        </div>
                        <div class="space-y-3">
                            <a href="/about" class="text-gray-300 hover:text-secondary transition-all duration-300 text-base flex items-center group">
                                <span class="w-1 h-1 bg-secondary rounded-full mr-3 opacity-0 group-hover:opacity-100 transition-all duration-300"></span>
                                About Us
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact & Social Section -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-white border-b-2 border-secondary pb-2 inline-block">
                        Contact Us
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center space-x-3 text-gray-300 hover:text-secondary transition-colors">
                            <svg class="text-secondary text-lg w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-base">info@snapshop.com</span>
                        </div>

                        <div class="flex items-center space-x-3 text-gray-300 hover:text-secondary transition-colors">
                            <svg class="text-secondary text-lg w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span class="text-base">+1 (555) 123-4567</span>
                        </div>

                        <div class="flex items-center space-x-3 text-gray-300 hover:text-secondary transition-colors">
                            <svg class="text-secondary text-lg w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-base">123 Fashion St, Style City</span>
                        </div>
                    </div>

                    <div class="pt-4">
                        <h4 class="font-semibold text-white mb-3">Follow Us</h4>
                        <div class="flex space-x-4">
                            <!-- Facebook -->
                            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-blue-400 transition-all duration-300 p-2 rounded-lg hover:bg-white/10">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            
                            <!-- Twitter -->
                            <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-blue-300 transition-all duration-300 p-2 rounded-lg hover:bg-white/10">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                            
                            <!-- Instagram -->
                            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-pink-400 transition-all duration-300 p-2 rounded-lg hover:bg-white/10">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987 6.62 0 11.987-5.367 11.987-11.987C24.014 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.708 13.744 3.708 12.447s.49-2.448 1.418-3.323c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.928.875 1.418 2.026 1.418 3.323s-.49 2.448-1.418 3.323c-.875.807-2.026 1.297-3.323 1.297zm7.718-1.297c-.49.49-1.08.807-1.77.807s-1.28-.317-1.77-.807c-.49-.49-.807-1.08-.807-1.77s.317-1.28.807-1.77c.49-.49 1.08-.807 1.77-.807s1.28.317 1.77.807c.49.49.807 1.08.807 1.77s-.317 1.28-.807 1.77z"/>
                                </svg>
                            </a>
                            
                            <!-- LinkedIn -->
                            <a href="https://linkedin.com" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-blue-500 transition-all duration-300 p-2 rounded-lg hover:bg-white/10">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Copyright Section -->
        <div class="border-t border-white/10 bg-primary/80 backdrop-blur-sm">
            <div class="container mx-auto px-4 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0">
                    <div class="flex items-center space-x-2">
                        <p class="text-gray-400 text-sm">2025 SnapShop. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
