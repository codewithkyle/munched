// Caution! Be sure you understand the caveats before publishing an application with
// offline support. See https://aka.ms/blazor-offline-considerations

self.importScripts('./service-worker-assets.js');
self.addEventListener('install', event => event.waitUntil(onInstall(event)));
self.addEventListener('activate', event => event.waitUntil(onActivate(event)));
self.addEventListener('fetch', event => event.respondWith(onFetch(event)));

const cacheNamePrefix = 'offline-cache-';
const cacheName = `${cacheNamePrefix}${self.assetsManifest.version}`;
const offlineAssetsInclude = [ /\.dll$/, /\.pdb$/, /\.wasm/, /\.html/, /\.js$/, /\.css$/, /\.png$/, /\.jpeg$/, /\.jpg$/, /\.gif$/, /\.mp3$/, /\.wav$/ ];
const offlineAssetsExclude = [ /^service-worker\.js$/, /^app\.json$/, ];

async function onInstall(event) {
    self.skipWaiting();
    const assetsRequests = self.assetsManifest.assets
        .filter(asset => offlineAssetsInclude.some(pattern => pattern.test(asset.url)))
        .filter(asset => !offlineAssetsExclude.some(pattern => pattern.test(asset.url)))
        .map(asset => new Request(asset.url));
	for (const request of assetsRequests){
		await caches.open(cacheName).then(cache => cache.add(request)).catch(error => {
			console.error("Failed to cache:", request);
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
    let cachedResponse = null;
    if (event.request.method === 'GET') {
        // For all navigation requests, try to serve index.html from cache
        // If you need some URLs to be server-rendered, edit the following check to exclude those URLs
        const shouldServeIndexHtml = event.request.mode === 'navigate';

        const request = shouldServeIndexHtml ? 'index.html' : event.request;
        const cache = await caches.open(cacheName);
        cachedResponse = await cache.match(request);
    }

    return cachedResponse || fetch(event.request);
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
