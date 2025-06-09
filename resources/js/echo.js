import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST, // e.g. workflowtoolstaging.godspeedoffers.com
    //wsPath: '/app', // this is CRUCIAL so it knows to use `/app` instead of root
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
});

