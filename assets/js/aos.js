// AOS (Animate On Scroll) Setup
document.addEventListener('DOMContentLoaded', function() {
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 100
    });
    
    // Custom animations
    const animateElements = document.querySelectorAll('[data-aos]');
    animateElements.forEach(element => {
        element.addEventListener('animationend', function() {
            this.classList.add('aos-animated');
        });
    });
});

// Simple fade in function
function fadeIn(element, duration = 500) {
    element.style.opacity = '0';
    element.style.transition = `opacity ${duration}ms ease-in-out`;
    
    setTimeout(() => {
        element.style.opacity = '1';
    }, 100);
}

// Simple slide up function
function slideUp(element, duration = 500) {
    element.style.transform = 'translateY(50px)';
    element.style.opacity = '0';
    element.style.transition = `all ${duration}ms ease-out`;
    
    setTimeout(() => {
        element.style.transform = 'translateY(0)';
        element.style.opacity = '1';
    }, 100);
}
