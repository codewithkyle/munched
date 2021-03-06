// Caution! Be sure you understand the caveats before publishing an application with
// offline support. See https://aka.ms/blazor-offline-considerations

self.importScripts('./service-worker-assets.js');
self.addEventListener('install', event => event.waitUntil(onInstall(event)));
self.addEventListener('activate', event => event.waitUntil(onActivate(event)));
self.addEventListener('fetch', event => event.respondWith(onFetch(event)));

const cacheNamePrefix = 'offline-cache-';
const cacheName = `${cacheNamePrefix}${self.assetsManifest.version}`;
const offlineAssetsInclude = [ /\.dll$/, /\.pdb$/, /\.wasm/, /\.html/, /\.js$/, /\.css$/, /\.png$/, /\.jpeg$/, /\.jpg$/, /\.gif$/, /\.webp$/, /\.mp3$/, /\.wav$/ ];
const offlineAssetsExclude = [ /^service-worker\.js$/, /^app\.json$/, ];

async function onInstall(event) {
    self.skipWaiting();
    const assetsRequests = self.assetsManifest.assets
        .filter(asset => offlineAssetsInclude.some(pattern => pattern.test(asset.url)))
        .filter(asset => !offlineAssetsExclude.some(pattern => pattern.test(asset.url)))
        .map(asset => new Request(asset.url));
	for (const request of assetsRequests){
		await caches.open(cacheName).then(cache => cache.add(request)).catch(error => {
			console.error("Failed to cache:", request, error);
		});
	}
}

async function onActivate(event) {
    const cacheKeys = await caches.keys();
    await Promise.all(cacheKeys
        .filter(key => key.startsWith(cacheNamePrefix) && key !== cacheName)
        .map(key => caches.delete(key)));
}

async function onFetch(event) {
    try {
        if (event.request.method === 'GET' && !event.request.url.match(/app\.json$/)) {
            const shouldServeIndexHtml = event.request.mode === 'navigate';
            const request = shouldServeIndexHtml ? 'index.html' : event.request;
            const cache = await caches.open(cacheName);
            const cachedResponse = await cache.match(request);
            if (!cachedResponse){
                return fetch(event.request).then(async (response) => {
                    // Skip caching bad responses
                    if (!response || response.status !== 200 || response.type !== "basic" && response.type !== "cors" || response.redirected) {
                        return response;
                    }
                    // Only cache image API responses
                    if (response.type === "cors" && response.url.indexOf("/v1/image/") !== -1){
                        var responseToCache = response.clone();
                        await cache.put(event.request, responseToCache);
                    }
                    return response;
                });
            } else {
                return cachedResponse;
            }
        } else {
            throw "Forced cache miss";
        }
    } catch (e){
        return fetch(event.request);   
    }
}

async function cachebust(){
    const cacheKeys = await caches.keys();
    await Promise.all(cacheKeys.map(key => caches.delete(key)));
}

function reloadClients(){
	self.clients.matchAll().then(clients => {
		clients.forEach(client => {
			if (!client.focused){
				client.postMessage({
					type: "reload",
				});
			}
		});
	});
}

self.onmessage = async (event) => {
    const { type } = event.data;
    switch (type){
		case "login":
			reloadClients();
			break;
		case "logout":
			reloadClients();
			break;
        case "reinstall":
            await cachebust();
            break;
        default:
            break;
    }
}
