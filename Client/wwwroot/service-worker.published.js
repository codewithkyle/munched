// Caution! Be sure you understand the caveats before publishing an application with
// offline support. See https://aka.ms/blazor-offline-considerations

self.importScripts('./service-worker-assets.js');
self.addEventListener('install', event => event.waitUntil(onInstall(event)));
self.addEventListener('activate', event => event.waitUntil(onActivate(event)));
self.addEventListener('fetch', event => event.respondWith(onFetch(event)));

const cacheNamePrefix = 'resource-cache-';
const apiCachePrefix = "api-cache-";
const imageCacheName = "image-cache";
const cacheName = `${cacheNamePrefix}${self.assetsManifest.version}`;
const apiCacheName = `${apiCachePrefix}${self.assetsManifest.version}`;
const offlineAssetsInclude = [ /\.dll$/, /\.pdb$/, /\.wasm/, /\.html/, /\.js$/, /\.css$/, /\.png$/, /\.jpeg$/, /\.jpg$/, /\.gif$/, /\.webp$/, /\.svg$/, /\.mp3$/, /\.wav$/, /\.json$/, /\.webmanifest$/ ];
const offlineAssetsExclude = [ /service-worker\.js$/, /app\.json$/, ];

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

async function tryAppCache(request){
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    return cachedResponse;
}

async function tryImageCache(request){
    const cache = await caches.open(imageCacheName);
    const cachedResponse = await cache.match(request);
    return cachedResponse;
}

async function tryFetch(request){
    const response =  await fetch(request);
    // Skip caching bad responses
    if (!response || response.status !== 200 || response.type !== "basic" && response.type !== "cors" || response.redirected) {
        return response;
    }
    // Only cache image API responses
    if (response.type === "cors"){
        const responseToCache = response.clone();
        if (response.url.indexOf("/v1/image/") !== -1){
            const imgCache = await caches.open(imageCacheName);
            await imgCache.put(request, responseToCache);
        } else {
            const apiCache = await caches.open(apiCacheName);
            await apiCache.put(request, responseToCache);
        }
    } else if (response.type === "basic"){
        await appCache.put(request, responseToCache);
    }
    return response;
}

async function onFetch(event) {
    const shouldServeIndexHtml = event.request.mode === 'navigate';
    const request = shouldServeIndexHtml ? 'index.html' : event.request;
    try {
        if (event.request.method === 'GET' && !event.request.url.match(/app\.json$/)) {
            let response = await tryAppCache(request);
            if (!response){
                if (event.request.url.indexOf("/v1/image/") !== -1){
                    response = await tryImageCache(event.request);
                    if (response){
                        return response;
                    }
                }
            }
            if (!response){
                response = await tryFetch(event.request);
            }
            return response;
        } else {
            return fetch(event.request);
        }
    } catch (e){
        // API cache is only hit when the client doesn't have a network connection
        const apiCache = await caches.open(apiCacheName);
        const cachedResponse = await apiCache.match(request);
        return cachedResponse;
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

function clearCache(){
    caches.delete(imageCacheName);
    caches.delete(apiCacheName);
    indexedDB.deleteDatabase("localdb");
}

self.onmessage = async (event) => {
    const { type } = event.data;
    switch (type){
		case "login":
			reloadClients();
			break;
		case "logout":
            clearCache();
			reloadClients();
			break;
        case "reinstall":
            await cachebust();
            break;
        default:
            break;
    }
}
