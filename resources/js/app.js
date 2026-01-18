import './bootstrap';
import './chatbot-api';
import Alpine from 'alpinejs';
import { mobileMenu } from './components/mobileMenu';
import { dropdown } from './components/dropdown';
import { modal } from './components/modal';

// Register Alpine data
Alpine.data('mobileMenu', mobileMenu);
Alpine.data('dropdown', dropdown);
Alpine.data('modal', modal);

// Make Alpine available globally
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

// Lazy load images with Intersection Observer (fallback for older browsers)
document.addEventListener('DOMContentLoaded', () => {
    // Check if native lazy loading is supported
    if ('loading' in HTMLImageElement.prototype) {
        // Native lazy loading is supported, no need for Intersection Observer
        return;
    }

    // Fallback: Use Intersection Observer
    const images = document.querySelectorAll('img[data-src]');
    
    if (images.length === 0) {
        return;
    }

    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                if (img.dataset.srcset) {
                    img.srcset = img.dataset.srcset;
                }
                img.removeAttribute('data-src');
                img.removeAttribute('data-srcset');
                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px', // Start loading 50px before image enters viewport
    });

    images.forEach(img => imageObserver.observe(img));
});
