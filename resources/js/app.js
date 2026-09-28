const ready = (callback) => {
    if (document.readyState !== 'loading') {
        callback();
        return;
    }

    document.addEventListener('DOMContentLoaded', callback);
};

const setupSidebar = () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const toggles = document.querySelectorAll('[data-sidebar-toggle]');

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => sidebar?.classList.toggle('sidebar--open'));
    });
};

const setupDropdowns = () => {
    document.querySelectorAll('[data-dropdown]').forEach((wrapper) => {
        const toggle = wrapper.querySelector('[data-dropdown-toggle]');
        const panel = wrapper.querySelector('[data-dropdown-panel]');

        if (!toggle || !panel) {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();

            document.querySelectorAll('[data-dropdown-panel]').forEach((other) => {
                if (other !== panel) {
                    other.classList.remove('dropdown-panel--open');
                }
            });

            const isOpen = panel.classList.toggle('dropdown-panel--open');
            toggle.setAttribute('aria-expanded', String(isOpen));
        });
    });

    document.addEventListener('click', (event) => {
        document.querySelectorAll('.dropdown-panel--open').forEach((panel) => {
            panel.classList.remove('dropdown-panel--open');
            panel.closest('[data-dropdown]')?.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
        });

        if (event.target.closest('[data-dropdown]')) {
            event.stopPropagation();
        }
    });
};

const setupAlerts = () => {
    document.querySelectorAll('[data-alert-close]').forEach((button) => {
        button.addEventListener('click', () => button.closest('.alert')?.remove());
    });
};

const TOAST_DELAY = 6000;

const setupToasts = () => {
    const stack = document.querySelector('[data-toast]');

    if (!stack) {
        return;
    }

    window.setTimeout(() => {
        stack.querySelectorAll('.alert').forEach((alert) => alert.remove());

        if (stack.children.length === 0) {
            stack.remove();
        }
    }, TOAST_DELAY);
};

const setupFlashModals = async () => {
    const payload = document.querySelector('[data-flash]');

    if (!payload) {
        return;
    }

    const { showFlashModals } = await import('./flash');

    await showFlashModals(JSON.parse(payload.textContent));
};

const setupModals = () => {
    document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
        const dialog = document.getElementById(trigger.dataset.modalOpen);

        if (!dialog) {
            return;
        }

        trigger.addEventListener('click', () => {
            if (!dialog.open) {
                dialog.showModal();
            }
        });
    });

    document.addEventListener('click', (event) => {
        const closeButton = event.target.closest('[data-modal-close]');

        if (closeButton) {
            closeButton.closest('dialog')?.close();
            return;
        }

        const dialog = event.target.closest('dialog.modal--form');

        if (dialog && event.target === dialog) {
            dialog.close();
        }
    });
};

const setupConfirmModal = () => {
    const modal = document.querySelector('[data-confirm-modal]');

    if (!modal) {
        return;
    }

    let pending = null;

    document.querySelectorAll('[data-confirm-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            event.stopPropagation();

            pending = form;
            modal.querySelector('[data-confirm-text]').textContent = form.dataset.confirm ?? 'Lanjutkan tindakan ini?';
            modal.showModal();
        });
    });

    modal.addEventListener('close', () => {
        if (modal.returnValue === 'confirm' && pending) {
            pending.submit();
        }

        pending = null;
    });
};

const setupPrint = () => {
    document.querySelectorAll('[data-print]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });
};

const LIVE_SEARCH_KEY = 'inventori:live-search';
const LIVE_SEARCH_DELAY = 350;

const setupLiveSearch = () => {
    const restoreFocus = sessionStorage.getItem(LIVE_SEARCH_KEY) === '1';

    sessionStorage.removeItem(LIVE_SEARCH_KEY);

    document.querySelectorAll('form.filters').forEach((form) => {
        const wrapper = form.querySelector('.filters__search');
        const field = wrapper?.querySelector('input');

        if (!wrapper || !field) {
            return;
        }

        const setBusy = (isBusy) => {
            wrapper.classList.toggle('filters__search--busy', isBusy);
            form.setAttribute('aria-busy', String(isBusy));
        };

        let settled = field.value.trim();
        let timer = null;

        const runSearch = () => {
            settled = field.value.trim();
            sessionStorage.setItem(LIVE_SEARCH_KEY, '1');
            setBusy(true);
            form.requestSubmit();
        };

        field.addEventListener('input', () => {
            window.clearTimeout(timer);

            if (field.value.trim() === settled) {
                return;
            }

            timer = window.setTimeout(runSearch, LIVE_SEARCH_DELAY);
        });

        field.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            window.clearTimeout(timer);

            if (field.value.trim() !== settled) {
                event.preventDefault();
                runSearch();
            }
        });

        form.addEventListener('submit', () => setBusy(true));

        window.addEventListener('pageshow', () => {
            if (wrapper.classList.contains('filters__search--busy')) {
                setBusy(false);
            }
        });

        if (restoreFocus && field.value.trim() !== '') {
            field.focus();
            field.setSelectionRange(field.value.length, field.value.length);
        }
    });
};

const setupStockAvailability = () => {
    document.querySelectorAll('[data-stock-availability]').forEach((panel) => {
        const field = document.getElementById('product_id');
        const list = panel.querySelector('[data-stock-availability-list]');
        const total = panel.querySelector('[data-stock-availability-total]');

        if (!field || !list) {
            return;
        }

        const matrix = JSON.parse(panel.dataset.matrix);

        const render = () => {
            const perLocation = field.value === '' ? {} : (matrix[field.value] ?? {});
            const rows = list.querySelectorAll('[data-location]');
            let sum = 0;

            rows.forEach((row) => {
                const qty = perLocation[row.dataset.location] ?? 0;

                sum += qty;
                row.querySelector('[data-stock-availability-qty]').textContent = qty;
                row.classList.toggle('availability__item--empty', qty === 0);
            });

            if (total) {
                total.textContent = `Total ${sum}`;
            }

            panel.classList.toggle('availability--empty', field.value === '');
        };

        field.addEventListener('change', render);
    });
};

ready(() => {
    setupSidebar();
    setupDropdowns();
    setupAlerts();
    setupToasts();
    setupFlashModals();
    setupModals();
    setupConfirmModal();
    setupPrint();
    setupLiveSearch();
    setupStockAvailability();
});
