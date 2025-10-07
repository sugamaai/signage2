// self.addEventListener("install", function (event) {
//     console.log("SW installed");
//     event.waitUntil(
//         caches.open("v1").then(function (cache) {
//             return cache.addAll(["/offline.html", "/index.html"]);
//         })
//     );
// });

// self.addEventListener("activate", function (event) {
//     console.log("SW activated");
//     event.waitUntil(self.clients.claim());
// });

// self.addEventListener("fetch", function (event) {
//     event.respondWith(
//         fetch(event.request).catch(function () {
//             return caches.open("v1").then(function (cache) {
//                 return cache.match(event.request).then(function (response) {
//                     return response || cache.match("/offline.html");
//                 });
//             });
//         })
//     );
// });
self.addEventListener('fetch', function (e) {
})
