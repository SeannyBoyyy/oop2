const cacheName = 'pwa-cache-v2';
const staticAssets = [
  '/oop2/',
  '/oop2/user/user_homepage.php',
  /*
  '/oop2/include/userNav.php',
  '/oop2/user/view_foodproducts.php',
  '/oop2/user/userSignup.php',
  '/oop2/user/userLogout.php',
  '/oop2/user/userLogin.php',
  '/oop2/user/userDashboard.php',
  '/oop2/user/user_tickets.php',
  '/oop2/user/user_orders.php',
  '/oop2/user/select_showtime_click.php',
  '/oop2/user/paymongo_payment.php',
  '/oop2/user/paymongo_success.php',
  '/oop2/user/order_receipt.php',
  '/oop2/user/order_food.php',
  '/oop2/user/cinema_schedule.php',
  '/oop2/user/buy_now.php',

  */

  // CCS
  '/oop2/css/style.css',
  '/oop2/css/adminSignup.css',
  '/oop2/css/adminLogin.css',
  '/oop2/css/user_homepage.css',
  '/oop2/css/adminDashboard.css',
  // Manifest
  '/oop2/manifest.json',
  // Icons
  '/oop2/icons/icon-192x192.png',
  '/oop2/icons/icon-512x512.png',
  // Links
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
  'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Lato:wght@300;400;700&display=swap',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css',
  'https://code.jquery.com/jquery-3.6.0.min.js'
  // Add any more files you want to cache
];

// Install – Pre-cache files
self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(cacheName).then(cache => {
      return cache.addAll(staticAssets);
    })
  );
});

// Activate – Clean old cache
self.addEventListener('activate', event => {
  clients.claim();
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== cacheName)
            .map(key => caches.delete(key))
      );
    })
  );
});

// Fetch – Serve cached or go to network
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request)
        .then(networkResponse => {
          return caches.open(cacheName).then(cache => {
            cache.put(event.request, networkResponse.clone());
            return networkResponse;
          });
        })
      ).catch(() => caches.match('/oop2/offline.html'))
  );
});