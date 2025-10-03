self.addEventListener("install", function (event) {
    console.log("SW installed");
    self.skipWaiting(); // すぐに有効化
});

self.addEventListener("activate", function (event) {
    console.log("SW activated");
    event.waitUntil(self.clients.claim()); // 全ページに即反映
});

self.addEventListener("fetch", function (event) {
    event.respondWith(
        fetch(event.request).catch(function () {
            // ネットワークエラー時に落ちないようにする
            return Response.error();
        })
    );
});
