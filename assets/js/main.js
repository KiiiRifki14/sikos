// assets/js/main.js
// Single consolidated DOMContentLoaded handler to avoid duplicates
document.addEventListener('DOMContentLoaded', function() {
    // --- SIDEBAR TOGGLE -----------------------
    function applySidebarState(isCollapsed) {
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
        } else {
            document.body.classList.remove('sidebar-collapsed');
        }
        document.querySelectorAll('.sidebar-toggle').forEach(btn => {
            btn.setAttribute('aria-expanded', isCollapsed ? 'true' : 'false');
        });
    }

    const savedState = localStorage.getItem('sidebarState');
    if (savedState === 'collapsed') {
        applySidebarState(true);
    } else {
        applySidebarState(false);
    }

    document.querySelectorAll('.sidebar-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
            button.setAttribute('aria-expanded', isCollapsed ? 'true' : 'false');
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (document.body.classList.contains('sidebar-collapsed')) {
                document.body.classList.remove('sidebar-collapsed');
            }
            document.querySelectorAll('#mobileMenu.active').forEach(menu => menu.classList.remove('active'));
            localStorage.setItem('sidebarState', 'expanded');
            document.querySelectorAll('.sidebar-toggle').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
        }
    });

    // --- MOBILE MENU (header) -----------------
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function(event) {
            event.stopPropagation();
            mobileMenu.classList.toggle('active');
            const expanded = mobileMenu.classList.contains('active');
            mobileMenuBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(ev) {
            if (!mobileMenu.contains(ev.target) && !mobileMenuBtn.contains(ev.target)) {
                if (mobileMenu.classList.contains('active')) {
                    mobileMenu.classList.remove('active');
                    mobileMenuBtn.setAttribute('aria-expanded', 'false');
                }
            }
        });
    }

    // --- SMOOTH SCROLL (Anchor links) ----------
    // Handles same-page anchors (including nav links like #kamar, #fasilitas)
    (function setupSmoothScroll() {
        // header offset to avoid content hidden behind fixed navbar
        const header = document.querySelector('.navbar');
        const headerOffset = header ? header.offsetHeight + 12 : 92; // +12 for breathing space

        // select links that point to hashes on the same page
        const anchorLinks = Array.from(document.querySelectorAll('a[href*="#"]'))
            .filter(a => {
                const href = a.getAttribute('href');
                // Accept pure hash (#id) or page anchors linking current page (index.php#kamar)
                return href && href.includes('#') && (href.charAt(0) === '#' || href.indexOf(location.pathname) !== -1);
            });

        anchorLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // If the href points to an element ID on this page, intercept and smooth-scroll
                const href = this.getAttribute('href');
                const hash = href.split('#')[1];
                if (!hash) return; // no target

                const targetEl = document.getElementById(hash);
                if (targetEl) {
                    e.preventDefault();

                    // compute target top with offset
                    const targetTop = targetEl.getBoundingClientRect().top + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: Math.max(0, targetTop),
                        behavior: 'smooth'
                    });

                    // Accessibility: focus target without scrolling (preventScroll)
                    try {
                        // add temporary tabindex if element not focusable
                        const hadTabindex = targetEl.hasAttribute('tabindex');
                        if (!hadTabindex) targetEl.setAttribute('tabindex', '-1');
                        targetEl.focus({ preventScroll: true });
                        if (!hadTabindex) targetEl.removeAttribute('tabindex');
                    } catch (err) {
                        // ignore focus errors on old browsers
                    }

                    // Close mobile menu if open (mobile-friendly)
                    if (mobileMenu && mobileMenu.classList.contains('active')) {
                        mobileMenu.classList.remove('active');
                        if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'false');
                    }

                    // NOTE: intentionally do NOT modify history/hash here,
                    // so the URL in address bar remains unchanged.
                }
            });
        });
    })();

    // --- ANTI DOUBLE SUBMIT & LOADING STATE ----
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) return;

            const btn = this.querySelector('button[type="submit"], input[type="submit"]');
            if (btn) {
                const loadingText = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

                if (btn.tagName === 'INPUT') {
                    btn.value = 'Memproses...';
                } else {
                    btn.style.width = getComputedStyle(btn).width;
                    btn.dataset.originalHtml = btn.innerHTML;
                    btn.innerHTML = loadingText;
                }

                btn.style.opacity = '0.7';
                btn.style.cursor = 'wait';

                setTimeout(() => {
                    btn.disabled = true;
                }, 50);
            }
        });
    });
});