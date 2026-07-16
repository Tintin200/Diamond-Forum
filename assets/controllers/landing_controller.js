import { Controller } from '@hotwired/stimulus';

/*
 * Contrôleur Stimulus pour la landing page Diamond Forum.
 * Remplace les comportements gérés par framer-motion / lenis dans la
 * maquette v0.app d'origine :
 *  - fond de la nav qui apparaît au scroll
 *  - menu mobile
 *  - scroll fluide vers les ancres (#hero, #scores, ...)
 *  - apparition progressive des blocs au scroll (data-reveal)
 *
 * Utilisation dans Twig :
 *   <div data-controller="landing">
 */
export default class extends Controller {
    static targets = ['nav', 'mobileMenu'];

    connect() {
        this.onScroll = this.onScroll.bind(this);
        window.addEventListener('scroll', this.onScroll, { passive: true });
        this.onScroll();

        this.setupSmoothScroll();
        this.setupReveal();
    }

    disconnect() {
        window.removeEventListener('scroll', this.onScroll);
        if (this.observer) {
            this.observer.disconnect();
        }
    }

    onScroll() {
        if (!this.hasNavTarget) return;
        if (window.scrollY > 50) {
            this.navTarget.classList.add('is-scrolled');
        } else {
            this.navTarget.classList.remove('is-scrolled');
        }
    }

    toggleMobileMenu() {
        if (!this.hasMobileMenuTarget) return;
        this.mobileMenuTarget.classList.toggle('is-open');
    }

    closeMobileMenu() {
        if (!this.hasMobileMenuTarget) return;
        this.mobileMenuTarget.classList.remove('is-open');
    }

    // Smooth scroll vers les sections ancrées (#hero, #scores, #classement...)
    setupSmoothScroll() {
        this.element.querySelectorAll('a[href^="#"]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const targetId = link.getAttribute('href');
                const target = document.querySelector(targetId);
                if (!target) return;
                event.preventDefault();
                const offset = 90;
                const top = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top, behavior: 'smooth' });
                this.closeMobileMenu();
            });
        });
    }

    // Ajoute la classe is-visible aux éléments [data-reveal] quand ils entrent dans le viewport
    setupReveal() {
        const items = this.element.querySelectorAll('[data-reveal]');
        if (!items.length) return;

        this.observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        this.observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.15, rootMargin: '-40px' }
        );

        items.forEach((item) => this.observer.observe(item));
    }
}
