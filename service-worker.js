var CACHE_NAME = "buffet-chay-staff-v3";
var STATIC_ASSETS = [
  "public/manifest.webmanifest",
  "public/offline.html",
  "public/assets/icons/pwa-icon.svg",
  "public/assets/css/nhanvien/dashboard.css",
  "public/assets/css/nhanvien/orders.css",
  "public/assets/css/nhanvien/reservations.css",
  "public/assets/js/nhanvien-dashboard.js",
  "public/assets/js/nhanvien-reservations.js",
];

function sameOrigin(requestUrl) {
  return requestUrl.origin === self.location.origin;
}

function shouldBypass(request) {
  var url = new URL(request.url);
  return (
    request.method !== "GET" ||
    !sameOrigin(url) ||
    url.pathname.indexOf("/nhan-vien/don-mon/su-kien") !== -1 ||
    url.pathname.indexOf("/nhan-vien/danh-sach-ban") !== -1 ||
    url.pathname.indexOf("/nhan-vien/don-theo-ban") !== -1 ||
    url.pathname.indexOf("/nhan-vien/dat-ban/") !== -1
  );
}

function shouldUseNetworkFirst(request) {
  var url = new URL(request.url);
  return (
    url.pathname.indexOf("/public/assets/js/") !== -1 ||
    url.pathname.indexOf("/public/assets/css/") !== -1
  );
}

self.addEventListener("install", function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.addAll(STATIC_ASSETS);
    }),
  );
  self.skipWaiting();
});

self.addEventListener("activate", function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.map(function (key) {
          if (key !== CACHE_NAME) return caches.delete(key);
          return Promise.resolve();
        }),
      );
    }),
  );
  self.clients.claim();
});

self.addEventListener("message", function (event) {
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }
});

self.addEventListener("fetch", function (event) {
  var request = event.request;
  if (shouldBypass(request)) return;

  if (request.mode === "navigate") {
    event.respondWith(
      fetch(request)
        .then(function (response) {
          var copy = response.clone();
          caches.open(CACHE_NAME).then(function (cache) {
            cache.put(request, copy);
          });
          return response;
        })
        .catch(function () {
          return caches.match(request).then(function (cached) {
            return cached || caches.match("public/offline.html");
          });
        }),
    );
    return;
  }

  if (shouldUseNetworkFirst(request)) {
    event.respondWith(
      fetch(request)
        .then(function (response) {
          if (response && response.status === 200) {
            var copy = response.clone();
            caches.open(CACHE_NAME).then(function (cache) {
              cache.put(request, copy);
            });
          }
          return response;
        })
        .catch(function () {
          return caches.match(request);
        }),
    );
    return;
  }

  event.respondWith(
    caches.match(request).then(function (cached) {
      var network = fetch(request)
        .then(function (response) {
          if (response && response.status === 200) {
            var copy = response.clone();
            caches.open(CACHE_NAME).then(function (cache) {
              cache.put(request, copy);
            });
          }
          return response;
        })
        .catch(function () {
          return cached;
        });
      return cached || network;
    }),
  );
});
