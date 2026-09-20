document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('smsSidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');
    if (toggle && sidebar) toggle.addEventListener('click', () => { sidebar.classList.toggle('sms-sidebar-open'); overlay?.classList.toggle('active'); });
    if (overlay && sidebar) overlay.addEventListener('click', () => { sidebar.classList.remove('sms-sidebar-open'); overlay.classList.remove('active'); });

    document.querySelectorAll('[data-password-toggle]').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            const icon = button.querySelector('i');
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            icon?.classList.toggle('bi-eye'); icon?.classList.toggle('bi-eye-slash');
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => { if (!confirm(form.dataset.confirm || 'Are you sure?')) e.preventDefault(); });
    });

    document.querySelectorAll('.mark-input').forEach(input => {
        input.addEventListener('input', () => {
            const row = input.closest('tr');
            const values = [...row.querySelectorAll('.mark-input')].map(i => parseFloat(i.value || 0));
            const total = row.querySelector('.mark-total');
            if (total) total.textContent = values.reduce((a,b)=>a+b,0).toFixed(2);
        });
    });
});
