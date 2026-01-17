document.addEventListener('DOMContentLoaded', () => {
    // Tab Switching Logic
    const tabs = document.querySelectorAll('.tab-btn');
    const forms = document.querySelectorAll('form');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            tab.classList.add('active');

            // Hide all forms
            forms.forEach(f => f.classList.remove('active-form'));
            // Show target form
            const targetId = tab.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active-form');
        });
    });

    // Validation Logic
    const setupValidation = (formId) => {
        const form = document.getElementById(formId);
        constinputs = form.querySelectorAll('input, textarea');

        form.addEventListener('submit', (e) => {
            let isValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) isValid = false;
            });
            if (!isValid) e.preventDefault();
        });

        inputs.forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => {
                if (input.closest('.input-group').classList.contains('error')) {
                    validateField(input);
                }
            });
        });
    };

    const validateField = (input) => {
        const group = input.closest('.input-group');
        const errorSpan = group.querySelector('.error-message');
        let isValid = true;
        let msg = '';

        if (input.hasAttribute('required') && !input.value.trim()) {
            isValid = false;
            msg = 'Field is required';
        } else if (input.name === 'phone') {
            const phoneRegex = /^\d{10}$/;
            if (!phoneRegex.test(input.value.trim())) {
                isValid = false;
                msg = 'Phone number must be exactly 10 digits';
            }
        } else if (input.name === 'password') {
            if (input.value.length < 8) {
                isValid = false;
                msg = 'Password must be at least 8 characters';
            }
        } else if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                isValid = false;
                msg = 'Invalid email';
            }
        }

        if (!isValid) {
            group.classList.add('error');
            errorSpan.textContent = msg;
        } else {
            group.classList.remove('error');
            errorSpan.textContent = '';
        }

        return isValid;
    };

    setupValidation('login-form');
    setupValidation('signup-form');
});
