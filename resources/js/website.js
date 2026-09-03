/* =========================================================
   Dreamers Association - Landing Page JS
   ========================================================= */

import { createIcons, icons } from 'lucide';

/* Expose a `lucide` global so inline `lucide.createIcons()` calls
   already used inside Blade partials (e.g. scripts-base) keep working. */
window.lucide = { createIcons: () => createIcons({ icons }) };


/* =========================================================
   Navbar Scroll
   ========================================================= */

function initNavbar() {

    const navbar = document.querySelector('[data-navbar]');

    if (!navbar) {
        return;
    }

    const handleScroll = () => {

        if (window.scrollY > 20) {
            navbar.classList.add('nav-scrolled');
        } else {
            navbar.classList.remove('nav-scrolled');
        }
    };

    window.addEventListener(
        'scroll',
        handleScroll,
        { passive: true }
    );

    handleScroll();
}


/* =========================================================
   Reveal Animation
   ========================================================= */

function initReveal() {

    const elements = document.querySelectorAll('.reveal');

    if (!elements.length) {
        return;
    }

    if (!('IntersectionObserver' in window)) {

        elements.forEach(element => {
            element.classList.add('active');
        });

        return;
    }

    const observer = new IntersectionObserver(
        entries => {

            entries.forEach(entry => {

                if (entry.isIntersecting) {

                    entry.target.classList.add('active');

                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.12
        }
    );

    elements.forEach(element => {
        observer.observe(element);
    });
}


/* =========================================================
   FAQ
   Only one FAQ can remain open in the same group.
   ========================================================= */

function initFaq() {

    const faqItems = document.querySelectorAll('.faq-item');

    if (!faqItems.length) {
        return;
    }

    faqItems.forEach(item => {

        item.addEventListener('toggle', () => {

            if (!item.open) {
                return;
            }

            faqItems.forEach(otherItem => {

                if (
                    otherItem !== item &&
                    otherItem.open
                ) {
                    otherItem.open = false;
                }
            });
        });
    });
}


/* =========================================================
   Mobile Menu
   ========================================================= */

function initMobileMenu() {

    const button = document.querySelector('[data-mobile-menu-button]');
    const menu = document.querySelector('[data-mobile-menu]');

    if (!button || !menu) {
        return;
    }

    button.addEventListener('click', () => {

        const isOpen =
            menu.getAttribute('data-open') === 'true';

        menu.setAttribute(
            'data-open',
            String(!isOpen)
        );

        menu.classList.toggle('hidden', isOpen);

        button.setAttribute(
            'aria-expanded',
            String(!isOpen)
        );
    });


    /* Close menu after clicking a link */

    menu.querySelectorAll('a[href^="#"]').forEach(link => {

        link.addEventListener('click', () => {

            menu.classList.add('hidden');

            menu.setAttribute(
                'data-open',
                'false'
            );

            button.setAttribute(
                'aria-expanded',
                'false'
            );
        });
    });
}


/* =========================================================
   DOM Ready
   ========================================================= */

document.addEventListener('DOMContentLoaded', () => {


    window.lucide.createIcons();

    initNavbar();

    initReveal();

    initFaq();

    initMobileMenu();
});