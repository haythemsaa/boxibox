/**
 * BOXIBOX - Enhanced JavaScript
 * Version: 2.2.0
 * Performance, Security & UX Optimized
 */

(function() {
    'use strict';

    // ============================================
    // PERFORMANCE OPTIMIZATIONS
    // ============================================

    /**
     * Debounce function for performance optimization
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Throttle function for scroll/resize events
     */
    function throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Lazy load images
     */
    function initLazyLoading() {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    // ============================================
    // MOBILE SIDEBAR TOGGLE
    // ============================================

    function initMobileSidebar() {
        // Create mobile toggle button if it doesn't exist
        if (!document.querySelector('.mobile-sidebar-toggle')) {
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'btn btn-primary mobile-sidebar-toggle d-md-none';
            toggleBtn.style.cssText = 'position: fixed; top: 10px; left: 10px; z-index: 1060;';
            toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
            document.body.appendChild(toggleBtn);

            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            backdrop.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1029; display: none;';
            document.body.appendChild(backdrop);

            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                backdrop.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
            });

            backdrop.addEventListener('click', () => {
                sidebar.classList.remove('show');
                backdrop.style.display = 'none';
            });
        }
    }

    // ============================================
    // FORM VALIDATION ENHANCEMENTS
    // ============================================

    function initFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }

    // ============================================
    // CSRF TOKEN FOR AJAX
    // ============================================

    function setupCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }
    }

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================

    window.showToast = function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px; animation: slideInRight 0.3s ease;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    };

    // ============================================
    // LOADING OVERLAY
    // ============================================

    window.showLoading = function(message = 'Chargement...') {
        if (document.querySelector('.loading-overlay')) return;

        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        `;
        overlay.innerHTML = `
            <div class="spinner"></div>
            <p class="text-white mt-3">${message}</p>
        `;
        document.body.appendChild(overlay);
    };

    window.hideLoading = function() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) overlay.remove();
    };

    // ============================================
    // AUTO-SAVE FORMS
    // ============================================

    function initAutoSave() {
        const autoSaveForms = document.querySelectorAll('[data-autosave]');
        autoSaveForms.forEach(form => {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('change', debounce(() => {
                    const formData = new FormData(form);
                    const key = `autosave_${form.id || 'form'}`;
                    localStorage.setItem(key, JSON.stringify(Object.fromEntries(formData)));
                    showToast('Sauvegarde automatique effectuée', 'success');
                }, 1000));
            });

            // Restore on load
            const key = `autosave_${form.id || 'form'}`;
            const saved = localStorage.getItem(key);
            if (saved) {
                const data = JSON.parse(saved);
                Object.entries(data).forEach(([name, value]) => {
                    const input = form.querySelector(`[name="${name}"]`);
                    if (input) input.value = value;
                });
            }
        });
    }

    // ============================================
    // CONFIRM DELETE
    // ============================================

    function initConfirmDelete() {
        document.addEventListener('click', e => {
            const deleteBtn = e.target.closest('[data-confirm-delete]');
            if (deleteBtn) {
                e.preventDefault();
                const message = deleteBtn.dataset.confirmDelete || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
                if (confirm(message)) {
                    if (deleteBtn.tagName === 'FORM') {
                        deleteBtn.submit();
                    } else if (deleteBtn.form) {
                        deleteBtn.form.submit();
                    } else {
                        window.location.href = deleteBtn.href;
                    }
                }
            }
        });
    }

    // ============================================
    // SMOOTH SCROLL TO TOP
    // ============================================

    function initScrollToTop() {
        const scrollBtn = document.createElement('button');
        scrollBtn.className = 'btn btn-primary scroll-to-top';
        scrollBtn.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            padding: 0;
        `;
        scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        document.body.appendChild(scrollBtn);

        window.addEventListener('scroll', throttle(() => {
            scrollBtn.style.display = window.scrollY > 300 ? 'block' : 'none';
        }, 200));

        scrollBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ============================================
    // TABLE SEARCH FILTER
    // ============================================

    window.filterTable = function(inputId, tableId) {
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        if (!input || !table) return;

        input.addEventListener('keyup', debounce(function() {
            const filter = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }, 300));
    };

    // ============================================
    // SECURITY: PREVENT XSS
    // ============================================

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    window.escapeHtml = escapeHtml;

    // ============================================
    // INITIALIZE ON DOM READY
    // ============================================

    document.addEventListener('DOMContentLoaded', function() {
        initLazyLoading();
        initMobileSidebar();
        initFormValidation();
        initAutoSave();
        initConfirmDelete();
        initScrollToTop();
        setupCSRFToken();

        // Add fade-in animation to main content
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.classList.add('fade-in');
        }

        console.log('✅ Boxibox Enhanced JS loaded successfully');
    });

    // ============================================
    // SERVICE WORKER FOR PWA (Future)
    // ============================================

    if ('serviceWorker' in navigator) {
        // Uncomment when ready for PWA
        // navigator.serviceWorker.register('/service-worker.js');
    }

})();
