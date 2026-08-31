</main>
<script>
document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', event => {
        let ok = true;
        form.querySelectorAll('[required]').forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('invalid');
                ok = false;
            } else {
                input.classList.remove('invalid');
            }
        });

        const email = form.querySelector('input[type="email"]');
        if (email && email.value.trim()) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regex.test(email.value.trim())) {
                email.classList.add('invalid');
                ok = false;
                alert('Please enter a valid email address.');
            }
        }

        const passwords = form.querySelectorAll('input[data-min-password]');
        passwords.forEach(input => {
            if (input.value.length < Number(input.dataset.minPassword)) {
                input.classList.add('invalid');
                ok = false;
                alert('Password must be at least ' + input.dataset.minPassword + ' characters.');
            }
        });

        const confirm = form.querySelector('input[data-confirm-password]');
        const newPassword = form.querySelector('input[name="new_password"]');
        if (confirm && newPassword && confirm.value !== newPassword.value) {
            confirm.classList.add('invalid');
            ok = false;
            alert('Passwords do not match.');
        }

        if (!ok) event.preventDefault();
    });
});
</script>
</body>
</html>
