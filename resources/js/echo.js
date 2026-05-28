const enabled = import.meta.env.VITE_REVERB_ENABLED === 'true';
const key = import.meta.env.VITE_REVERB_APP_KEY;

if (enabled && key) {
    const { default: Echo } = await import('laravel-echo');
    const { default: Pusher } = await import('pusher-js');

    window.Pusher = Pusher;

    const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
    const isSecure = scheme === 'https';
    const host = import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1';
    const port = import.meta.env.VITE_REVERB_PORT ?? (isSecure ? 443 : 80);

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: key,
        wsHost: host,
        wsPort: port,
        wssPort: isSecure ? port : undefined,
        forceTLS: isSecure,
        enabledTransports: isSecure ? ['ws', 'wss'] : ['ws'],
    });
}
