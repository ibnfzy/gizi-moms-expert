(function () {
    const storageKey = 'gizi-theme';
    const root = document.documentElement;

    const readStoredTheme = () => {
        try {
            return localStorage.getItem(storageKey);
        } catch (error) {
            return null;
        }
    };

    const writeStoredTheme = (value) => {
        try {
            localStorage.setItem(storageKey, value);
        } catch (error) {
            // Ignore storage write errors (e.g., private mode)
        }
    };

    const getPreferredTheme = () => {
        const stored = readStoredTheme();
        if (stored === 'dark' || stored === 'light') {
            return stored;
        }

        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        return prefersDark ? 'dark' : 'light';
    };

    const setRootTheme = (theme, persist = true) => {
        const normalized = theme === 'dark' ? 'dark' : 'light';
        root.classList.toggle('dark', normalized === 'dark');
        root.setAttribute('data-theme', normalized);

        if (persist) {
            writeStoredTheme(normalized);
        }
    };

    const updateToggleState = (theme) => {
        const toggles = document.querySelectorAll('[data-theme-toggle]');
        const label = theme === 'dark' ? 'Aktifkan mode terang' : 'Aktifkan mode gelap';
        const isDark = theme === 'dark';

        toggles.forEach((toggle) => {
            toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            toggle.setAttribute('aria-label', label);
            toggle.setAttribute('title', label);
            toggle.dataset.themeState = theme;

            const labelTarget = toggle.querySelector('[data-theme-toggle-text]');
            if (labelTarget) {
                labelTarget.textContent = label;
            }

            const sunIcon = toggle.querySelector('[data-theme-icon="sun"]');
            const moonIcon = toggle.querySelector('[data-theme-icon="moon"]');

            if (sunIcon) {
                sunIcon.classList.toggle('hidden', isDark);
            }

            if (moonIcon) {
                moonIcon.classList.toggle('hidden', !isDark);
            }
        });
    };

    const syncTheme = (theme, persist = true) => {
        setRootTheme(theme, persist);
        updateToggleState(theme);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const initialTheme = root.getAttribute('data-theme') || getPreferredTheme();
        const hasStoredTheme = readStoredTheme();

        if (!hasStoredTheme) {
            writeStoredTheme(initialTheme);
        }

        updateToggleState(initialTheme);

        document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                syncTheme(nextTheme);
            });
        });

        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', (event) => {
                if (readStoredTheme()) {
                    return;
                }

                syncTheme(event.matches ? 'dark' : 'light', false);
            });
        }

        const mobileDrawer = document.querySelector('[data-mobile-nav-drawer]');
        if (mobileDrawer) {
            const panel = mobileDrawer.querySelector('[data-mobile-nav-panel]');
            const backdrop = mobileDrawer.querySelector('[data-mobile-nav-backdrop]');
            const toggles = document.querySelectorAll('[data-mobile-nav-toggle]');
            const closeButtons = mobileDrawer.querySelectorAll('[data-mobile-nav-close]');
            let hideTimer = null;
            let isExpanded = false;

            const setExpandedState = (expanded) => {
                isExpanded = expanded;
                toggles.forEach((toggle) => {
                    toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                });
                if (panel) {
                    panel.setAttribute('aria-hidden', expanded ? 'false' : 'true');
                }
            };

            const openDrawer = () => {
                if (!panel) {
                    return;
                }

                if (hideTimer) {
                    window.clearTimeout(hideTimer);
                    hideTimer = null;
                }

                panel.classList.remove('hidden');
                if (backdrop) {
                    backdrop.classList.remove('hidden');
                }

                requestAnimationFrame(() => {
                    panel.classList.remove('translate-x-full');
                    if (backdrop) {
                        backdrop.classList.remove('opacity-0');
                    }
                });

                setExpandedState(true);
                document.body.classList.add('overflow-hidden');
            };

            const closeDrawer = () => {
                if (!panel) {
                    return;
                }

                panel.classList.add('translate-x-full');
                if (backdrop) {
                    backdrop.classList.add('opacity-0');
                }

                setExpandedState(false);
                document.body.classList.remove('overflow-hidden');

                hideTimer = window.setTimeout(() => {
                    panel.classList.add('hidden');
                    if (backdrop) {
                        backdrop.classList.add('hidden');
                    }
                    hideTimer = null;
                }, 200);
            };

            toggles.forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    if (isExpanded) {
                        closeDrawer();
                    } else {
                        openDrawer();
                    }
                });
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeDrawer);
            });

            if (backdrop) {
                backdrop.addEventListener('click', closeDrawer);
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && isExpanded) {
                    closeDrawer();
                }
            });
        }
    });
})();
