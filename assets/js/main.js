// assets/js/main.js
// Single consolidated DOMContentLoaded handler
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

    // --- SUBMENU (Sidebar Sections) -----------
    (function setupSidebarSubmenus() {
        // Attach to all toggles
        const toggles = document.querySelectorAll('.sidebar-submenu-toggle');
        toggles.forEach(toggle => {
            const controls = toggle.getAttribute('aria-controls');
            const submenu = controls ? document.getElementById(controls) : toggle.nextElementSibling;
            const key = controls ? ('submenu:' + controls) : null;

            // Initialize state from aria or localStorage
            let open = toggle.getAttribute('aria-expanded') === 'true';
            if (key) {
                const stored = localStorage.getItem(key);
                if (stored !== null) open = stored === 'true';
            }
            if (open) {
                toggle.classList.add('active');
                toggle.setAttribute('aria-expanded', 'true');
                if (submenu) submenu.classList.add('open');
                // mark parent container for CSS rule
                if (toggle.parentElement) toggle.parentElement.classList.add('open');
            } else {
                toggle.setAttribute('aria-expanded', 'false');
                if (submenu) submenu.classList.remove('open');
                if (toggle.parentElement) toggle.parentElement.classList.remove('open');
            }

            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const nowOpen = toggle.getAttribute('aria-expanded') !== 'true';
                toggle.setAttribute('aria-expanded', nowOpen ? 'true' : 'false');
                if (nowOpen) {
                    toggle.classList.add('active');
                    if (submenu) submenu.classList.add('open');
                    if (toggle.parentElement) toggle.parentElement.classList.add('open');
                } else {
                    toggle.classList.remove('active');
                    if (submenu) submenu.classList.remove('open');
                    if (toggle.parentElement) toggle.parentElement.classList.remove('open');
                }
                // persist
                if (key) localStorage.setItem(key, nowOpen ? 'true' : 'false');
            });
        });
    })();

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
    (function setupSmoothScroll() {
        const header = document.querySelector('.navbar');
        const headerOffset = header ? header.offsetHeight + 12 : 92;

        const anchorLinks = Array.from(document.querySelectorAll('a[href*="#"]'))
            .filter(a => {
                const href = a.getAttribute('href');
                return href && href.includes('#') && (href.charAt(0) === '#' || href.indexOf(location.pathname) !== -1);
            });

        anchorLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                const hash = href.split('#')[1];
                if (!hash) return;

                const targetEl = document.getElementById(hash);
                if (targetEl) {
                    e.preventDefault();

                    const targetTop = targetEl.getBoundingClientRect().top + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: Math.max(0, targetTop),
                        behavior: 'smooth'
                    });

                    // Accessibility: focus target without scrolling
                    try {
                        const hadTabindex = targetEl.hasAttribute('tabindex');
                        if (!hadTabindex) targetEl.setAttribute('tabindex', '-1');
                        targetEl.focus({ preventScroll: true });
                        if (!hadTabindex) targetEl.removeAttribute('tabindex');
                    } catch (err) {}

                    if (mobileMenu && mobileMenu.classList.contains('active')) {
                        mobileMenu.classList.remove('active');
                        if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'false');
                    }
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