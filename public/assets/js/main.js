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

        form.addEventListener('submit', function (event) {
            // If the browser's built-in validation is going to stop this
            // (e.g., required field empty, bad email), do not show the spinner.
            // The browser will handle it.
            if (!form.checkValidity()) {
                return;
            }

            // Lock the button
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');

            // Swap the label
            const label = submitBtn.querySelector('.btn__label');
            if (label) {
                label.textContent = 'Sending…';
            }

            // Show the spinner
            const spinner = submitBtn.querySelector('.btn__spinner');
            if (spinner) {
                spinner.classList.remove('d-none');
            }

            // Safety net: if the server hangs or something crashes, don't
            // leave the user stuck forever. Re-enable after 15 seconds.
            setTimeout(function () {
                submitBtn.disabled = false;
                submitBtn.classList.remove('is-loading');
                if (label) label.textContent = 'Submit';
                if (spinner) spinner.classList.add('d-none');
            }, 15000);
        });
    }

    // --------------------------------------------------------
    // Filter form — show loading state on submit
    // --------------------------------------------------------
    function initFilterLock() {
        const form = document.getElementById('filter-form');
        const filterBtn = document.getElementById('filter-btn');

        if (!form || !filterBtn) return;

        form.addEventListener('submit', function () {
            if (!form.checkValidity()) {
                return;
            }

            filterBtn.disabled = true;
            filterBtn.classList.add('is-loading');

            const label = filterBtn.querySelector('.btn__label');
            if (label) {
                label.textContent = 'Filtering…';
            }

            const spinner = filterBtn.querySelector('.btn__spinner');
            if (spinner) {
                spinner.classList.remove('d-none');
            }
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
        initFilterLock();
        initErrorFocus();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();