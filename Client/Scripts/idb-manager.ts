class IDBManager {
	private queue: Array<any>;
	private ready: boolean;
	private worker: Worker;
	private promises: {
		[key: string]: Function;
	};
	constructor() {
		this.ready = false;
		this.queue = [];
		this.promises = {};
		this.worker = new Worker(`${location.origin}/js/idb-worker.js`);
		this.worker.onmessage = this.inbox.bind(this);
	}

	private inbox(e: MessageEvent) {
		const messageEventData = e.data;
		const { type, data, uid } = messageEventData;
		switch (type) {
			case "error":
				toast({
					title: "Error",
					message: data,
					closeable: false,
					classes: "-red",
					icon: `<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="exclamation-circle" class="svg-inline--fa fa-exclamation-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 448c-110.532 0-200-89.431-200-200 0-110.495 89.472-200 200-200 110.491 0 200 89.471 200 200 0 110.53-89.431 200-200 200zm42-104c0 23.159-18.841 42-42 42s-42-18.841-42-42 18.841-42 42-42 42 18.841 42 42zm-81.37-211.401l6.8 136c.319 6.387 5.591 11.401 11.985 11.401h41.17c6.394 0 11.666-5.014 11.985-11.401l6.8-136c.343-6.854-5.122-12.599-11.985-12.599h-54.77c-6.863 0-12.328 5.745-11.985 12.599z"></path></svg>`,
					duration: 60,
					buttons: [
						{
							label: "Reload",
							callback: () => {
								location.reload();
							},
						},
					],
				});
				break;
			case "response":
				if (this.promises?.[uid]) {
					this.promises[uid](data);
					delete this.promises[uid];
				}
				break;
			case "ready":
				this.flushQueue();
				break;
			default:
				console.warn(`Unhandled IDB Manager inbox message type: ${type}`);
				break;
		}
	}

	private send(type: string, data: any = null, callback: Function = noop) {
		const messageUid = uid();
		const message = {
			type: type,
			data: data,
			uid: messageUid,
		};
		this.promises[messageUid] = callback;
		if (this.ready) {
			this.worker.postMessage(message);
		} else {
			this.queue.push(message);
		}
	}

	private flushQueue() {
		this.ready = true;
		for (let i = this.queue.length - 1; i >= 0; i--) {
			this.worker.postMessage(this.queue[i]);
			this.queue.splice(i, 1);
		}
	}

	public ingest(role: string) {
		this.send("ingest", role);
	}

	public purge() {
		this.send("purge");
	}
}
const idbManager = new IDBManager();

function Ingest(role: string) {
	idbManager.ingest(role);
}
