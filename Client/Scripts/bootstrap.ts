function LoadStaticScripts() {
	const staticScripts = [
		"/js/utilities.js",
		"/js/tooltipper.js",
		"/js/lit-html.js",
		"/js/idb-manager.js",
		"/js/auth.js",
		"/js/account.js",
		"/js/admin.js",
		"/_framework/blazor.webassembly.js",
	];
	for (let i = 0; i < staticScripts.length; i++) {
		const script = document.createElement("script");
		script.src = staticScripts[i];
		document.head.appendChild(script);
	}
}

async function Bootstrap() {
	let latestVersion = null;
	const loadedVersion = localStorage.getItem("version");
	const request = await fetch(`${location.origin}/app.json`, {
		headers: new Headers({
			Accept: "application/json",
		}),
		cache: "no-cache",
	});
	if (request.ok) {
		const response = await request.json();
		latestVersion = response.build;
		localStorage.setItem("version", latestVersion);
	}
	if (loadedVersion !== latestVersion && loadedVersion !== null) {
		const sw: ServiceWorker = navigator?.serviceWorker?.controller ?? null;
		if (sw) {
			sw.postMessage({
				type: "reinstall",
			});
		}
		snackbar({
			message: `An update has been installed.`,
			buttons: [
				{
					label: "reload",
					autofocus: true,
					callback: () => {
						location.reload();
					},
				},
			],
			duration: Infinity,
			force: true,
			closeable: false,
			classes: ["install-notification"],
		});
		const app: HTMLElement = document.body.querySelector("app");
		app.style.display = "none";
	} else {
		localStorage.setItem("version", latestVersion);
		LoadStaticScripts();
	}
}
Bootstrap();
