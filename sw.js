self.addEventListener("install", function (event) {
    console.log("SW installed");
    // offline.html をキャッシュ
    event.waitUntil(
        caches.open("v1").then(function (cache) {
            return cache.addAll(["/offline.html"]);
        })
    );
});

self.addEventListener("activate", function (event) {
    console.log("SW activated");
    event.waitUntil(self.clients.claim());
});

self.addEventListener("fetch", function (event) {
    event.respondWith(
        fetch(event.request).catch(function () {
            // ネットがなければ offline.html を返す
            return caches.match("/offline.html");
        })
    );
});
