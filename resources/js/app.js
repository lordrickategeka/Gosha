import './bootstrap';

// Guarded Alpine initialization: only initialize if not already present.
if (!window.Alpine) {
	import('alpinejs').then(({ default: Alpine }) => {
		window.Alpine = Alpine;
		if (!window.Alpine.__started && typeof window.Alpine.start === 'function') {
			Alpine.start();
			window.Alpine.__started = true;
		}
	}).catch(err => {
		console.warn('Failed to load Alpine:', err);
	});
} else {
	if (!window.Alpine.__started && typeof window.Alpine.start === 'function') {
		window.Alpine.start();
		window.Alpine.__started = true;
	}
}
