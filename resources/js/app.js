// Register the service worker, so the app can be installed on the home screen.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((error) => {
            console.warn('Service worker gagal didaftarkan', error);
        });
    });
}
