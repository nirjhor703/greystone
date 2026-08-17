import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener(
    'click',
    (event) => {
        const closeButton = event.target.closest(
            [
                '[data-close-modal]',
                '[data-close-admin-modal]',
                '[data-close-logout-modal]',
                '[data-close-admin-permission-modal]',
                '[data-close-root-passcode-modal]',
            ].join(',')
        );

        if (!closeButton) {
            return;
        }

        const modalId =
            closeButton.dataset.closeModal
            || closeButton.dataset.closeAdminModal;

        const modal =
            modalId
                ? document.getElementById(modalId)
                : closeButton.closest('.brand-modal');

        if (!modal || !modal.classList.contains('open')) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        if (closeButton.matches('[data-close-root-passcode-modal]')) {
            window.dispatchEvent(
                new CustomEvent('admin-root-passcode-modal-closing')
            );
        }

        modal.classList.add('closing');
        modal.setAttribute('aria-hidden', 'true');

        window.setTimeout(() => {
            modal.classList.remove('open', 'closing');

            if (!document.querySelector('.brand-modal.open')) {
                document.body.classList.remove('brand-modal-open');
            }
        }, 210);
    },
    true
);

import './admin/brands.js';
import './admin/admin-users.js';
import './admin/categories.js';
import './admin/coupons.js';
import './admin/orders.js';
import './admin/products.js';
import './admin/reports.js';
import './admin/search.js';
import './storefront/cart.js';
import './storefront/checkout.js';
import './storefront/category-filter';
import './storefront/coupon-popup';
import './storefront/launch';
