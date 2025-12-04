// assets/js/main.js
// Single consolidated DOMContentLoaded handler to avoid duplicates
document.addEventListener('DOMContentLoaded', function() {
    // --- SIDEBAR TOGGLE -----------------------
    // Toggle class on body and persist user preference
    function applySidebarState(isCollapsed) {
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
        } else {
            document.body.classList.remove('sidebar-collapsed');
        }
        // Updated aria-expanded on toggle buttons
        document.querySelectorAll('.sidebar-toggle').forEach(btn => {
            btn.setAttribute('aria-expanded', isCollapsed ? 'true' : 'false');
        });
    }

    // Initialize from localStorage
    const savedState = localStorage.getItem('sidebarState');
    if (savedState === 'collapsed') {
        applySidebarState(true);
    } else {
        applySidebarState(false);
    }

    // Attach click handlers to all sidebar-toggle buttons (works with inline onclick too)
    document.querySelectorAll('.sidebar-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
            // update aria-expanded
            button.setAttribute('aria-expanded', isCollapsed ? 'true' : 'false');
        });
    });

    // Allow ESC to close sidebar on mobile when overlay is present
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (document.body.classList.contains('sidebar-collapsed')) {
                // On mobile we use collapsed to mean "open" for sidebar; keep behavior consistent:
                // if overlay present (mobile), remove collapsed to hide.
                document.body.classList.remove('sidebar-collapsed');
            }
            // Close mobile menu if open
            document.querySelectorAll('#mobileMenu.active').forEach(menu => menu.classList.remove('active'));
            // update stored state (we treat ESC as "expanded" on desktop)
            localStorage.setItem('sidebarState', 'expanded');
            // update aria-expanded on toggle buttons
            document.querySelectorAll('.sidebar-toggle').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
        }
    });

    // --- MOBILE MENU (header) -----------------
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            const expanded = mobileMenu.classList.contains('active');
            mobileMenuBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });

        // Close mobile menu when clicking outside (simple)
        document.addEventListener('click', function(ev) {
            if (!mobileMenu.contains(ev.target) && !mobileMenuBtn.contains(ev.target)) {
                if (mobileMenu.classList.contains('active')) {
                    mobileMenu.classList.remove('active');
                    mobileMenuBtn.setAttribute('aria-expanded', 'false');
                }
            }
        });
    }

    // --- ANTI DOUBLE SUBMIT & LOADING STATE ----
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // If form invalid HTML5, do nothing
            if (!this.checkValidity()) return;

            // Find submit button
            const btn = this.querySelector('button[type="submit"], input[type="submit"]');
            if (btn) {
                // compute original text and set loading indicator
                const loadingText = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

                if (btn.tagName === 'INPUT') {
                    btn.value = 'Memproses...';
                } else {
                    // preserve width to avoid layout jump
                    btn.style.width = getComputedStyle(btn).width;
                    btn.dataset.originalHtml = btn.innerHTML;
                    btn.innerHTML = loadingText;
                }

                btn.style.opacity = '0.7';
                btn.style.cursor = 'wait';

                // disable after a short delay to allow submit event propagation
                setTimeout(() => {
                    btn.disabled = true;
                }, 50);
            }
        });
    });
});