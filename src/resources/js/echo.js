/**
 * Laravel Echo Configuration
 *
 * Bundled locally for optimal performance
 * No CDN dependency - fully self-hosted
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Initialize Reverb WebSocket connection
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Global listeners
window.Echo.channel('data-updated').listen('.data.updated', (event) => {
    console.log('[Reverb] Data updated:', event);
    
    // Trigger dashboard refresh if we are on the dashboard
    if (window.refreshDashboardStats) {
        window.refreshDashboardStats();
    }

    if (window.reloadTableData) {
        window.reloadTableData(event.model, event.id, event.action);
    }
});
