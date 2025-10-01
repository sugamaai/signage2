self.addEventListener("install", (event) => {
    console.log("Service Worker: Installed");
});

self.addEventListener("activate", (event) => {
    console.log("Service Worker: Activated");
});

// ネットワークから取得（キャッシュ処理はなし）
self.addEventListener("fetch", (event) => {
    event.respondWith(fetch(event.request));
});
