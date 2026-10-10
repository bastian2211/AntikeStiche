/**
 * Antiquariat Webshop – Frontend JavaScript
 * Setzt Alpine.js-Komponenten und globale Hilfsfunktionen
 */

// ============================================================
// Alpine.js Komponenten
// ============================================================

document.addEventListener('alpine:init', () => {

    /**
     * Bildgalerie mit Lightbox für Artikel-Detailseite
     */
    Alpine.data('imageGallery', () => ({
        activeIndex: 0,
        lightboxOpen: false,

        get activeImage() {
            return this.images[this.activeIndex] || null;
        },

        init() {
            // Bilder aus data-images Attribut laden (gesetzt vom Blade-Template)
            const el = this.$el.closest('[data-images]');
            this.images = el ? JSON.parse(el.dataset.images || '[]') : [];
        },

        setActive(index) {
            this.activeIndex = index;
        },

        next() {
            this.activeIndex = (this.activeIndex + 1) % this.images.length;
        },

        prev() {
            this.activeIndex = (this.activeIndex - 1 + this.images.length) % this.images.length;
        },

        openLightbox(index) {
            this.activeIndex = index;
            this.lightboxOpen = true;
            document.body.style.overflow = 'hidden';
        },

        closeLightbox() {
            this.lightboxOpen = false;
            document.body.style.overflow = '';
        },
    }));

    /**
     * Mobiles Menü
     */
    Alpine.data('mobileMenu', () => ({
        open: false,
        toggle() { this.open = !this.open; },
        close() { this.open = false; },
    }));

    /**
     * Filter-Sidebar (Artikel-Liste)
     */
    Alpine.data('filterSidebar', () => ({
        open: window.innerWidth >= 1024, // Desktop: offen, Mobile: geschlossen

        toggle() { this.open = !this.open; },
    }));

});

// ============================================================
// Keyboard-Navigation für Lightbox
// ============================================================

document.addEventListener('keydown', (e) => {
    // Escape schließt Lightbox
    if (e.key === 'Escape') {
        const lightboxClose = document.querySelector('[data-lightbox-close]');
        if (lightboxClose) lightboxClose.click();
    }
});

// ============================================================
// Flash-Nachrichten ausblenden
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    const flashes = document.querySelectorAll('[data-flash]');
    flashes.forEach(flash => {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s ease';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 4000);
    });
});

// ============================================================
// Admin: Bild-Upload Drag & Drop (erweitert handleFileUpload)
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.querySelector('.upload-drop-zone');
    if (!dropZone) return;

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-amber-400', 'bg-amber-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-amber-400', 'bg-amber-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-amber-400', 'bg-amber-50');

        const input = dropZone.querySelector('input[type="file"]');
        if (input && e.dataTransfer.files.length > 0) {
            // DataTransfer in Input simulieren
            const dt = new DataTransfer();
            for (const file of e.dataTransfer.files) dt.items.add(file);
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
});
