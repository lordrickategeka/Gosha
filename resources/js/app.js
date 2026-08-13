import './bootstrap';

// Guarded Alpine initialization: only initialize if not already present.
// if (!window.Alpine) {
// 	import('alpinejs').then(({ default: Alpine }) => {
// 		window.Alpine = Alpine;
// 		if (!window.Alpine.__started && typeof window.Alpine.start === 'function') {
// 			Alpine.start();
// 			window.Alpine.__started = true;
// 		}
// 	}).catch(err => {
// 		console.warn('Failed to load Alpine:', err);
// 	});
// } else {
// 	if (!window.Alpine.__started && typeof window.Alpine.start === 'function') {
// 		window.Alpine.start();
// 		window.Alpine.__started = true;
// 	}
// }


// import './bootstrap';

// Alpine.js (if not using Livewire's built-in Alpine)
// import Alpine from 'alpinejs';
// window.Alpine = Alpine;
// Alpine.start();

// Global helpers
window.formatCurrency = function(amount, currency = 'UGX') {
    return new Intl.NumberFormat('en-UG', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

window.formatDate = function(date) {
    return new Intl.DateTimeFormat('en-UG', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
};

window.formatTime = function(time) {
    return new Intl.DateTimeFormat('en-UG', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).format(new Date(`2000-01-01T${time}`));
};

// Toast notifications via Livewire events
document.addEventListener('livewire:initialized', () => {
    Livewire.on('toast', (event) => {
        // You can integrate with a toast library here
        console.log('Toast:', event);
    });

    Livewire.on('confirm', (event) => {
        if (confirm(event.message)) {
            Livewire.dispatch(event.onConfirmed);
        }
    });
});

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    const dropdowns = document.querySelectorAll('.dropdown.dropdown-open');
    dropdowns.forEach((dropdown) => {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('dropdown-open');
            dropdown.querySelector('details')?.removeAttribute('open');
        }
    });
});

// Clickable table rows: <tr data-href="/work-orders/1042"> (ported from detheme/garagehq.js)
document.addEventListener('click', (e) => {
    const row = e.target.closest('[data-href]');
    if (!row) return;
    if (e.target.closest('button, a, input, select')) return;
    window.location.href = row.getAttribute('data-href');
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.querySelector('[data-search-input]')?.focus();
    }

    // Escape to close modals
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.modal-open');
        modals.forEach((modal) => {
            modal.classList.remove('modal-open');
        });
    }
});
