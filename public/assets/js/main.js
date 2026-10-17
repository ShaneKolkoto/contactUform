(function () {
    'use strict';
    const RULES = {
        name: {
            test: (v) => v.length >= 2 && v.length <= 100 && /^[\p{L}\p{M}\s'\-\.]+$/u.test(v),
            message: 'Please enter a valid name (2-100 characters, letters only).',
        },
        email: {
            test: (v) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) && v.length <= 255,
            message: 'Please enter a valid email address.',
        },
        phone: {
            test: (v) => normalizePhone(v) !== null,
            message: 'Please enter a valid South African phone number.',
        },
        message: {
            test: (v) => v.length >= 10 && v.length <= 5000,
            message: 'Message must be between 10 and 5000 characters.',
        },
    };

    function normalizePhone(input) {
        const digits = input.replace(/[\s\-\.\(\)]+/g, '');
        if (!digits) return null;

        const cleaned = digits.startsWith('+') ? digits.slice(1) : digits;
        if (!/^\d+$/.test(cleaned)) return null;

        let national = null;
        if (cleaned.length === 11 && cleaned.startsWith('27')) {
            national = cleaned.slice(2);
        } else if (cleaned.length === 10 && cleaned.startsWith('0')) {
            national = cleaned.slice(1);
        } else {
            return null;
        }

        return /^[2-8]\d{8}$/.test(national) ? `+27${national}` : null;
    }

    function initCharCounter() {
        const message = document.getElementById('message');
        const counter = document.getElementById('char-count');

        if (!message || !counter) return;

        const max = 5000;

        const update = () => {
            const length = message.value.length;
            counter.textContent = `${length} / ${max}`;

            // Visual cue as the user approaches the limit
            counter.classList.toggle('is-near-limit', length > max * 0.9);
        };

        message.addEventListener('input', update);
        update(); // Run once on load so it reflects any preserved value
    }

    function initInlineValidation() {
        const form = document.getElementById('contact-form');
        if (!form) return;

        const fields = ['name', 'email', 'phone', 'message'];

        fields.forEach((fieldName) => {
            const input = form.querySelector(`[name="${fieldName}"]`);
            if (!input) return;

            const rule = RULES[fieldName];

            // Validate when leaving the field
            input.addEventListener('blur', () => {
                if (input.value.trim() === '') {
                    // Empty required fields: don't show "invalid" on blur,
                    // just let the browser focus flow happen. Submit will catch it.
                    clearFieldState(input);
                    return;
                }

                const value = input.value.trim();
                if (rule.test(value)) {
                    markValid(input);
                } else {
                    markInvalid(input, rule.message);
                }
            });

            // If a field is already marked invalid, re-check as the user types
            input.addEventListener('input', () => {
                if (!input.classList.contains('is-invalid')) return;

                const value = input.value.trim();
                if (value === '') {
                    clearFieldState(input);
                    return;
                }

                if (rule.test(value)) {
                    markValid(input);
                }
            });
        });
    }

    function markValid(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        input.removeAttribute('aria-invalid');

        // Clear the error text if any
        const errorEl = document.getElementById(`${input.name}-error`);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        }
    }

    function markInvalid(input, message) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        input.setAttribute('aria-invalid', 'true');

        // Fill the inline error container if present
        const errorEl = document.getElementById(`${input.name}-error`);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = '';
        }
    }

    function clearFieldState(input) {
        input.classList.remove('is-invalid', 'is-valid');
        input.removeAttribute('aria-invalid');

        const errorEl = document.getElementById(`${input.name}-error`);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        }
    }

    // --------------------------------------------------------
    // Submit button — lock and show spinner
    // --------------------------------------------------------
    function initSubmitLock() {
        const form = document.getElementById('contact-form');
        const submitBtn = document.getElementById('submit-btn');
        if (!form || !submitBtn) return;

        form.addEventListener('submit', (e) => {
            // Prevent double-submits
            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }

            // Validate everything client-side first
            const fields = ['name', 'email', 'phone', 'message'];
            let firstInvalid = null;

            fields.forEach((fieldName) => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                if (!input) return;

                const value = input.value.trim();
                const rule = RULES[fieldName];

                if (value === '' || !rule.test(value)) {
                    markInvalid(input, rule.message);
                    if (!firstInvalid) firstInvalid = input;
                }
            });

            if (firstInvalid) {
                e.preventDefault();
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // All good — lock the button and show the spinner
            submitBtn.disabled = true;
            const spinner = submitBtn.querySelector('.btn__spinner');
            const label = submitBtn.querySelector('.btn__label');
            if (spinner) spinner.classList.remove('d-none');
            if (label) label.textContent = 'Sending…';

            // Safety: re-enable after 15s in case the network hangs
            // and the browser's back button returns to this page
            setTimeout(() => {
                submitBtn.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                if (label) label.textContent = 'Submit';
            }, 15000);
        });
    }

    // --------------------------------------------------------
    // Focus the error summary when the page loads with errors
    // --------------------------------------------------------
    function initErrorFocus() {
        const errorAlert = document.querySelector('.alert-danger');
        if (!errorAlert) return;

        // Focus the alert so screen readers announce it, but make it
        // programmatically focusable first.
        errorAlert.setAttribute('tabindex', '-1');
        errorAlert.focus({ preventScroll: true });
        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // --------------------------------------------------------
    // Wire everything up once the DOM is ready
    // --------------------------------------------------------
    function init() {
        initCharCounter();
        initInlineValidation();
        initSubmitLock();
        initErrorFocus();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();