// MRM Grocery & Wholesale - Main JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // ===== HAMBURGER MENU =====
    const hamburger = document.getElementById('hamburger');
    const mobileNav = document.getElementById('mobileNav');
    if (hamburger && mobileNav) {
        hamburger.addEventListener('click', function () {
            mobileNav.classList.toggle('open');
            hamburger.classList.toggle('active');
        });
    }

    // ===== ADMIN SIDEBAR TOGGLE (mobile) =====
    const adminToggle = document.getElementById('adminSidebarToggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    if (adminToggle && adminSidebar) {
        adminToggle.addEventListener('click', function () {
            adminSidebar.classList.toggle('open');
        });
    }

    // ===== TOAST SYSTEM =====
    window.showToast = function (msg, type = 'success') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        const icons = { success: '✅', error: '❌', info: 'ℹ️' };
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span>${icons[type] || '✅'}</span> <span>${msg}</span>`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('removing');
            toast.addEventListener('animationend', () => toast.remove());
        }, 3000);
    };

    // ===== ADD TO CART (AJAX) =====
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-cart');
        if (!btn) return;
        const productId = btn.dataset.productId;
        const qtyInput = document.getElementById('detail-qty');
        const qty = qtyInput ? parseInt(qtyInput.value) : 1;
        if (!productId) return;

        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="loading"></span>';
        btn.disabled = true;

        fetch('cart-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&product_id=${productId}&qty=${qty}`
        })
        .then(r => r.json())
        .then(data => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            if (data.success) {
                showToast(data.message || 'Added to cart!', 'success');
                // Update cart badge
                const badges = document.querySelectorAll('.cart-badge');
                badges.forEach(b => { b.textContent = data.cart_count; });
            } else {
                showToast(data.message || 'Error adding to cart', 'error');
                if (data.redirect) window.location.href = data.redirect;
            }
        })
        .catch(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            showToast('Something went wrong', 'error');
        });
    });

    // ===== CART QUANTITY UPDATE =====
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('cart-qty-btn')) {
            const btn = e.target;
            const input = btn.closest('.cart-qty-control')?.querySelector('.cart-qty-input');
            if (!input) return;
            let val = parseInt(input.value) || 1;
            if (btn.dataset.action === 'inc') val = Math.min(val + 1, 99);
            if (btn.dataset.action === 'dec') val = Math.max(val - 1, 1);
            input.value = val;
            updateCartItem(input.dataset.cartId, val);
        }
        // Qty buttons on product detail page
        if (e.target.classList.contains('qty-btn')) {
            const btn = e.target;
            const input = document.getElementById('detail-qty');
            if (!input) return;
            let val = parseInt(input.value) || 1;
            const max = parseInt(input.max) || 99;
            if (btn.dataset.action === 'inc') val = Math.min(val + 1, max);
            if (btn.dataset.action === 'dec') val = Math.max(val - 1, 1);
            input.value = val;
        }
    });

    function updateCartItem(cartId, qty) {
        fetch('cart-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update&cart_id=${cartId}&qty=${qty}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const priceEl = document.querySelector(`.item-subtotal[data-cart-id="${cartId}"]`);
                if (priceEl) priceEl.textContent = data.subtotal;
                const totalEl = document.getElementById('cart-total');
                if (totalEl) totalEl.textContent = data.cart_total;
                const badges = document.querySelectorAll('.cart-badge');
                badges.forEach(b => { b.textContent = data.cart_count; });
            }
        });
    }

    // ===== REMOVE FROM CART =====
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove')) {
            const btn = e.target.closest('.btn-remove');
            const cartId = btn.dataset.cartId;
            if (!confirm('Remove this item from cart?')) return;
            fetch('cart-action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=remove&cart_id=${cartId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest('.cart-item');
                    if (row) row.remove();
                    const badges = document.querySelectorAll('.cart-badge');
                    badges.forEach(b => { b.textContent = data.cart_count; });
                    const totalEl = document.getElementById('cart-total');
                    if (totalEl) totalEl.textContent = data.cart_total;
                    showToast('Item removed', 'info');
                    if (data.cart_count === 0) location.reload();
                }
            });
        }
    });

    // ===== IMAGE PREVIEW (admin) =====
    const imgInput = document.getElementById('product_image');
    const imgPreview = document.getElementById('imgPreview');
    if (imgInput && imgPreview) {
        imgInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    imgPreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ===== PAYMENT OPTION SELECTION =====
    document.querySelectorAll('.payment-option').forEach(opt => {
        opt.addEventListener('click', function () {
            document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });
    // Mark initially selected
    const checkedRadio = document.querySelector('.payment-option input:checked');
    if (checkedRadio) checkedRadio.closest('.payment-option')?.classList.add('selected');

    // ===== TABLE SEARCH (admin) =====
    const tableSearch = document.getElementById('tableSearch');
    if (tableSearch) {
        tableSearch.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ===== FORM VALIDATION =====
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            let valid = true;
            this.querySelectorAll('[required]').forEach(field => {
                field.classList.remove('error');
                if (!field.value.trim()) {
                    field.classList.add('error');
                    valid = false;
                }
            });
            if (!valid) {
                e.preventDefault();
                showToast('Please fill all required fields', 'error');
            }
        });
    }

    // ===== SALE PRICE TOGGLE (admin) =====
    const saleCheck = document.getElementById('is_on_sale');
    const salePriceWrap = document.getElementById('salePriceWrap');
    function toggleSalePrice() {
        if (!salePriceWrap) return;
        if (saleCheck && saleCheck.checked) {
            salePriceWrap.style.display = '';
        } else {
            salePriceWrap.style.display = 'none';
        }
    }
    if (saleCheck) {
        saleCheck.addEventListener('change', toggleSalePrice);
        toggleSalePrice();
    }

    // ===== AUTO DISMISS ALERTS =====
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

    // Confirm deletes
    document.querySelectorAll('.confirm-delete').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

});
