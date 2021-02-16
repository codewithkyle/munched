// @ts-expect-error
self.importScripts("/js/idb.js");

// @ts-expect-error
self.importScripts("/js/fuzzysort.js");

// @ts-expect-error
self.importScripts("/js/config.js");

type Schema = {
	version: number;
	tables: Array<Table>;
};

type Table = {
	name: string;
	columns: Array<Column>;
	keyPath?: string;
	autoIncrement?: boolean;
	ingestURL?: string;
};

type Column = {
	key: string;
	unique?: boolean;
}

const DB_NAME = "localdb";

class IDBWorker{
	private db:any;

	constructor(){
		this.db = null;
		self.onmessage = this.inbox.bind(this);
		this.main();
	}

	private inbox(e:MessageEvent){
		const messageEventData  = e.data;
		const origin = e?.origin ?? null;
		const { type, data, uid } = messageEventData;
		switch(type){
			case "ingest":
				this.ingestData(data);
				break;
			case "purge":
				this.purgeData();
				break;
			default:
				console.warn(`Unhandled IDB Worker inbox message type: ${type}`);
				break;
		}
	}

	private send(type:string = "response", data:any = null, uid:string = null, origin = null){
		const message = {
			type: type,
			data: data,
			uid: uid,
		};
		if (origin){
			self.postMessage(message, origin);
		} else {
			// @ts-expect-error
			self.postMessage(message);
		}
	}

	private async ingestData(type:string){
		const request = await fetch(`/ingest.json`);
		const data = await request.json();
		if (data?.[type]){
			for (const ingest of data[type]){
				const ingestRequest = await fetch(`${API_URL}/v1/ingest/${ingest.route.replace(/^\//, "")}`, {
					method: "GET",
					credentials: "include",
					headers: new Headers({
						Accept: "application/json",
					}),
				});
				const ingestData = await ingestRequest.json();
				if (ingestData.success){
					await this.db.clear(ingest.table);
					for (const data of ingestData.data){
						await this.db.put(ingest.table, data);
					}
				}
			}
		} else {
			console.error(`${type} does not exist on ingest.json`);
		}
	}

	private async purgeData(){
		// @ts-expect-error
		await idb.deleteDB(DB_NAME, {
			blocked(){
				this.send("error", "Failed to purge local data because this app is still open in other tabs.");
			}
		});
	}

	private async main(){
		try {
			const request = await fetch(`/schema.json`);
			const scheam:Schema = await request.json();
			// @ts-expect-error
			this.db = await idb.openDB(DB_NAME, scheam.version, {
				upgrade(db, oldVersion, newVersion, transaction) {
					// Purge old stores so we don't brick the JS runtime VM when upgrading
					for (let i = 0; i < db.objectStoreNames.length; i++){
						db.deleteObjectStore(db.objectStoreNames[i]);
					}
					for (let i = 0; i < scheam.tables.length; i++){
						const table:Table = scheam.tables[i];
						const options = {
							keyPath: "id",
							autoIncrement: false,
						};
						if (table?.keyPath){
							options.keyPath = table.keyPath;
						}
						if (typeof table.autoIncrement !== "undefined"){
							options.autoIncrement = table.autoIncrement;
						}
						const store = db.createObjectStore(table.name, options);
						for (let k = 0; k < table.columns.length; k++){
							const column:Column = table.columns[k];
							store.createIndex(column.key, column.key, {
								unique: column?.unique ?? false,
							});
						}
					}
				},
				blocked() {
					this.send("error", "This app needs to restart. Close all tabs for this app and before relaunching.");
				},
				blocking() {
					this.send("error", "This app needs to restart. Close all tabs for this app before relaunching.");
				}
			});
			this.send("ready");
		} catch (e) {
			console.error(e);
		}
	}
}
new IDBWorker();
