// @ts-expect-error
self.importScripts("/js/idb.js");

// @ts-expect-error
self.importScripts("/js/fuzzysort.js");
declare var fuzzysort:any;

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
			case "delete":
				this.delete(data).then(() => {
					this.send("response", true, uid, origin);
				})
				.catch(error => {
					console.error(error);
					this.send("response", false, uid, origin);
				});
				break;
			case "put":
				this.put(data).then(() => {
					this.send("response", true, uid, origin);
				})
				.catch(error => {
					console.error(error);
					this.send("response", false, uid, origin);
				});
				break;
			case "search":
				this.search(data).then(output => {
					this.send("response", output, uid, origin);
				});
				break;
			case "get":
				this.get(data).then(output => {
					this.send("response", output, uid, origin);
				});
				break;
			case "count":
				this.count(data).then(output => {
					this.send("response", output, uid, origin);
				});
				break;
			case "select":
				this.select(data).then(output => {
					this.send("response", output, uid, origin);
				});
				break;
			case "ingest":
				this.ingestData(data).then(() => {
					this.send("response", true, uid, origin);
				})
				.catch(error => {
					console.error(error);
					this.send("response", false, uid, origin);
				});
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

	private async ingestData(data){
		const { route, table } = data;
		const ingestRequest = await fetch(`${API_URL}/${route.replace(/^\//, "")}`, {
			method: "GET",
			credentials: "include",
			headers: new Headers({
				Accept: "application/json",
			}),
		});
		const ingestData = await ingestRequest.json();
		const existingData = await this.db.getAll(table);
		// TODO: put and delete as needed
		if (ingestData.success){
			await this.db.clear(table);
			for (const data of ingestData.data){
				await this.db.put(table, data);
			}
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

	private async delete(data): Promise<void>{
		const { table, key } = data;
		await this.db.delete(table, key);
		return;
	}

	private async put(data): Promise<void>{
		const { table, key, value } = data;
		if (key !== null){
			await this.db.put(table, value, key);
		} else {
			await this.db.put(table, value);
		}
		return;
	}

	private async search(data): Promise<unknown>{
		const { table, key, query, limit } = data;
		let output = [];
		const rows:Array<unknown> = await this.db.getAll(table);
		const options = {
			threshold: -10000,
			limit: limit,
			allowTypo: false,
		};
		if (Array.isArray(key)){
			options["keys"] = key;
		} else {
			options["key"] = key;
		}
		const results = fuzzysort.go(query, rows, options);
		for (let i = 0; i < results.length; i++) {
			output.push(results[i].obj);
		}
		return output;
	}

	private async get(data): Promise<unknown>{
		const { table, key, index } = data;
		let output = null;
		if (index !== null){
			output = await this.db.getFromIndex(table, index, key);
		} else {
			output = await this.db.get(table, key);
		}
		return output;
	}

	private async count(table:string):Promise<number>{
		const rows:Array<unknown> = await this.db.getAll(table);
		return rows.length;
	}

	private async select(data): Promise<Array<unknown>>{
		const { table, page, limit } = data;
		const rows:Array<unknown> = await this.db.getAll(table);
		let output = [];
		if (limit !== null){
			let start = (page - 1) * limit;
			let end = page * limit;
			output = rows.slice(start, end);
		} else {
			output = rows;
		}
		return output;
	}
}
new IDBWorker();
